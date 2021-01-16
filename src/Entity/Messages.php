<?php

namespace App\Entity;

use App\Repository\MessagesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MessagesRepository::class)
 */
class Messages
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="array")
     */
    private $content = [];

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $sender;

    /**
     * @ORM\ManyToOne(targetEntity=Chats::class, inversedBy="messages")
     * @ORM\JoinColumn(nullable=false)
     */
    private $chat;

    /**
     * @ORM\Column(type="datetime")
     */
    private $sendAt;

    /**
     * @ORM\ManyToMany(targetEntity=User::class)
     */
    private $seen;

    public function __construct()
    {
        $this->seen = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?array
    {
        return $this->content;
    }

    public function setContent(array $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getSender(): ?User
    {
        return $this->sender;
    }

    public function setSender(?User $sender): self
    {
        $this->sender = $sender;

        return $this;
    }

    public function getChat(): ?Chats
    {
        return $this->chat;
    }

    public function setChat(?Chats $chat): self
    {
        $this->chat = $chat;

        return $this;
    }

    public function getSendAt(): ?\DateTimeInterface
    {
        return $this->sendAt;
    }

    public function setSendAt(\DateTimeInterface $sendAt): self
    {
        $this->sendAt = $sendAt;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getSeen(): Collection
    {
        return $this->seen;
    }

    public function addSeen(User $seen): self
    {
        if (!$this->seen->contains($seen)) {
            $this->seen[] = $seen;
        }

        return $this;
    }

    public function removeSeen(User $seen): self
    {
        $this->seen->removeElement($seen);

        return $this;
    }
}
