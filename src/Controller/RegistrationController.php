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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
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

    public function __construct(EmailVerifier $emailVerifier, TranslatorInterface $translator, UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->emailVerifier = $emailVerifier;
        $this->translator = $translator;
        $this->userPasswordHasher = $userPasswordHasher;
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, EntityManagerInterface $entityManager, ProfilePictureManager $profilePictureManager, SessionInterface $session): Response
    {
        // Denied access if user is already logged in.
        if ($this->getUser()) {
            $this->addFlash('error', $this->translator->trans('error.already_logged'));
            return $this->redirectToRoute('app_home');
        }

        if (!$session->has('step')) {
            $session->set('step', $this::USER_CHOICE);
        }

        $step = $session->get('step');
        $userChoice = $session->get('user_choice');

        $user = new User();
        $form = $this->createAppropriateForm($step, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($step === $this::USER_CHOICE) {
                $session->set('user_choice', $form->getData()['user_choice']);
                $session->set('step', $this::USER_REGISTRATION);
            } else if ($step === $this::USER_REGISTRATION) {
                // Encode the plain password
                $this->hasPassword($form, $user);

                // Save picture on server via FileUploader Service
                $profilePictureManager->handleProfilePicture($form, $user);

                $entityManager->persist($user);
                $entityManager->flush();

                // Generate a signed url and email it to the user
                $this->sendConfirmationEmail($user);

                $this->addFlash('success', $this->translator->trans('success.account_created'));
            } elseif ($userChoice === 'parent' && $step === $this::CHILDREN_REGISTRATION) {
            } elseif ($userChoice === 'player' && $step === $this::CHILDREN_REGISTRATION) {
            }
        }

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
    public function verifyUserEmail(Request $request, TranslatorInterface $translator, UserRepository $userRepository, EmailManager $emailManager): Response
    {
        $id = $request->query->get('id');

        if (null === $id) {
            return $this->redirectToRoute('app_login');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('app_login');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            $this->addFlash('error', $this->translator->trans('error.email_validation'));

            return $this->redirectToRoute('app_login');
        }

        // Send an e-mail to admins to inform them of a new registration - Mail can stay in french
        $emailManager->sendEmail(EmailManager::ADMIN_MAIL, "Nouvelle inscription", "new_member", ['user' => $user]);

        $this->addFlash('success', $this->translator->trans('success.email_verified'));

        return $this->redirectToRoute('app_login');
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

    private function hasPassword(FormInterface $form, User $user): void
    {
        $user->setPassword(
            $this->userPasswordHasher->hashPassword(
                $user,
                $form->get('password')->getData()
            )
        );
    }
}
