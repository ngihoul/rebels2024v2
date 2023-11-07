<?php

namespace App\Entity;

use App\Repository\LicenseCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LicenseCategoryRepository::class)]
class LicenseCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'category', targetEntity: LicenseSubCategory::class, orphanRemoval: true)]
    private Collection $licenseSubCategories;

    public function __construct()
    {
        $this->licenseSubCategories = new ArrayCollection();
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

    /**
     * @return Collection<int, LicenseSubCategory>
     */
    public function getLicenseSubCategories(): Collection
    {
        return $this->licenseSubCategories;
    }

    public function addLicenseSubCategory(LicenseSubCategory $licenseSubCategory): static
    {
        if (!$this->licenseSubCategories->contains($licenseSubCategory)) {
            $this->licenseSubCategories->add($licenseSubCategory);
            $licenseSubCategory->setCategory($this);
        }

        return $this;
    }

    public function removeLicenseSubCategory(LicenseSubCategory $licenseSubCategory): static
    {
        if ($this->licenseSubCategories->removeElement($licenseSubCategory)) {
            // set the owning side to null (unless already changed)
            if ($licenseSubCategory->getCategory() === $this) {
                $licenseSubCategory->setCategory(null);
            }
        }

        return $this;
    }
}
