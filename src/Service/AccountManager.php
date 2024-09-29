<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class AccountManager
{
    private UserRepository $userRepository;
    private SessionInterface $session;

    public function __construct(UserRepository $userRepository, RequestStack $requestStack)
    {
        $this->userRepository = $userRepository;
        $this->session = $requestStack->getSession();
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
