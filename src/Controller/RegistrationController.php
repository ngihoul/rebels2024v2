<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Service\ProfilePictureManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;
    private TranslatorInterface $translator;

    public function __construct(EmailVerifier $emailVerifier, TranslatorInterface $translator)
    {
        $this->emailVerifier = $emailVerifier;
        $this->translator = $translator;
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, ProfilePictureManager $profilePictureManager): Response
    {
        // Denied access if user is already logged in.
        if ($this->getUser()) {
            $this->addFlash('error', $this->translator->trans('error.already_logged'));
            return $this->redirectToRoute('app_home');
        }

        // Create registration form
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            // Save picture on server via FileUploader Service
            $profilePictureManager->handleProfilePicture($form, $user);

            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email',
                $user,
                (new TemplatedEmail())
                    ->from(new Address('nicolas@gihoul.be', 'Liege Rebels Baseball & Softball Club'))
                    ->to($user->getEmail())
                    ->subject('Liège Rebels - ' . $this->translator->trans('registration.subject', [], 'emails'))
                    ->htmlTemplate('emails/registration_confirmation.html.twig')
            );

            $this->addFlash('success', $this->translator->trans('success.account_created'));

            return $this->redirectToRoute('app_home');
        }

        return $this->render('registration/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator, UserRepository $userRepository): Response
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

        $this->addFlash('success', $this->translator->trans('success.email_verified'));

        return $this->redirectToRoute('app_login');
    }
}
