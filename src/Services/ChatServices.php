<?php

namespace App\Services;

use App\Interfaces\ChatServicesInterface;
use App\Repository\ChatsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ChatServices implements ChatServicesInterface
{
    private $cr;
    private $em;

    public function __construct(ChatsRepository $cr, EntityManagerInterface $em)
    {
        $this->cr = $cr;
        $this->em = $em;
    }

    public function createChat(array $data, object $currentUser, ParameterBagInterface $pb): ?int
    {
        if (sizeof($data['members']) < 1) {
            return null;
        } else {
            $chat = new \App\Entity\Chats;

            if (sizeof($data['members']) === 1) {
                $chat->setMembers(['admins' => [$currentUser, $data['members'][0]], 'members' => []]);
                $chat->setImage("/images/users/images/{$data['members'][0]->getImage()}");
            } else {
                $temp = [];
                $temp['admins'][] = $currentUser;
                foreach ($data['members'] as $user) {
                    $temp['members'][] = $user;
                }

                $chat->setMembers($temp);
                $chat->setImage("/images/chats/default.png");
            }

            $chat->setName($data['name'] ?? "");

            $this->em->persist($chat);
            $this->em->flush();

            $filesystem = new Filesystem;
            $filesystem->mkdir("{$pb->get('kernel.project_dir')}/public/images/chats/{$chat->getId()}");

            if ($data['image']) {
                try {
                    $new = "chatImage.{$data['image']->guessExtension()}";
                    $data['image']->move(
                        "images/chats/{$chat->getId()}",
                        $new
                    );
                } catch (FileException $e) {
                    throw new Exception("Chat has been created but there was problem with uploading your file. Try again in created chat", 500);
                }

                $chat->setImage("/images/chats/{$chat->getId()}/chatImage.{$data['image']->guessExtension()}");
                $this->em->flush();
            }

            return $chat->getId();
        }

        return null;
    }

    public function findChatId(int $id, object $currentUser): int
    {
        $chats = $this->cr->getUserChats($currentUser);

        foreach ($chats as $chat) {
            $members = $chat->getMembers();

            if (sizeof($members['admins']) === 2 && sizeof($members['members']) === 0) {
                $current = $looked = false;
                foreach ($members['admins'] as $member) {
                    if ($member->getId() === $currentUser->getId()) {
                        $current = true;
                    }

                    if ($member->getId() === $id) {
                        $looked = true;
                    }
                }

                if ($current && $looked) {
                    return $chat->getId();
                }
            }
        }

        return 0;
    }

    public function getCustomName(string $name, array $members, int $currentId): string
    {
        if ($name !== "") {
            return $name;
        }

        $totalMembers = sizeof($members['admins']) + sizeof($members['members']);
        $output = "";
        $temp = [];

        foreach ($members as $gr) {
            foreach ($gr as $m) {
                $temp[] = $m;
            }
        }

        if ($totalMembers === 2) {
            foreach ($members['admins'] as $ind => $user) {
                if ($user->getId() !== $currentId) {
                    return $members['admins'][$ind]->getFullName();
                }
            }
        }
        if ($totalMembers < 3) {
            for ($i = 0; $i < $totalMembers; $i++) {
                $output .= $temp[$i]->getName()['first'];
                $output .= $this->getOperator($i, $totalMembers);
            }
        }
        if ($totalMembers > 3) {
            for ($i = 0; $i < 4; $i++) {
                $output .= $temp[$i]->getName()['first'];
                $output .= $this->getOperator($i, $totalMembers);
            }
            $output .= ($totalMembers - 3) . " others";
        }

        unset($totalMembers);
        unset($temp);

        return $output;
    }

    private function getOperator(int $iteration, int $size): string
    {
        if ($iteration === $size - 2) {
            return " and ";
        } else if ($iteration !== $size - 1) {
            return ", ";
        }
        return "";
    }

    public function getLatestMessage(object $messages): string
    {
        if (sizeof($messages) === 0) {
            return "Nothing has been written so far.";
        }

        $msgs = [];

        foreach ($messages as $m) {
            $msgs[] = $m;
        }

        usort($msgs, function ($a, $b) {
            return $b->getSendAt() <=> $a->getSendAt();
        });

        $latest = $msgs[0];

        if ($latest->getContent()['text'] !== "") {
            return $latest->getContent()['text'];
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

    public function getLatestMessageDate(object $messages): ?\DateTimeInterface
    {
        if (sizeof($messages) === 0) {
            return null;
        }

        $msgs = [];

        foreach ($messages as $m) {
            $msgs[] = $m;
        }

        usort($msgs, function ($a, $b) {
            return $b->getSendAt() <=> $a->getSendAt();
        });

        return $msgs[0]->getSendAt();
    }

    public function getAmountOfUnseenMessages(object $messages, int $currentId): int
    {
        if (sizeof($messages) === 0) {
            return 0;
        }

        (int) $unseenAmount = 0;

        $msgs = [];

        foreach ($messages as $m) {
            $msgs[] = $m;
        }

        usort($msgs, function ($a, $b) {
            return $b->getSendAt() <=> $a->getSendAt();
        });

        foreach ($msgs as $message) {
            foreach ($message->getSeen() as $user) {
                if ($user->getId() === $currentId) {
                    $unseenAmount++;
                }
            }
        }

        return $unseenAmount;
    }

    public function getLastFileId(int $chatId): int
    {
        $chat = $this->cr->findOneBy(['id' => $chatId]);

        $msgs = [];

        foreach ($chat->getMessages() as $m) {
            $msgs[] = $m;
        }

        usort($msgs, function ($a, $b) {
            return $b->getSendAt() <=> $a->getSendAt();
        });

        foreach ($msgs as $mess) {
            if (sizeof($mess->getContent()['files']) > 0) {
                $filesSize = sizeof($mess->getContent()['files']);
                $latest = $mess->getContent()['files'][$filesSize - 1];

                $items = explode(".", $latest);

                return (int) $items[0];
            }
        }

        return 0;
    }

    public function getChatAmountOfFiles(int $chatId): int
    {
        $amount = 0;
        $chat = $this->cr->findOneBy(['id' => $chatId]);

        foreach ($chat->getMessages() as $message) {
            if (sizeof($message->getContent()['files']) > 0) {
                $amount += sizeof($message->getContent()['files']);
            }
        }

        return $amount;
    }
}
