<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $sender = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column]
    private ?bool $sent_by_mail = null;

    #[ORM\Column]
    private ?bool $is_archived = null;

    #[ORM\Column]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\OneToMany(mappedBy: 'message', targetEntity: MessageStatus::class)]
    private Collection $messageStatuses;

    public function __construct()
    {
        $this->messageStatuses = new ArrayCollection();
        $this->is_archived = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSender(): ?User
    {
        return $this->sender;
    }

    public function setSender(?User $sender): static
    {
        $this->sender = $sender;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function isSentByMail(): ?bool
    {
        return $this->sent_by_mail;
    }

    public function setSentByMail(bool $sent_by_mail): static
    {
        $this->sent_by_mail = $sent_by_mail;

        return $this;
    }

    public function isIsArchived(): ?bool
    {
        return $this->is_archived;
    }

    public function setIsArchived(bool $is_archived): static
    {
        $this->is_archived = $is_archived;

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
            $messageStatus->setMessage($this);
        }

        return $this;
    }

    public function removeMessageStatus(MessageStatus $messageStatus): static
    {
        if ($this->messageStatuses->removeElement($messageStatus)) {
            // set the owning side to null (unless already changed)
            if ($messageStatus->getMessage() === $this) {
                $messageStatus->setMessage(null);
            }
        }

        return $this;
    }
}
