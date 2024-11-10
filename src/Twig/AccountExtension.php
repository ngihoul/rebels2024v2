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
            new TwigFunction('get_original_user', [$this, 'getOriginalUser']),
        ];
    }

    public function getUserChildren($userId)
    {
        return $this->accountManager->getUserChildren($userId);
    }

    public function getOriginalUser()
    {
        return $this->accountManager->getOriginalUser();
    }
}
