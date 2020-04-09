<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MessagesRepository")
 */
class Messages
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $Content;

    /**
     * @ORM\Column(type="datetime")
     */
    private $Date;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="messages")
     * @ORM\JoinColumn(nullable=false)
     */
    private $sender;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Chats", inversedBy="messages")
     * @ORM\JoinColumn(nullable=false)
     */
    private $chat;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User")
     */
    private $displayed;

    public function __construct()
    {
        $this->displayed = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->Content;
    }

    public function setContent(string $Content): self
    {
        $this->Content = $Content;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->Date;
    }

    public function setDate(\DateTimeInterface $Date): self
    {
        $this->Date = $Date;

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

    /**
     * @return Collection|User[]
     */
    public function getDisplayed(): Collection
    {
        return $this->displayed;
    }

    public function addDisplayed(User $displayed): self
    {
        if (!$this->displayed->contains($displayed)) {
            $this->displayed[] = $displayed;
        }

        return $this;
    }

    public function removeDisplayed(User $displayed): self
    {
        if ($this->displayed->contains($displayed)) {
            $this->displayed->removeElement($displayed);
        }

        return $this;
    }
}
