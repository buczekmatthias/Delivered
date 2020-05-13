<?php

namespace App\Services;

use App\Repository\ChatsRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ChatService
{
    public function __construct(SessionInterface $session, UserRepository $uR, ChatsRepository $cR)
    {
        $this->user = $session->get('user');
        $this->uR = $uR;
        $this->cR = $cR;
    }
    public function generateChatHash()
    {
        $output = rand(0, 9);
        for ($i = 0; $i < 15; $i++) {
            $output .= rand(0, 9);
        }
        return (int) $output;
    }
    public function checkIfChatExists($members, $temp = [])
    {
        //Get users from form and sort them
        foreach ($members as $m) {
            $temp[] = $m->getId();
        }
        $members = $temp;
        $members[] = $this->user->getId();
        sort($members);

        //Get all chats and check if in any of them there is same set of users
        $chats = $this->cR->findAll();
        foreach ($chats as $chat) {
            $temp = array();
            foreach ($chat->getMembers() as $member) {
                $temp[] = $member->getId();
            }
            sort($temp);
            if ($members === $temp) return ['hash' => $chat->getChatHash()];
            if (sizeof($temp) > 2) {
                $merge = array_merge(array_diff($members, $temp), array_diff($temp, $members));
                if (sizeof($merge) === 1 && in_array($this->user->getId(), $merge)) return ['request' => $chat->getChatHash()];
            }
        }
        return false;
    }
}
