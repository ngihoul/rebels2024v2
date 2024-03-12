<?php

namespace App\Twig;

use App\Service\UnreadMessageCounter;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class UnreadMessagesExtension extends AbstractExtension
{
    private UnreadMessageCounter $unreadMessageCounter;

    public function __construct(UnreadMessageCounter $unreadMessageCounter)
    {
        $this->unreadMessageCounter = $unreadMessageCounter;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('unread_messages_count', [$this, 'getUnreadMessagesCount']),
        ];
    }

    public function getUnreadMessagesCount(): int
    {
        return $this->unreadMessageCounter->countUnreadMessages();
    }
}
