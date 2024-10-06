<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Twig\Environment;
use Symfony\Contracts\Translation\TranslatorInterface;

class EmailManager
{
    const ADMIN_MAIL = 'nicolas@gihoul.be';
    const ADMIN_NAME = 'Liege Rebels Baseball & Softball Club';

    private $mailer;
    private TranslatorInterface $translator;

    public function __construct(MailerInterface $mailer, TranslatorInterface $translator)
    {
        $this->mailer = $mailer;
        $this->translator = $translator;
    }

    public function sendEmail(string $to, string $subject, string $template, array $parameters = []): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address(self::ADMIN_MAIL, self::ADMIN_NAME))
            ->to($to)
            ->subject(self::ADMIN_NAME . ' - ' . $subject)
            ->htmlTemplate("emails/$template.html.twig")
            ->context($parameters);

        $this->mailer->send($email);
    }

    public function inviteChildToChoosePassword(User $child)
    {
        $this->sendEmail($child->getEmail(), $this->translator->trans('children.choose_password.subject', [], 'emails'), "children_choose_password", []);
    }
}
