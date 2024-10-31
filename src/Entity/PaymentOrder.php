<?php

namespace App\Entity;

use App\Repository\PaymentOrderRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaymentOrderRepository::class)]
class PaymentOrder
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'paymentOrders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Payment $payment = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 2)]
    private ?string $amount = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dueDate = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $valueDate = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment = null;

    #[ORM\ManyToOne]
    private ?User $validatedBy = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPayment(): ?Payment
    {
        return $this->payment;
    }

    public function setPayment(?Payment $payment): static
    {
        $this->payment = $payment;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getDueDate(): ?\DateTimeImmutable
    {
        return $this->dueDate;
    }

    public function setDueDate(\DateTimeImmutable $dueDate): static
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    public function getValueDate(): ?\DateTimeImmutable
    {
        return $this->valueDate;
    }

    public function setValueDate(?\DateTimeImmutable $valueDate): static
    {
        $this->valueDate = $valueDate;

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

    public function getValidatedBy(): ?User
    {
        return $this->validatedBy;
    }

    public function setValidatedBy(?User $validatedBy): static
    {
        $this->validatedBy = $validatedBy;

        return $this;
    }
}
