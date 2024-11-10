<?php

namespace App\Twig;

use App\Entity\User;
use App\Service\UnreadMessageCounter;
use App\Service\UnrepliedEventsCounter;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AwaitingActionsExtension extends AbstractExtension
{
    private UnreadMessageCounter $unreadMessageCounter;
    private UnrepliedEventsCounter $unrepliedEventsCounter;

    public function __construct(UnreadMessageCounter $unreadMessageCounter, UnrepliedEventsCounter $unrepliedEventsCounter)
    {
        $this->unreadMessageCounter = $unreadMessageCounter;
        $this->unrepliedEventsCounter = $unrepliedEventsCounter;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('awaiting_actions_count', [$this, 'getAwaitingActionsCount']),
            new TwigFunction('awaiting_actions_children_count', [$this, 'getAwaitingActionsForChildrenCount']),
        ];
    }

    public function getAwaitingActionsCount(User $user): int
    {
        $nbUnreadMessages = $this->unreadMessageCounter->countUnreadMessages($user);
        $nbUnrepliedEvents = $this->unrepliedEventsCounter->countUnrepliedEvents($user);

        return $nbUnreadMessages + $nbUnrepliedEvents;
    }

    public function getAwaitingActionsForChildrenCount(User $user): int
    {
        $nbUnreadMessages = 0;
        $nbUnrepliedEvents = 0;

        $children = $user->getChildren();

        foreach ($children as $child) {
            $nbUnreadMessages += $this->unreadMessageCounter->countUnreadMessages($child);
            $nbUnrepliedEvents += $this->unrepliedEventsCounter->countUnrepliedEvents($child);
        }

        return $nbUnreadMessages + $nbUnrepliedEvents;
    }
}
