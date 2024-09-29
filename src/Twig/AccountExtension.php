<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use App\Service\AccountManager;

class AccountExtension extends AbstractExtension
{
    private AccountManager $accountManager;

    public function __construct(AccountManager $accountManager)
    {
        $this->accountManager = $accountManager;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('get_children', [$this, 'getUserChildren']),
            new TwigFunction('get_active_user', [$this, 'getActiveUser']),
        ];
    }

    public function getUserChildren($userId)
    {
        return $this->accountManager->getUserChildren($userId);
    }

    public function getActiveUser()
    {
        return $this->accountManager->getActiveUser();
    }
}
