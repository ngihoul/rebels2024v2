<?php

namespace App\Twig;

use App\Service\UnrepliedEventsCounter;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class UnrepliedEventsExtension extends AbstractExtension
{
    private UnrepliedEventsCounter $unrepliedEventsCounter;

    public function __construct(UnrepliedEventsCounter $unrepliedEventsCounter)
    {
        $this->unrepliedEventsCounter = $unrepliedEventsCounter;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('unreplied_events_count', [$this, 'getUnrepliedEventsCount']),
        ];
    }

    public function getUnrepliedEventsCount(): int
    {
        return $this->unrepliedEventsCounter->countUnrepliedEvents();
    }
}
