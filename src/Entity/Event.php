<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $time_meeting = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE)]
    private ?\DateTimeImmutable $time_from = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE)]
    private ?\DateTimeImmutable $time_to = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Place $place = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    private ?Team $team = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    private ?EventCategory $category = null;

    #[ORM\OneToMany(mappedBy: 'event', targetEntity: EventAttendee::class)]
    private Collection $attendees;

    public function __construct()
    {
        $this->attendees = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, EventAttendee>
     */
    public function getAttendees(): Collection
    {
        return $this->attendees;
    }

    public function addAttendee(EventAttendee $attendee): static
    {
        if (!$this->attendees->contains($attendee)) {
            $this->attendees->add($attendee);
            $attendee->setEvent($this);
        }

        return $this;
    }

    public function removeAttendee(EventAttendee $attendee): static
    {
        if ($this->attendees->removeElement($attendee)) {
            // set the owning side to null (unless already changed)
            if ($attendee->getEvent() === $this) {
                $attendee->setEvent(null);
            }
        }

        return $this;
    }
}
