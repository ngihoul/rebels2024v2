<?php

namespace App\Entity;

use App\Repository\MessageStatusRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessageStatusRepository::class)]
class MessageStatus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'messageStatuses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Message $message = null;

    #[ORM\ManyToOne(inversedBy: 'messageStatuses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $receiver = null;

    #[ORM\Column]
    private ?bool $status = null;

    public function __construct()
    {
        $this->status = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessage(): ?Message
    {
        return $this->message;
    }

    public function setMessage(?Message $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getReceiver(): ?User
    {
        return $this->receiver;
    }

    public function setReceiver(?User $receiver): static
    {
        $this->receiver = $receiver;

        return $this;
    }

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): static
    {
        $this->status = $status;

        return $this;
    }
}
