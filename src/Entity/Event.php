<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $time_meeting = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE)]
    private ?\DateTimeImmutable $time_from = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE)]
    private ?\DateTimeImmutable $time_to = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $opponent = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Place $place = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    private ?Team $team = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    private ?EventCategory $category = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getTimeMeeting(): ?\DateTimeImmutable
    {
        return $this->time_meeting;
    }

    public function setTimeMeeting(?\DateTimeImmutable $time_meeting): static
    {
        $this->time_meeting = $time_meeting;

        return $this;
    }

    public function getTimeFrom(): ?\DateTimeImmutable
    {
        return $this->time_from;
    }

    public function setTimeFrom(\DateTimeImmutable $time_from): static
    {
        $this->time_from = $time_from;

        return $this;
    }

    public function getTimeTo(): ?\DateTimeImmutable
    {
        return $this->time_to;
    }

    public function setTimeTo(\DateTimeImmutable $time_to): static
    {
        $this->time_to = $time_to;

        return $this;
    }

    public function getOpponent(): ?string
    {
        return $this->opponent;
    }

    public function setOpponent(?string $opponent): static
    {
        $this->opponent = $opponent;

        return $this;
    }

    public function getPlace(): ?Place
    {
        return $this->place;
    }

    public function setPlace(?Place $place): static
    {
        $this->place = $place;

        return $this;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): static
    {
        $this->team = $team;

        return $this;
    }

    public function getCategory(): ?EventCategory
    {
        return $this->category;
    }

    public function setCategory(?EventCategory $category): static
    {
        $this->category = $category;

        return $this;
    }
}
