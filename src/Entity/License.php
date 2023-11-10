<?php

namespace App\Entity;

use App\Repository\LicenseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinTable;

#[ORM\Entity(repositoryClass: LicenseRepository::class)]
class License
{
    const ON_DEMAND = 1;
    const DOC_DOWNLOADED = 2;
    const DOC_RECEIVED = 3;
    const DOC_VALIDATED = 4;
    const IN_ORDER = 5;


    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 4)]
    private ?string $season = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $demand_file = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $uploaded_file = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $status = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\ManyToOne(inversedBy: 'licenses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne]
    private ?User $user_last_update = null;

    #[ORM\ManyToMany(targetEntity: LicenseSubCategory::class, inversedBy: 'licenses')]
    #[JoinTable(name: 'license_detail')]
    private Collection $subCategories;

    public function __construct()
    {
        $this->subCategories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSeason(): ?string
    {
        return $this->season;
    }

    public function setSeason(string $season): static
    {
        $this->season = $season;

        return $this;
    }

    public function getDemandFile(): ?string
    {
        return $this->demand_file;
    }

    public function setDemandFile(?string $demand_file): static
    {
        $this->demand_file = $demand_file;

        return $this;
    }

    public function getUploadedFile(): ?string
    {
        return $this->uploaded_file;
    }

    public function setUploadedFile(?string $uploaded_file): static
    {
        $this->uploaded_file = $uploaded_file;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): static
    {
        $this->status = $status;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getUserLastUpdate(): ?User
    {
        return $this->user_last_update;
    }

    public function setUserLastUpdate(?User $user_last_update): static
    {
        $this->user_last_update = $user_last_update;

        return $this;
    }

    /**
     * @return Collection<int, LicenseSubCategory>
     */
    public function getSubCategories(): Collection
    {
        return $this->subCategories;
    }

    public function addSubCategory(LicenseSubCategory $subCategory): static
    {
        if (!$this->subCategories->contains($subCategory)) {
            $this->subCategories->add($subCategory);
        }

        return $this;
    }

    public function removeSubCategory(LicenseSubCategory $subCategory): static
    {
        $this->subCategories->removeElement($subCategory);

        return $this;
    }

    // Définir par défaut l année d'une license (cette année)
    #[ORM\PrePersist]
    public function setDefaultSeason(): void
    {
        if (null === $this->season) {
            $this->season = date('Y');
        }
    }
}
