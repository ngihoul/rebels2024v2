<?php

namespace App\Service;

use App\Repository\UserRepository;

class AccountManager
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getUserChildren($userId)
    {
        $user = $this->userRepository->find($userId);
        return $user->getChildren();
    }
}
