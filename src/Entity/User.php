<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $login;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=300)
     */
    private $email;

    /**
     * @ORM\Column(type="array")
     */
    private $name = [];

    /**
     * @ORM\Column(type="string", length=300, nullable=true)
     */
    private $city;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $birthdayDate;

    /**
     * @ORM\Column(type="datetime")
     */
    private $joinedAt;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $friends = [];

    /**
     * @ORM\OneToMany(targetEntity=Invitations::class, mappedBy="sender")
     */
    private $sentInvitation;

    /**
     * @ORM\OneToMany(targetEntity=Invitations::class, mappedBy="toWho")
     */
    private $receivedInvitations;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    private $image = "/images/users/avatar.png";

    /**
     * @ORM\OneToMany(targetEntity=Requests::class, mappedBy="byWho")
     */
    private $joinRequests;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastActivity;

    /**
     * @ORM\OneToMany(targetEntity=Notifications::class, mappedBy="toWho")
     */
    private $notifications;

    public function __construct()
    {
        $this->sentInvitation = new ArrayCollection();
        $this->receivedInvitations = new ArrayCollection();
        $this->joinRequests = new ArrayCollection();
        $this->notifications = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(string $login): self
    {
        $this->login = $login;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->login;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getName(): ?array
    {
        return $this->name;
    }

    public function setName(array $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getBirthdayDate(): ?\DateTimeInterface
    {
        return $this->birthdayDate;
    }

    public function setBirthdayDate(?\DateTimeInterface $birthdayDate): self
    {
        $this->birthdayDate = $birthdayDate;

        return $this;
    }

    public function getJoinedAt(): ?\DateTimeInterface
    {
        return $this->joinedAt;
    }

    public function setJoinedAt(\DateTimeInterface $joinedAt): self
    {
        $this->joinedAt = $joinedAt;

        return $this;
    }

    public function getFriends(): ?array
    {
        return $this->friends;
    }

    public function setFriends(?array $friends): self
    {
        $this->friends = $friends;

        return $this;
    }

    /**
     * @return Collection|Invitations[]
     */
    public function getSentInvitation(): Collection
    {
        return $this->sentInvitation;
    }

    public function addSentInvitation(Invitations $sentInvitation): self
    {
        if (!$this->sentInvitation->contains($sentInvitation)) {
            $this->sentInvitation[] = $sentInvitation;
            $sentInvitation->setSender($this);
        }

        return $this;
    }

    public function removeSentInvitation(Invitations $sentInvitation): self
    {
        if ($this->sentInvitation->removeElement($sentInvitation)) {
            // set the owning side to null (unless already changed)
            if ($sentInvitation->getSender() === $this) {
                $sentInvitation->setSender(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Invitations[]
     */
    public function getReceivedInvitations(): Collection
    {
        return $this->receivedInvitations;
    }

    public function addReceivedInvitation(Invitations $receivedInvitation): self
    {
        if (!$this->receivedInvitations->contains($receivedInvitation)) {
            $this->receivedInvitations[] = $receivedInvitation;
            $receivedInvitation->setToWho($this);
        }

        return $this;
    }

    public function removeReceivedInvitation(Invitations $receivedInvitation): self
    {
        if ($this->receivedInvitations->removeElement($receivedInvitation)) {
            // set the owning side to null (unless already changed)
            if ($receivedInvitation->getToWho() === $this) {
                $receivedInvitation->setToWho(null);
            }
        }

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return Collection|Requests[]
     */
    public function getJoinRequests(): Collection
    {
        return $this->joinRequests;
    }

    public function addJoinRequest(Requests $joinRequest): self
    {
        if (!$this->joinRequests->contains($joinRequest)) {
            $this->joinRequests[] = $joinRequest;
            $joinRequest->setByWho($this);
        }

        return $this;
    }

    public function removeJoinRequest(Requests $joinRequest): self
    {
        if ($this->joinRequests->removeElement($joinRequest)) {
            // set the owning side to null (unless already changed)
            if ($joinRequest->getByWho() === $this) {
                $joinRequest->setByWho(null);
            }
        }

        return $this;
    }

    public function getLastActivity(): ?\DateTimeInterface
    {
        return $this->lastActivity;
    }

    public function setLastActivity(?\DateTimeInterface $lastActivity): self
    {
        $this->lastActivity = $lastActivity;

        return $this;
    }

    /**
     * @return Collection|Notifications[]
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notifications $notification): self
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications[] = $notification;
            $notification->setToWho($this);
        }

        return $this;
    }

    public function removeNotification(Notifications $notification): self
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getToWho() === $this) {
                $notification->setToWho(null);
            }
        }

        return $this;
    }

    public function isActive()
    {
        return ($this->getLastActivity() > new \DateTime('5 minutes ago'));
    }

    public function getFullName(): ?string
    {
        $out = '';

        $out .= $this->name['first'];

        if ($this->name['mid']) {
            $out .= " " . $this->name['mid'];
        }

        $out .= " " . $this->name['last'];

        return $out;
    }
}
