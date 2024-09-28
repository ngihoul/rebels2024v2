<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationChildrenType;
use App\Form\RegistrationFormType;
use App\Form\UserChoiceType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Service\EmailManager;
use App\Service\ProfilePictureManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private const USER_CHOICE = 1;
    private const USER_REGISTRATION = 2;
    private const CHILDREN_REGISTRATION = 3;

    private EmailVerifier $emailVerifier;
    private TranslatorInterface $translator;
    private UserPasswordHasherInterface $userPasswordHasher;
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;
    private ProfilePictureManager $profilePictureManager;
    private SessionInterface $session;
    private EmailManager $emailManager;

    public function __construct(
        EmailVerifier $emailVerifier,
        TranslatorInterface $translator,
        UserPasswordHasherInterface $userPasswordHasher,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        ProfilePictureManager $profilePictureManager,
        RequestStack $requestStack,
        EmailManager $emailManager
    ) {
        $this->emailVerifier = $emailVerifier;
        $this->translator = $translator;
        $this->userPasswordHasher = $userPasswordHasher;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->profilePictureManager = $profilePictureManager;
        $this->session = $requestStack->getSession();
        $this->emailManager = $emailManager;
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request): Response
    {
        if ($this->getUser()) {
            return $this->handleUserAlreadyLoggedIn();
        }

        $this->initializeStep();

        $step = $this->session->get('step');
        $userChoice = $this->session->get('user_choice');

        $user = new User();

        $form = $this->createAppropriateForm($step, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->handleFormSubmission($form, $step, $userChoice);
        }

        return $this->renderAppropriateView($step, $form, $userChoice);
    }

    private function handleUserAlreadyLoggedIn(): Response
    {
        $this->addFlash('error', $this->translator->trans('error.already_logged'));
        return $this->redirectToRoute('app_home');
    }

    private function initializeStep(): void
    {
        if (!$this->session->has('step')) {
            $this->session->set('step', $this::USER_CHOICE);
        }
    }

    private function createAppropriateForm(int $step, User $user): FormInterface
    {
        if ($step === $this::USER_CHOICE) {
            $form = $this->createForm(UserChoiceType::class);
        } else if ($step === $this::USER_REGISTRATION) {
            $form = $this->createForm(RegistrationFormType::class, $user);
        } else if ($step === $this::CHILDREN_REGISTRATION) {
            $form = $this->createForm(RegistrationChildrenType::class);
        }

        return $form;
    }

    private function handleFormSubmission(FormInterface $form, int $step, ?string $userChoice): Response
    {
        if ($step === $this::USER_CHOICE) {
            return $this->handleUserChoiceStep($form);
        } elseif ($step === $this::USER_REGISTRATION) {
            return $this->handleUserRegistrationStep($form);
        } elseif ($userChoice === 'parent' && $step === $this::CHILDREN_REGISTRATION) {
            return $this->handleChildrenRegistrationStep($form);
        } elseif ($userChoice === 'player' && $step === $this::CHILDREN_REGISTRATION) {
            $this->session->clear();
            return $this->redirectToRoute('app_home');
        }
    }

    private function handleUserChoiceStep(FormInterface $form): Response
    {
        $this->session->set('user_choice', $form->getData()['user_choice']);
        $this->session->set('step', $this::USER_REGISTRATION);

        return $this->redirectToRoute('app_register');
    }

    private function handleUserRegistrationStep(FormInterface $form): Response
    {
        $user = new User();
        $user = $form->getData();

        // dd($form->getData());

        $this->hasPassword($form, $user);
        $this->profilePictureManager->handleProfilePicture($form, $user);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->sendConfirmationEmail($user);

        $this->addFlash('success', $this->translator->trans('success.account_created'));

        $this->session->set('step', $this::CHILDREN_REGISTRATION);
        $this->session->set('user_id', $user->getId());

        return $this->redirectToRoute('app_register');
    }

    private function hasPassword(FormInterface $form, User $user): void
    {
        $user->setPassword(
            $this->userPasswordHasher->hashPassword(
                $user,
                $form->get('password')->getData()
            )
        );
    }

    private function handleChildrenRegistrationStep(FormInterface $form): Response
    {
        $user = $this->userRepository->find($this->session->get('user_id'));
        $children = $form->getData()['children'];

        // dd($form->getData()['children']);

        foreach ($children as $index => $child) {
            $child->setParent($user);
            $this->profilePictureManager->handleProfilePicture($form->get('children')->get($index), $child);
            $this->entityManager->persist($child);
        }

        $this->entityManager->flush();

        $this->addFlash('success', $this->translator->trans('children_created'));

        $this->session->clear();

        return $this->redirectToRoute('app_home');
    }

    private function sendConfirmationEmail(User $user): void
    {
        $this->emailVerifier->sendEmailConfirmation(
            'app_verify_email',
            $user,
            (new TemplatedEmail())
                ->from(new Address('nicolas@gihoul.be', 'Liege Rebels Baseball & Softball Club'))
                ->to($user->getEmail())
                ->subject('Liège Rebels - ' . $this->translator->trans('registration.subject', [], 'emails'))
                ->htmlTemplate('emails/registration_confirmation.html.twig')
        );
    }

    private function renderAppropriateView(int $step, FormInterface $form, ?string $userChoice): Response
    {
        if ($step === $this::USER_CHOICE) {
            return $this->render('registration/user_choice.html.twig', [
                'form' => $form->createView(),
            ]);
        } elseif ($step === $this::USER_REGISTRATION) {
            return $this->render('registration/user_registration.html.twig', [
                'form' => $form->createView(),
            ]);
        } elseif ($userChoice === 'parent' && $step === $this::CHILDREN_REGISTRATION) {
            return $this->render('registration/children_registration.html.twig', [
                'form' => $form->createView(),
            ]);
        } elseif ($userChoice === 'player' && $step === $this::CHILDREN_REGISTRATION) {
            return $this->redirectToRoute('app_home');
        }
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request): Response
    {
        $id = $request->query->get('id');

        if (null === $id) {
            return $this->redirectToRoute('app_login');
        }

        $user = $this->userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('app_login');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $this->translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));
            $this->addFlash('error', $this->translator->trans('error.email_validation'));

            return $this->redirectToRoute('app_login');
        }

        // Send an e-mail to admins to inform them of a new registration - Mail can stay in french
        $this->emailManager->sendEmail(EmailManager::ADMIN_MAIL, "Nouvelle inscription", "new_member", ['user' => $user]);

        $this->addFlash('success', $this->translator->trans('success.email_verified'));

        return $this->redirectToRoute('app_login');
    }
}
