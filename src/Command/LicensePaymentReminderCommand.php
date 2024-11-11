<?php

namespace App\Command;

use App\Repository\PaymentOrderRepository;
use App\Repository\UserRepository;
use App\Service\EmailManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

// To be executed on the 7th of each month

#[AsCommand(
    name: 'app:license:payment_reminder',
    description: 'Send a reminder e-mail to users with overdue payments',
)]
class LicensePaymentReminderCommand extends Command
{
    private PaymentOrderRepository $paymentOrderRepository;
    private LoggerInterface $logger;
    private EmailManager $emailManager;
    private TranslatorInterface $translator;

    public function __construct(PaymentOrderRepository $paymentOrderRepository, UserRepository $userRepository, LoggerInterface $logger, EmailManager $emailManager, TranslatorInterface $translator)
    {
        $this->paymentOrderRepository = $paymentOrderRepository;
        $this->logger = $logger;
        $this->emailManager = $emailManager;
        $this->translator = $translator;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $paymentOrdersOverdue = $this->paymentOrderRepository->getPaymentsOverdue();
        $nbPaymentsOverdue = count($paymentOrdersOverdue);

        if ($paymentOrdersOverdue) {

            foreach ($paymentOrdersOverdue as $paymentOrder) {
                $user = $paymentOrder->getPayment()->getLicense()->getUser();

                if ($user->getAge() < 18) {
                    foreach ($user->getParents() as $parent) {
                        $this->sendPaymentReminder($parent, $paymentOrder);
                    }
                } else {
                    $this->sendPaymentReminder($user, $paymentOrder);
                }
            }

            $this->logger->info('Reminders sent : ' . $nbPaymentsOverdue);
            return Command::SUCCESS;
        } else {
            $this->logger->info('No reminder sent');
            return Command::FAILURE;
        }
    }

    private function sendPaymentReminder($user, $paymentOrder): void
    {
        $this->emailManager->sendEmail($user->getEmail(), $this->translator->trans('payment.reminder.subject', [], 'emails'), 'payment_reminder', ['paymentOrder' => $paymentOrder]);
    }
}
