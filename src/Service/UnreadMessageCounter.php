<?php

namespace App\Service;

use App\Repository\MessageRepository;
use Symfony\Bundle\SecurityBundle\Security;

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
            // Gérer le cas où l'utilisateur n'est pas connecté, si nécessaire
            return 0;
        }

        return $this->messageRepository->countUnreadMessagesForThisUser($currentUser);
    }
}
