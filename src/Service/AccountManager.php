<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;

class AccountManager
{
    private UserRepository $userRepository;
    private RequestStack $requestStack;
    private TokenStorageInterface $tokenStorage;
    private Security $security;

    public function __construct(UserRepository $userRepository, RequestStack $requestStack, TokenStorageInterface $tokenStorage, Security $security)
    {
        $this->userRepository = $userRepository;
        $this->requestStack = $requestStack;
        $this->tokenStorage = $tokenStorage;
        $this->security = $security;
    }

    public function getUserChildren($userId)
    {
        $user = $this->userRepository->find($userId);
        return $user->getChildren();
    }

    public function getActiveUser()
    {
        $token = $this->tokenStorage->getToken();

        if ($token instanceof SwitchUserToken) {
            return $token->getOriginalToken()->getUser();
        }

        return $this->security->getUser();
    }
}
