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
     * @ORM\OneToMany(targetEntity="App\Entity\Messages", mappedBy="Source")
     */
    private $writtenMessages;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Messages", mappedBy="Target")
     */
    private $receivedMessages;

    public function __construct()
    {
        $this->writtenMessages = new ArrayCollection();
        $this->receivedMessages = new ArrayCollection();
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
    public function getWrittenMessages(): Collection
    {
        return $this->writtenMessages;
    }

    public function addWrittenMessage(Messages $writtenMessage): self
    {
        if (!$this->writtenMessages->contains($writtenMessage)) {
            $this->writtenMessages[] = $writtenMessage;
            $writtenMessage->setSource($this);
        }

        return $this;
    }

    public function removeWrittenMessage(Messages $writtenMessage): self
    {
        if ($this->writtenMessages->contains($writtenMessage)) {
            $this->writtenMessages->removeElement($writtenMessage);
            // set the owning side to null (unless already changed)
            if ($writtenMessage->getSource() === $this) {
                $writtenMessage->setSource(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Messages[]
     */
    public function getReceivedMessages(): Collection
    {
        return $this->receivedMessages;
    }

    public function addReceivedMessage(Messages $receivedMessage): self
    {
        if (!$this->receivedMessages->contains($receivedMessage)) {
            $this->receivedMessages[] = $receivedMessage;
            $receivedMessage->setTarget($this);
        }

        return $this;
    }

    public function removeReceivedMessage(Messages $receivedMessage): self
    {
        if ($this->receivedMessages->contains($receivedMessage)) {
            $this->receivedMessages->removeElement($receivedMessage);
            // set the owning side to null (unless already changed)
            if ($receivedMessage->getTarget() === $this) {
                $receivedMessage->setTarget(null);
            }
        }

        return $this;
    }
}
