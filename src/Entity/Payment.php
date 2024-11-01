<?php

namespace App\Entity;

use App\Repository\PaymentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
class Payment
{
    const STATUS_PENDING = NULL;
    const STATUS_ACCEPTED = 1;
    const STATUS_REFUSED = 2;
    const STATUS_COMPLETED = 3;

    const BY_STRIPE = 1;
    const BY_BANK_TRANSFER = 2;
    const BY_PAYMENT_PLAN = 3;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'payments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?License $license = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $payment_type = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $status = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $user_comment = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $refusal_comment = null;

    #[ORM\Column]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    #[Gedmo\Timestampable(on: 'change', field: ['status'])]
    private ?\DateTimeImmutable $updated_at = null;

    /**
     * @var Collection<int, PaymentOrder>
     */
    #[ORM\OneToMany(mappedBy: 'payment', targetEntity: PaymentOrder::class)]
    private Collection $payment_orders;

    public function __construct()
    {
        $this->payment_orders = new ArrayCollection();
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
        return $this->user_comment;
    }

    public function setUserComment(?string $user_comment): static
    {
        $this->user_comment = $user_comment;

        return $this;
    }

    public function getRefusalComment(): ?string
    {
        return $this->refusal_comment;
    }

    public function setRefusalComment(?string $refusal_comment): static
    {
        $this->refusal_comment = $refusal_comment;

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

    public function setUpdatedAt(?\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    /**
     * @return Collection<int, PaymentOrder>
     */
    public function getPaymentOrders(): Collection
    {
        return $this->payment_orders;
    }

    public function addPaymentOrder(PaymentOrder $payment_order): static
    {
        if (!$this->payment_orders->contains($payment_order)) {
            $this->payment_orders->add($payment_order);
            $payment_order->setPayment($this);
        }

        return $this;
    }

    public function removePaymentOrder(PaymentOrder $payment_order): static
    {
        if ($this->payment_orders->removeElement($payment_order)) {
            // set the owning side to null (unless already changed)
            if ($payment_order->getPayment() === $this) {
                $payment_order->setPayment(null);
            }
        }

        return $this;
    }

    public function getPaymentType(): ?int
    {
        return $this->payment_type;
    }

    public function setPaymentType(?int $payment_type): static
    {
        $this->payment_type = $payment_type;

        return $this;
    }
}
