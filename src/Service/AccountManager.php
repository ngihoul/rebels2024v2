<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;

class AccountManager
{
    private UserRepository $userRepository;
    private RequestStack $requestStack;
    private Security $security;

    public function __construct(UserRepository $userRepository, RequestStack $requestStack, Security $security)
    {
        $this->userRepository = $userRepository;
        $this->requestStack = $requestStack;
        $this->security = $security;
    }

    public function getUserChildren($userId)
    {
        $user = $this->userRepository->find($userId);
        return $user->getChildren();
    }

    public function getOriginalUser()
    {
        $originalToken = unserialize($this->requestStack->getSession()->get('_switch_user'));

        if ($originalToken) {
            return $originalToken->getUser();
        }

        return $this->security->getUser();
    }
}
