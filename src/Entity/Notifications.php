<?php

namespace App\Entity;

use App\Repository\NotificationsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=NotificationsRepository::class)
 */
class Notifications
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="notifications")
     * @ORM\JoinColumn(nullable=false)
     */
    private $toWho;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @ORM\Column(type="datetime")
     */
    private $receivedAt;

    /**
     * @ORM\Column(type="boolean")
     */
    private $seen = false;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getReceivedAt(): ?\DateTimeInterface
    {
        return $this->receivedAt;
    }

    public function setReceivedAt(\DateTimeInterface $receivedAt): self
    {
        $this->receivedAt = $receivedAt;

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
