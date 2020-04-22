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
     * @ORM\JoinTable(name="chat_members")
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

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User")
     * @ORM\JoinTable(name="chat_admins")
     */
    private $admins;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ChatFiles", mappedBy="chat")
     */
    private $chatFiles;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\JoinRequests", mappedBy="chat")
     */
    private $joinRequests;

    /**
     * @ORM\Column(type="blob", nullable=true)
     */
    private $image;

    public function __construct()
    {
        $this->members = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->admins = new ArrayCollection();
        $this->chatFiles = new ArrayCollection();
        $this->joinRequests = new ArrayCollection();
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

    /**
     * @return Collection|User[]
     */
    public function getAdmins(): Collection
    {
        return $this->admins;
    }

    public function addAdmin(User $admin): self
    {
        if (!$this->admins->contains($admin)) {
            $this->admins[] = $admin;
        }

        return $this;
    }

    public function removeAdmin(User $admin): self
    {
        if ($this->admins->contains($admin)) {
            $this->admins->removeElement($admin);
        }

        return $this;
    }

    /**
     * @return Collection|ChatFiles[]
     */
    public function getChatFiles(): Collection
    {
        return $this->chatFiles;
    }

    public function addChatFile(ChatFiles $chatFile): self
    {
        if (!$this->chatFiles->contains($chatFile)) {
            $this->chatFiles[] = $chatFile;
            $chatFile->setChat($this);
        }

        return $this;
    }

    public function removeChatFile(ChatFiles $chatFile): self
    {
        if ($this->chatFiles->contains($chatFile)) {
            $this->chatFiles->removeElement($chatFile);
            // set the owning side to null (unless already changed)
            if ($chatFile->getChat() === $this) {
                $chatFile->setChat(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|JoinRequests[]
     */
    public function getJoinRequests(): Collection
    {
        return $this->joinRequests;
    }

    public function addJoinRequest(JoinRequests $joinRequest): self
    {
        if (!$this->joinRequests->contains($joinRequest)) {
            $this->joinRequests[] = $joinRequest;
            $joinRequest->setChat($this);
        }

        return $this;
    }

    public function removeJoinRequest(JoinRequests $joinRequest): self
    {
        if ($this->joinRequests->contains($joinRequest)) {
            $this->joinRequests->removeElement($joinRequest);
            // set the owning side to null (unless already changed)
            if ($joinRequest->getChat() === $this) {
                $joinRequest->setChat(null);
            }
        }

        return $this;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setImage($image): self
    {
        $this->image = $image;

        return $this;
    }
}
