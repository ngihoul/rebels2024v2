<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\EventRepository;
use Symfony\Bundle\SecurityBundle\Security;

// Service to display in the nav bar a badge with the number of unreplied events
// Linked to /Twig/UnrepliedEventsExtension
class UnrepliedEventsCounter
{
    private EventRepository $eventRepository;
    private Security $security;

    public function __construct(EventRepository $eventRepository, Security $security)
    {
        $this->eventRepository = $eventRepository;
        $this->security = $security;
    }

    public function countUnrepliedEvents(User $user): int
    {
        if ($user === null) {
            return 0;
        }

        return count($this->eventRepository->findPendingEventsForThisUser($user));
    }
}
