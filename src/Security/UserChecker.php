<?php

namespace App\Security;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Security\EmailVerifier;
use App\Service\EmailManager;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserChecker implements UserCheckerInterface
{

    private EntityManagerInterface $entityManager;
    private EmailVerifier $emailVerifier;
    private TranslatorInterface $translator;
    private EmailManager $emailManager;

    public function __construct(EntityManagerInterface $entityManager, EmailVerifier $emailVerifier, TranslatorInterface $translator, EmailManager $emailManager)
    {
        $this->entityManager = $entityManager;
        $this->emailVerifier = $emailVerifier;
        $this->translator = $translator;
        $this->emailManager = $emailManager;
    }

    public function checkPreAuth(UserInterface $user)
    {
        if ($user->getPassword() == null) {
            throw new CustomUserMessageAuthenticationException($this->translator->trans('error.bad_credentials'));
        }

        // 
        if ($user->isChild() && $user->getPassword() == null && $user->canUseApp() == true) {
            $this->emailManager->inviteChildToChoosePassword($user);
            throw new CustomUserMessageAuthenticationException($this->translator->trans('error.bad_credentials'));
        }

        if (!$user->isVerified()) {
            // Generate a signed url and email it to the user if his email is not verified
            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email',
                $user,
                (new TemplatedEmail())
                    ->from(new Address('no-reply@gihoul.be', 'Liège Rebels Baseball & Softball Club'))
                    ->to($user->getEmail())
                    ->subject('Confirmation de ton compte')
                    ->htmlTemplate('emails/registration_confirmation.html.twig')
            );
            throw new CustomUserMessageAuthenticationException($this->translator->trans('error.verify_email.not_verified'));
        }

        if ($user->isBanned()) {
            throw new CustomUserMessageAuthenticationException($this->translator->trans('error.verify_email.banned'));
        }
    }

    public function checkPostAuth(UserInterface $user) {}
}
