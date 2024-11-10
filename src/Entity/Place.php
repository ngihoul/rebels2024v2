<?php

namespace App\Entity;

use App\Repository\PlaceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlaceRepository::class)]
class Place
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $address_street = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address_number = null;

    #[ORM\Column(length: 6)]
    private ?string $address_zipcode = null;

    #[ORM\Column(length: 255)]
    private ?string $address_locality = null;

    #[ORM\OneToMany(mappedBy: 'place', targetEntity: Event::class)]
    private Collection $events;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Country $address_country = null;

    public function __construct()
    {
        $this->events = new ArrayCollection();
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

    public function getAddressStreet(): ?string
    {
        return $this->address_street;
    }

    public function setAddressStreet(string $address_street): static
    {
        $this->address_street = $address_street;

        return $this;
    }

    public function getAddressNumber(): ?string
    {
        return $this->address_number;
    }

    public function setAddressNumber(string $address_number): static
    {
        $this->address_number = $address_number;

        return $this;
    }

    public function getAddressZipcode(): ?string
    {
        return $this->address_zipcode;
    }

    public function setAddressZipcode(string $address_zipcode): static
    {
        $this->address_zipcode = $address_zipcode;

        return $this;
    }

    public function getAddressLocality(): ?string
    {
        return $this->address_locality;
    }

    public function setAddressLocality(string $address_locality): static
    {
        $this->address_locality = $address_locality;

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setPlace($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getPlace() === $this) {
                $event->setPlace(null);
            }
        }

        return $this;
    }

    public function getAddressCountry(): ?Country
    {
        return $this->address_country;
    }

    public function setAddressCountry(?Country $address_country): static
    {
        $this->address_country = $address_country;

        return $this;
    }
}
