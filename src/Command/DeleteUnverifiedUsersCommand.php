<?php

namespace App\Command;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(
    name: 'app:delete:unverified-users',
    description: 'Delete unverified users after 24h',
)]
class DeleteUnverifiedUsersCommand extends Command
{
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;
    private ParameterBagInterface $params;
    private LoggerInterface $logger;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $entityManager, ParameterBagInterface $params, LoggerInterface $logger)
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->params = $params;
        $this->logger = $logger;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Fetch unverified users
        $unverifiedUsers = $this->userRepository->getUnverified();
        // Log the number of unverified users
        $nbUnverifedUsers = count($unverifiedUsers);
        if ($unverifiedUsers) {
            foreach ($unverifiedUsers as $user) {
                // Delete profil picture linked to this user from server
                $profilePicture = $user->getProfilePicture();
                if ($profilePicture) {
                    $filePath = $this->params->get('pictures_directory') . '/' . $profilePicture;
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }
                // Delete the user
                $this->entityManager->remove($user);
                $this->entityManager->flush();
            }
            $this->logger->info('Unverified users deleted : ' . $nbUnverifedUsers);
            return Command::SUCCESS;
        } else {
            $this->logger->info('No unverified user');
            return Command::FAILURE;
        }
    }
}
