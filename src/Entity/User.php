<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinTable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Gedmo\Mapping\Annotation as Gedmo;

use function PHPUnit\Framework\stringStartsWith;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 50)]
    private ?string $firstname = null;

    #[ORM\Column(length: 50)]
    private ?string $lastname = null;

    #[ORM\Column(length: 12, nullable: true)]
    private ?string $license_number = null;

    #[ORM\Column(type: 'smallint', nullable: true)]
    private ?int $jersey_number = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $date_of_birth;

    #[ORM\Column(length: 1)]
    private ?string $gender = null;

    #[ORM\Column(length: 120)]
    private ?string $address_street = null;

    #[ORM\Column(length: 20)]
    private ?string $address_number = null;

    #[ORM\Column(length: 6)]
    private ?string $zipcode = null;

    #[ORM\Column(length: 50)]
    private ?string $locality = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $phone_number = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $mobile_number = null;

    #[ORM\Column(length: 180, unique: true, nullable: true)]
    private ?string $email = null;

    /**
     * @var string The hashed password
     */
    #[ORM\Column(nullable: true)]
    private ?string $password = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column(nullable: true)]
    private ?string $profile_picture = null;

    #[ORM\Column(type: 'boolean')]
    private bool $newsletter_lfbbs = false;

    #[ORM\Column(type: 'boolean')]
    private bool $internal_rules = false;

    #[ORM\Column(type: 'boolean')]
    private bool $is_banned = false;

    #[ORM\Column(type: 'boolean')]
    private bool $is_archived = false;

    #[ORM\Column(type: 'boolean')]
    private $isVerified = false;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: License::class, orphanRemoval: true)]
    private Collection $licenses;

    #[ORM\ManyToMany(targetEntity: Team::class, mappedBy: 'players')]
    #[JoinTable(name: 'roster')]
    private Collection $teams;

    #[ORM\OneToMany(mappedBy: 'coach', targetEntity: Team::class)]
    private Collection $coach_of;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: EventAttendee::class)]
    private Collection $events;

    #[ORM\Column]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    #[Gedmo\Timestampable(on: 'update')]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Country $nationality = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Country $country = null;

    #[ORM\Column(options: ["default" => 0], nullable: true)]
    private ?bool $privacy_policy = null;

    #[ORM\OneToMany(mappedBy: 'receiver', targetEntity: MessageStatus::class)]
    private Collection $messageStatuses;

    #[ORM\OneToMany(mappedBy: 'child', targetEntity: Relation::class, cascade: ['persist', 'remove'])]
    private Collection $children;

    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: Relation::class, cascade: ['persist', 'remove'])]
    private Collection $parents;

    #[ORM\Column(nullable: true)]
    private ?bool $canUseApp = null;

    #[ORM\ManyToOne(targetEntity: self::class)]
    private ?self $canUseAppBy = null;

    #[ORM\Column(nullable: true)]
    #[Gedmo\Timestampable(on: 'change', field: ['can_use_app'])]
    private ?\DateTimeImmutable $canUseAppFromDate = null;

    public function __construct()
    {
        $this->licenses = new ArrayCollection();
        $this->teams = new ArrayCollection();
        $this->coach_of = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->messageStatuses = new ArrayCollection();
        $this->children = new ArrayCollection();
        $this->parents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFullName()
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    public function getLicenseNumber(): ?string
    {
        return $this->license_number;
    }

    public function setLicenseNumber(?string $license_number): static
    {
        $this->license_number = $license_number;

        return $this;
    }

    public function getJerseyNumber(): ?int
    {
        return $this->jersey_number;
    }

    public function setJerseyNumber(?int $jersey_number): static
    {
        $this->jersey_number = $jersey_number;

        return $this;
    }

    public function getDateOfBirth(): \DateTimeInterface
    {
        return $this->date_of_birth;
    }

    public function getAge(): int
    {
        $now = new \DateTime();
        $interval = $now->diff($this->date_of_birth);

        return $interval->y;
    }

    public function setDateOfBirth(\DateTimeInterface $date_of_birth): static
    {
        $this->date_of_birth = $date_of_birth;

        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(string $gender): static
    {
        $this->gender = $gender;

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

    public function getZipcode(): ?string
    {
        return $this->zipcode;
    }

    public function setZipcode(string $zipcode): static
    {
        $this->zipcode = $zipcode;

        return $this;
    }

    public function getLocality(): ?string
    {
        return $this->locality;
    }

    public function setLocality(string $locality): static
    {
        $this->locality = $locality;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phone_number;
    }

    public function setPhoneNumber(?string $phone_number): static
    {
        $this->phone_number = $phone_number;

        return $this;
    }

    public function getMobileNumber(): ?string
    {
        return $this->mobile_number;
    }

    public function setMobileNumber(?string $mobile_number): static
    {
        $this->mobile_number = $mobile_number;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getProfilePicture(): ?string
    {
        return $this->profile_picture;
    }

    public function setProfilePicture(string $profile_picture): static
    {
        $this->profile_picture = $profile_picture;

        return $this;
    }

    public function isNewsletterLfbbs(): bool
    {
        return $this->newsletter_lfbbs;
    }

    public function setNewsletterLfbbs(bool $newsletter_lfbbs): static
    {
        $this->newsletter_lfbbs = $newsletter_lfbbs;

        return $this;
    }

    public function isInternalRules(): bool
    {
        return $this->internal_rules;
    }

    public function setInternalRules(bool $internal_rules): static
    {
        $this->internal_rules = $internal_rules;

        return $this;
    }

    public function isBanned(): bool
    {
        return $this->is_banned;
    }

    public function setIsBanned(bool $is_banned): static
    {
        $this->is_banned = $is_banned;

        return $this;
    }

    public function isArchived(): bool
    {
        return $this->is_archived;
    }

    public function setIsArchived(bool $is_archived): static
    {
        $this->is_archived = $is_archived;

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    /**
     * @return Collection<int, License>
     */
    public function getLicenses(): Collection
    {
        return $this->licenses;
    }

    public function addLicense(License $license): static
    {
        if (!$this->licenses->contains($license)) {
            $this->licenses->add($license);
            $license->setUser($this);
        }

        return $this;
    }

    public function removeLicense(License $license): static
    {
        if ($this->licenses->removeElement($license)) {
            // set the owning side to null (unless already changed)
            if ($license->getUser() === $this) {
                $license->setUser(null);
            }
        }

        return $this;
    }

    public function isProfileComplete(): array
    {
        $missingFields = [];

        if ($this->firstname === null) {
            $missingFields[] = 'firstname';
        }
        if ($this->lastname === null) {
            $missingFields[] = 'lastname';
        }
        if ($this->nationality === null) {
            $missingFields[] = 'nationality';
        }
        if ($this->date_of_birth === null) {
            $missingFields[] = 'date_of_birth';
        }
        if ($this->gender === null) {
            $missingFields[] = 'gender';
        }
        if ($this->address_street === null) {
            $missingFields[] = 'address_street';
        }
        if ($this->address_number === null) {
            $missingFields[] = 'address_number';
        }
        if ($this->zipcode === null) {
            $missingFields[] = 'zipcode';
        }
        if ($this->locality === null) {
            $missingFields[] = 'locality';
        }
        if ($this->country === null) {
            $missingFields[] = 'country';
        }
        // if $this->profile_picture start with default

        if ($this->profile_picture === null || $this->profile_picture === '' || !strpos($this->profile_picture, 'default/default')) {
            $missingFields[] = 'profile_picture';
        }
        if (!$this->isChild()) {
            if ($this->newsletter_lfbbs === null) {
                $missingFields[] = 'newsletter_lfbbs';
            }
            if ($this->internal_rules !== true) {
                $missingFields[] = 'internal_rules';
            }
            if ($this->privacy_policy !== true) {
                $missingFields[] = 'privacy_policy';
            }
            if ($this->email === null) {
                $missingFields[] = 'email';
            }
        }

        return $missingFields;
    }

    public function isChild(): bool
    {
        return $this->getParents()->count() > 0;
    }

    /**
     * @return Collection<int, Team>
     */
    public function getTeams(): Collection
    {
        return $this->teams;
    }

    public function addTeam(Team $team): static
    {
        if (!$this->teams->contains($team)) {
            $this->teams->add($team);
            $team->addPlayer($this);
        }

        return $this;
    }

    public function removeTeam(Team $team): static
    {
        if ($this->teams->removeElement($team)) {
            $team->removePlayer($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Team>
     */
    public function getCoachOf(): Collection
    {
        return $this->coach_of;
    }

    public function addCoachOf(Team $coachOf): static
    {
        if (!$this->coach_of->contains($coachOf)) {
            $this->coach_of->add($coachOf);
            $coachOf->setCoach($this);
        }

        return $this;
    }

    public function removeCoachOf(Team $coachOf): static
    {
        if ($this->coach_of->removeElement($coachOf)) {
            // set the owning side to null (unless already changed)
            if ($coachOf->getCoach() === $this) {
                $coachOf->setCoach(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, EventAttendee>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(EventAttendee $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setUser($this);
        }

        return $this;
    }

    public function removeEvent(EventAttendee $event): static
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getUser() === $this) {
                $event->setUser(null);
            }
        }

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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getNationality(): ?Country
    {
        return $this->nationality;
    }

    public function setNationality(?Country $nationality): static
    {
        $this->nationality = $nationality;

        return $this;
    }

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setCountry(?Country $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function isPrivacyPolicy(): ?bool
    {
        return $this->privacy_policy;
    }

    public function setPrivacyPolicy(bool $privacy_policy): static
    {
        $this->privacy_policy = $privacy_policy;

        return $this;
    }

    /**
     * @return Collection<int, MessageStatus>
     */
    public function getMessageStatuses(): Collection
    {
        return $this->messageStatuses;
    }

    public function addMessageStatus(MessageStatus $messageStatus): static
    {
        if (!$this->messageStatuses->contains($messageStatus)) {
            $this->messageStatuses->add($messageStatus);
            $messageStatus->setReceiver($this);
        }

        return $this;
    }

    public function removeMessageStatus(MessageStatus $messageStatus): static
    {
        if ($this->messageStatuses->removeElement($messageStatus)) {
            // set the owning side to null (unless already changed)
            if ($messageStatus->getReceiver() === $this) {
                $messageStatus->setReceiver(null);
            }
        }

        return $this;
    }

    public function getChildren(): Collection
    {
        return $this->parents->map(fn(Relation $relation) => $relation->getChild());
    }

    public function setChild(User $child, RelationType $relationType): static
    {
        if (!$this->getChildren()->contains($child)) {
            $relation = new Relation();
            $relation->setParent($this);
            $relation->setChild($child);
            $relation->setRelationType($relationType);
            $this->parents->add($relation);
        }

        return $this;
    }

    public function removeChild(User $child): static
    {
        foreach ($this->parents as $relation) {
            if ($relation->getChild() === $child) {
                $this->parents->removeElement($relation);
                break;
            }
        }

        return $this;
    }

    // To solve doctrine error // Never used !!!
    public function addChild(User $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children[] = $child;
        }

        return $this;
    }

    public function getParents(): Collection
    {
        return $this->children->map(fn(Relation $relation) => $relation->getParent());
    }

    public function setParent(User $parent, RelationType $relationType): static
    {
        if (!$this->getParents()->contains($parent)) {
            $relation = new Relation();
            $relation->setParent($parent);
            $relation->setChild($this);
            $relation->setRelationType($relationType);
            $this->children->add($relation);
        }

        return $this;
    }

    public function removeParent(User $parent): static
    {
        foreach ($this->children as $relation) {
            if ($relation->getParent() === $parent) {
                $this->children->removeElement($relation);
                break;
            }
        }

        return $this;
    }

    public function canUseApp(): ?bool
    {
        return $this->canUseApp;
    }

    public function setCanUseApp(?bool $canUseApp): static
    {
        $this->canUseApp = $canUseApp;

        return $this;
    }

    public function getCanUseAppBy(): ?self
    {
        return $this->canUseAppBy;
    }

    public function setCanUseAppBy(?self $canUseAppBy): static
    {
        $this->canUseAppBy = $canUseAppBy;

        return $this;
    }

    public function getCanUseAppFromDate(): ?\DateTimeImmutable
    {
        return $this->canUseAppFromDate;
    }

    public function setCanUseAppFromDate(?\DateTimeImmutable $canUseAppFromDate): static
    {
        $this->canUseAppFromDate = $canUseAppFromDate;

        return $this;
    }
}
