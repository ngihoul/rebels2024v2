<?php

namespace App\Entity;

use App\Repository\PaymentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
class Payment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'payments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?License $license = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?PaymentType $PaymentType = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $status = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $UserComment = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $refusalComment = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, PaymentOrder>
     */
    #[ORM\OneToMany(mappedBy: 'payment', targetEntity: PaymentOrder::class)]
    private Collection $paymentOrders;

    public function __construct()
    {
        $this->paymentOrders = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLicense(): ?License
    {
        return $this->license;
    }

    public function setLicense(?License $license): static
    {
        $this->license = $license;

        return $this;
    }

    public function getPaymentType(): ?PaymentType
    {
        return $this->PaymentType;
    }

    public function setPaymentType(?PaymentType $PaymentType): static
    {
        $this->PaymentType = $PaymentType;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getUserComment(): ?string
    {
        return $this->UserComment;
    }

    public function setUserComment(?string $UserComment): static
    {
        $this->UserComment = $UserComment;

        return $this;
    }

    public function getRefusalComment(): ?string
    {
        return $this->refusalComment;
    }

    public function setRefusalComment(?string $refusalComment): static
    {
        $this->refusalComment = $refusalComment;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, PaymentOrder>
     */
    public function getPaymentOrders(): Collection
    {
        return $this->paymentOrders;
    }

    public function addPaymentOrder(PaymentOrder $paymentOrder): static
    {
        if (!$this->paymentOrders->contains($paymentOrder)) {
            $this->paymentOrders->add($paymentOrder);
            $paymentOrder->setPayment($this);
        }

        return $this;
    }

    public function removePaymentOrder(PaymentOrder $paymentOrder): static
    {
        if ($this->paymentOrders->removeElement($paymentOrder)) {
            // set the owning side to null (unless already changed)
            if ($paymentOrder->getPayment() === $this) {
                $paymentOrder->setPayment(null);
            }
        }

        return $this;
    }
}
