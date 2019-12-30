<?php

namespace App\Entity;

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
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="writtenMessages")
     * @ORM\JoinColumn(nullable=false)
     * @ORM\JoinTable(name="user_send")
     */
    private $Source;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="receivedMessages")
     * @ORM\JoinColumn(nullable=false)
     * @ORM\JoinTable(name="user_receive")
     */
    private $Target;

    /**
     * @ORM\Column(type="text")
     */
    private $Content;

    /**
     * @ORM\Column(type="datetime")
     */
    private $Date;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSource(): ?User
    {
        return $this->Source;
    }

    public function setSource(?User $Source): self
    {
        $this->Source = $Source;

        return $this;
    }

    public function getTarget(): ?User
    {
        return $this->Target;
    }

    public function setTarget(?User $Target): self
    {
        $this->Target = $Target;

        return $this;
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
}
