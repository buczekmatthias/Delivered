<?php

namespace App\Services;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ChatService
{
    public function __construct(SessionInterface $session, UserRepository $uR)
    {
        $this->user = $session->get('user');
        $this->uR = $uR;
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
        sort($members);

        //Get all chats and check if in any of them there is same set of users
        $chats = $this->uR->findOneBy(['id' => $this->user->getId()])->getChats();
        foreach ($chats as $chat) {
            $temp = array();
            foreach ($chat->getMembers() as $member) {
                if ($member->getId() !== $this->user->getId()) $temp[] = $member->getId();
            }
            sort($temp);
            if ($members === $temp) return $chat->getChatHash();
        }
        return false;
    }
}
