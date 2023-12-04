<?php

namespace App\Security;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Security\EmailVerifier;

class UserChecker implements UserCheckerInterface
{

    private EntityManagerInterface $entityManager;
    private EmailVerifier $emailVerifier;

    public function __construct(EntityManagerInterface $entityManager, EmailVerifier $emailVerifier)
    {
        $this->entityManager = $entityManager;
        $this->emailVerifier = $emailVerifier;
    }

    public function checkPreAuth(UserInterface $user)
    {
        if (!$user->isVerified()) {
            // Generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email',
                $user,
                (new TemplatedEmail())
                    ->from(new Address('no-reply@gihoul.be', 'Liège Rebels Baseball & Softball Club'))
                    ->to($user->getEmail())
                    ->subject('Confirmation de ton compte')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );
            throw new CustomUserMessageAuthenticationException("Ton compte n'a pas encore été verifié. Un nouveau mail de confirmation t'a été envoyé");
        }

        if ($user->isBanned()) {
            throw new CustomUserMessageAuthenticationException("Tu es banni. Tu ne peux donc plus utiliser ce site !");
        }
    }

    public function checkPostAuth(UserInterface $user)
    {
    }
}
