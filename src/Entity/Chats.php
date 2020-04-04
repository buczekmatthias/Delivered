<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ChatsRepository")
 */
class Chats
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $chatHash;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="chats")
     */
    private $members;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Messages", mappedBy="chat")
     */
    private $messages;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $chatName;

    public function __construct()
    {
        $this->members = new ArrayCollection();
        $this->messages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChatHash(): ?string
    {
        return $this->chatHash;
    }

    public function setChatHash(string $chatHash): self
    {
        $this->chatHash = $chatHash;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(User $member): self
    {
        if (!$this->members->contains($member)) {
            $this->members[] = $member;
        }

        return $this;
    }

    public function removeMember(User $member): self
    {
        if ($this->members->contains($member)) {
            $this->members->removeElement($member);
        }

        return $this;
    }

    /**
     * @return Collection|Messages[]
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Messages $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages[] = $message;
            $message->setChat($this);
        }

        return $this;
    }

    public function removeMessage(Messages $message): self
    {
        if ($this->messages->contains($message)) {
            $this->messages->removeElement($message);
            // set the owning side to null (unless already changed)
            if ($message->getChat() === $this) {
                $message->setChat(null);
            }
        }

        return $this;
    }

    public function getChatName(): ?string
    {
        return $this->chatName;
    }

    public function setChatName(?string $chatName): self
    {
        $this->chatName = $chatName;

        return $this;
    }
}
