<?php

namespace App\Service;

use App\Repository\MessageRepository;
use Symfony\Bundle\SecurityBundle\Security;

// Service to display in the mav bar a badge with the number of unread messages
// Linked to /Twig/UnreadMessagesExtension
class UnreadMessageCounter
{
    private MessageRepository $messageRepository;
    private Security $security;

    public function __construct(MessageRepository $messageRepository, Security $security)
    {
        $this->messageRepository = $messageRepository;
        $this->security = $security;
    }

    public function countUnreadMessages(): int
    {
        $currentUser = $this->security->getUser();
        if ($currentUser === null) {
            return 0;
        }

        return $this->messageRepository->countUnreadMessagesForThisUser($currentUser);
    }
}
