<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=75)
     */
    private $Login;

    /**
     * @ORM\Column(type="string", length=75)
     */
    private $Password;

    /**
     * @ORM\Column(type="string", length=300)
     */
    private $Email;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $Name;

    /**
     * @ORM\Column(type="string", length=250)
     */
    private $Surname;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Messages", mappedBy="sender")
     */
    private $messages;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Chats", mappedBy="members")
     */
    private $chats;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ChatFiles", mappedBy="user")
     */
    private $chatFiles;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\JoinRequests", mappedBy="user")
     */
    private $joinRequests;

    /**
     * @ORM\Column(type="blob", nullable=true)
     */
    private $userImg;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
        $this->chats = new ArrayCollection();
        $this->chatFiles = new ArrayCollection();
        $this->joinRequests = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogin(): ?string
    {
        return $this->Login;
    }

    public function setLogin(string $Login): self
    {
        $this->Login = $Login;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->Password;
    }

    public function setPassword(string $Password): self
    {
        $this->Password = $Password;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->Email;
    }

    public function setEmail(string $Email): self
    {
        $this->Email = $Email;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->Name;
    }

    public function setName(string $Name): self
    {
        $this->Name = $Name;

        return $this;
    }

    public function getSurname(): ?string
    {
        return $this->Surname;
    }

    public function setSurname(string $Surname): self
    {
        $this->Surname = $Surname;

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
            $message->setSender($this);
        }

        return $this;
    }

    public function removeMessage(Messages $message): self
    {
        if ($this->messages->contains($message)) {
            $this->messages->removeElement($message);
            // set the owning side to null (unless already changed)
            if ($message->getSender() === $this) {
                $message->setSender(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Chats[]
     */
    public function getChats(): Collection
    {
        return $this->chats;
    }

    public function addChat(Chats $chat): self
    {
        if (!$this->chats->contains($chat)) {
            $this->chats[] = $chat;
            $chat->addMember($this);
        }

        return $this;
    }

    public function removeChat(Chats $chat): self
    {
        if ($this->chats->contains($chat)) {
            $this->chats->removeElement($chat);
            $chat->removeMember($this);
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
            $chatFile->setUser($this);
        }

        return $this;
    }

    public function removeChatFile(ChatFiles $chatFile): self
    {
        if ($this->chatFiles->contains($chatFile)) {
            $this->chatFiles->removeElement($chatFile);
            // set the owning side to null (unless already changed)
            if ($chatFile->getUser() === $this) {
                $chatFile->setUser(null);
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
            $joinRequest->setUser($this);
        }

        return $this;
    }

    public function removeJoinRequest(JoinRequests $joinRequest): self
    {
        if ($this->joinRequests->contains($joinRequest)) {
            $this->joinRequests->removeElement($joinRequest);
            // set the owning side to null (unless already changed)
            if ($joinRequest->getUser() === $this) {
                $joinRequest->setUser(null);
            }
        }

        return $this;
    }

    public function getUserImg()
    {
        return $this->userImg ? stream_get_contents($this->userImg) : null;
    }

    public function setUserImg($userImg): self
    {
        $this->userImg = $userImg;

        return $this;
    }
}
