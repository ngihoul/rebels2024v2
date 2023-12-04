<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Twig\Environment;

class EmailManager
{
    const ADMIN_MAIL = 'nicolas@gihoul.be';
    const ADMIN_NAME = 'Liege Rebels Baseball & Softball Club';

    private $mailer;
    private $twig;

    public function __construct(MailerInterface $mailer, Environment $twig)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    public function sendEmail(string $to, string $subject, string $template, array $parameters = []): void
    {
        $htmlBody = $this->twig->render("emails/$template.html.twig", $parameters);

        $email = (new TemplatedEmail())
            ->from(new Address(self::ADMIN_MAIL, self::ADMIN_NAME))
            ->to($to)
            ->subject(self::ADMIN_NAME . ' - ' . $subject)
            ->htmlTemplate("emails/$template.html.twig")
            ->context($parameters);

        $this->mailer->send($email);
    }
}
