<?php

namespace App\Command;

use App\Repository\UserRepository;
use App\Service\EmailManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(
    name: 'app:upgrade:child-16yo',
    description: 'Send a mail to parents and children when a child turns 16 and can now use the app',
)]
class ChildrenWhoTurn16Command extends Command
{
    private UserRepository $userRepository;
    private LoggerInterface $logger;
    private EmailManager $emailManager;
    private TranslatorInterface $translator;

    public function __construct(UserRepository $userRepository, LoggerInterface $logger, EmailManager $emailManager, TranslatorInterface $translator)
    {
        $this->userRepository = $userRepository;
        $this->logger = $logger;
        $this->emailManager = $emailManager;
        $this->translator = $translator;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Fetch children who have just reached 16 years old
        $children = $this->userRepository->getChildrenWhoTurn16();
        $nbChildren = count($children);

        if ($children) {
            foreach ($children as $child) {
                // Send an email to the parents
                $parents = $child->getParents();

                foreach ($parents as $parent) {
                    $this->emailManager->sendEmail($parent->getEmail(), $this->translator->trans('children.turn_16.parent.subject', ['child_firstname' => $child->getFirstname()], 'emails'), 'parent_child_16', ['child' => $child]);
                }
            }

            $this->logger->info('Children who turn 16 years old : ' . $nbChildren);
            return Command::SUCCESS;
        } else {
            $this->logger->info('No Children who turn 16 years old');
            return Command::FAILURE;
        }
    }
}
