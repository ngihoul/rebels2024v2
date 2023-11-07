<?php

namespace App\Entity;

use App\Repository\LicenseSubCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinTable;

#[ORM\Entity(repositoryClass: LicenseSubCategoryRepository::class)]
class LicenseSubCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'licenseSubCategories')]
    #[ORM\JoinColumn(nullable: false)]
    private ?LicenseCategory $category = null;

    #[ORM\ManyToMany(targetEntity: License::class, mappedBy: 'subCategories')]
    #[JoinTable(name: 'license_detail')]
    private Collection $licenses;

    public function __construct()
    {
        $this->licenses = new ArrayCollection();
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

    public function getCategory(): ?LicenseCategory
    {
        return $this->category;
    }

    public function setCategory(?LicenseCategory $category): static
    {
        $this->category = $category;

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
            $license->addSubCategory($this);
        }

        return $this;
    }

    public function removeLicense(License $license): static
    {
        if ($this->licenses->removeElement($license)) {
            $license->removeSubCategory($this);
        }

        return $this;
    }
}
