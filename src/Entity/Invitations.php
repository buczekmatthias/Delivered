<?php

namespace App\Entity;

use App\Repository\InvitationsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=InvitationsRepository::class)
 */
class Invitations
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="sentInvitation")
     * @ORM\JoinColumn(nullable=false)
     */
    private $sender;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="receivedInvitations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $toWho;

    /**
     * @ORM\Column(type="datetime")
     */
    private $requestedAt;

    /**
     * @ORM\Column(type="boolean")
     */
    private $seen = false;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getToWho(): ?User
    {
        return $this->toWho;
    }

    public function setToWho(?User $toWho): self
    {
        $this->toWho = $toWho;

        return $this;
    }

    public function getRequestedAt(): ?\DateTimeInterface
    {
        return $this->requestedAt;
    }

    public function setRequestedAt(\DateTimeInterface $requestedAt): self
    {
        $this->requestedAt = $requestedAt;

        return $this;
    }

    public function getSeen(): ?bool
    {
        return $this->seen;
    }

    public function setSeen(bool $seen): self
    {
        $this->seen = $seen;

        return $this;
    }

    public function isNew(): ?bool
    {
        return !$this->seen;
    }
}
