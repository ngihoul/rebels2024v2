<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class AccountManager
{
    private UserRepository $userRepository;
    private SessionInterface $session;

    public function __construct(UserRepository $userRepository, SessionInterface $session)
    {
        $this->userRepository = $userRepository;
        $this->session = $session;
    }

    public function getSession(): SessionInterface
    {
        return $this->session;
    }

    public function getUserById($userId): ?User
    {
        return $this->userRepository->find($userId);
    }

    public function getUserChildren($userId)
    {
        $user = $this->userRepository->find($userId);
        return $user->getChildren();
    }
}
