<?php

namespace App\Entity;

use App\Repository\ChatsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * @ORM\Entity(repositoryClass=ChatsRepository::class)
 */
class Chats
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
    private $members = [];

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $files = [];

    /**
     * @ORM\OneToMany(targetEntity=Messages::class, mappedBy="chat")
     */
    private $messages;

    /**
     * @ORM\OneToMany(targetEntity=Requests::class, mappedBy="chat")
     */
    private $joinRequests;

    /**
     * @ORM\Column(type="string", length=300, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    private $image;

    public function __construct(TokenStorage $tokenStorage)
    {
        $this->messages = new ArrayCollection();
        $this->joinRequests = new ArrayCollection();
        $this->current = $tokenStorage->getToken()->getUser();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMembers(): ?array
    {
        return $this->members;
    }

    public function setMembers(array $members = ['admins' => [], 'members' => []]): self
    {
        $this->members = $members;

        return $this;
    }

    public function getFiles(): ?array
    {
        return $this->files;
    }

    public function setFiles(?array $files): self
    {
        $this->files = $files;

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
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getChat() === $this) {
                $message->setChat(null);
            }
        }

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
            $joinRequest->setChat($this);
        }

        return $this;
    }

    public function removeJoinRequest(Requests $joinRequest): self
    {
        if ($this->joinRequests->removeElement($joinRequest)) {
            // set the owning side to null (unless already changed)
            if ($joinRequest->getChat() === $this) {
                $joinRequest->setChat(null);
            }
        }

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image = "/images/chats/default.png"): self
    {
        $this->image = $image;

        return $this;
    }

    public function getCustomName(): ?string
    {
        if ($this->name) {
            return $this->name;
        }

        $totalMembers = sizeof($this->members['admins']) + sizeof($this->members['members']);
        $output = "";
        $temp = [];

        foreach ($this->members as $gr) {
            foreach ($gr as $m) {
                $temp[] = $m;
            }

        }

        for ($i = 0; $i < ($totalMembers > 4 ? 3 : 4); $i++) {
            $output .= $temp[$i]->getName()['first'];
            $output .= $this->getOperator($i);
        }
        if ($totalMembers > 4) {
            $output .= "$totalMembers others";
        }

        unset($totalMembers);
        unset($temp);

        return $output;
    }

    private function getOperator(int $iteration): ?string
    {
        switch ($iteration) {
            case 0 || 1:
                return ", ";
                break;
            case 2:
                return " and ";
                break;
            default:
                break;
        }
    }

    public function getLatestMessage(): ?string
    {
        if (!$this->messages) {
            return "Nothing has been written so far.";
        }

        usort($this->messages, function ($a, $b) {
            return $b->getSendAt() <=> $a->getSendAt();
        });

        $latest = $this->messages[0];

        if ($latest->getContent()['text'] !== "") {
            return $latest['text'];
        }

        $links = sizeof($latest->getContent()['links']);
        $files = sizeof($latest->getContent()['files']);
        if ($links > 0 && $files > 0) {
            return "{$latest->getSender()->getName()['first']} sent $links " . ($links === 1 ? 'link' : 'links') . " and $files " . ($files === 1 ? 'file' : 'files');
        }

        if ($links > 0) {
            return "{$latest->getSender()->getName()['first']} sent $links " . ($links === 1 ? 'link' : 'links');
        }

        if ($files > 0) {
            return "{$latest->getSender()->getName()['first']} sent $files " . ($files === 1 ? 'file' : 'files');
        }
    }

    public function getAmountOfUnseenMessages(): ?int
    {
        if (!$this->messages) {
            return 0;
        }

        (int) $unseenAmount = 0;
        usort($this->messages, function ($a, $b) {
            return $b->getSendAt() <=> $a->getSendAt();
        });

        foreach ($this->messages as $message) {
            foreach ($message->getSeen() as $user) {
                if ($user->getId() === $this->current->getId()) {
                    $unseenAmount++;
                }
            }
        }

        return $unseenAmount;
    }
}
