<?php

namespace App\Command;

use App\Repository\RelationRepository;
use App\Repository\UserRepository;
use App\Service\EmailManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(
    name: 'app:upgrade:major-children',
    description: 'Upgrade 18years old children to adult : Delete their parent(s) and set their role to ROLE_USER',
)]
class UpgradeMajorChildrenCommand extends Command
{
    private UserRepository $userRepository;
    private RelationRepository $relationRepository;
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;
    private EmailManager $emailManager;
    private TranslatorInterface $translator;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $entityManager, LoggerInterface $logger, RelationRepository $relationRepository, EmailManager $emailManager, TranslatorInterface $translator)
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->relationRepository = $relationRepository;
        $this->emailManager = $emailManager;
        $this->translator = $translator;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Fetch children who have just reached 18 years old
        $youngAdults = $this->userRepository->getYoungAdults();
        $nbYoungAdults = count($youngAdults);

        if ($youngAdults) {
            foreach ($youngAdults as $youngAdult) {
                // Delete the parent(s)
                $parents = $youngAdult->getParents();

                foreach ($parents as $parent) {
                    $relation = $this->relationRepository->getRelation($parent, $youngAdult);

                    $this->entityManager->remove($relation);
                    $this->entityManager->flush();

                    $this->emailManager->sendEmail($parent->getEmail(), $this->translator->trans('children.turn_18.parent.subject', ['child_firstname' => $youngAdult->getFirstname()], 'emails'), 'parent_child_18', ['child' => $youngAdult]);
                }

                // Set the role to ROLE_USER
                $youngAdult->setRoles(['ROLE_USER']);
                $this->entityManager->persist($youngAdult);
                $this->entityManager->flush();

                if ($youngAdult->getEmail()) {
                    if ($youngAdult->getPassword()) {
                        $this->emailManager->sendEmail($youngAdult->getEmail(), $this->translator->trans('children.turn_18.child.subject', [], 'emails'), 'child_18');
                    } else {
                        $this->emailManager->inviteChildToChoosePassword($youngAdult);
                    }
                }
            }

            $this->logger->info('Young adults upgraded : ' . $nbYoungAdults);
            return Command::SUCCESS;
        } else {
            $this->logger->info('No young adult');
            return Command::FAILURE;
        }
    }
}
