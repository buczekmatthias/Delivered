<?php

namespace App\Controller;

use App\Repository\ChatsRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
{
    private $ur;
    private $cr;

    public function __construct(UserRepository $ur, ChatsRepository $cr)
    {
        $this->ur = $ur;
        $this->cr = $cr;
    }

    /**
     * @Route("/", name="homepage", methods={"GET"})
     */
    public function homepage()
    {
        $temp = [];
        if ($this->getUser()->getFriends()) {
            foreach ($this->getUser()->getFriends() as $friend) {
                $temp[] = $friend->getLogin();
            }
        }

        return $this->render('app/homepage.html.twig', [
            'newToAdd' => $this->ur->getUnfriendedUsers($this->getUser()->getLogin(), $temp),
            'chats' => $this->cr->getUsersChatsByActivity($this->getUser()),
        ]);
    }

    /**
     * @Route("/chats", name="chats", methods={"GET"})
     */
    public function chats()
    {
        return $this->render('app/chats.html.twig', [
            'chats' => $this->cr->getUsersChatsByActivity($this->getUser()),
        ]);
    }

    /**
     * @Route("/friends", name="friends", methods={"GET"})
     */
    public function friends()
    {
        return $this->render('app/friends.html.twig', []);
    }
}
