<?php

namespace App\Entity;

use App\Repository\EventAttendeeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventAttendeeRepository::class)]
class EventAttendee
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'attendees')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Event $event = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?bool $user_response = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $responded_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): static
    {
        $this->event = $event;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function isUserResponse(): ?bool
    {
        return $this->user_response;
    }

    public function setUserResponse(?bool $user_response): static
    {
        $this->user_response = $user_response;

        return $this;
    }

    public function getRespondedAt(): ?\DateTimeImmutable
    {
        return $this->responded_at;
    }

    public function setRespondedAt(?\DateTimeImmutable $responded_at): static
    {
        $this->responded_at = $responded_at;

        return $this;
    }
}
