<?php

namespace App\Services;

use App\Interfaces\ChatServicesInterface;
use App\Repository\ChatsRepository;

class ChatServices implements ChatServicesInterface
{
    private $cr;

    public function __construct(ChatsRepository $cr)
    {
        $this->cr = $cr;
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
}
