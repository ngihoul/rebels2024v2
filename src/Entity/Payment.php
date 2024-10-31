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
    private ?PaymentType $payment_type = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $status = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $user_comment = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $refusal_comment = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
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

    public function getPaymentType(): ?PaymentType
    {
        return $this->payment_type;
    }

    /*************  ✨ Codeium Command ⭐  *************/
    /**
     * Sets the payment type for this payment.
     *
     * @param PaymentType|null $PaymentType
     *
     * @return static
     */
    /******  f81febee-8919-4d16-baa5-a0eb477130ac  *******/
    public function setPaymentType(?PaymentType $payment_type): static
    {
        $this->payment_type = $payment_type;

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
}
