<?php

namespace App\Controller;

use App\Repository\ChatsRepository;
use App\Repository\UserRepository;
use App\Services\UserServices;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
{
    private $ur;
    private $cr;
    private $userServices;

    public function __construct(UserRepository $ur, ChatsRepository $cr, UserServices $userServices)
    {
        $this->ur = $ur;
        $this->cr = $cr;
        $this->userServices = $userServices;
    }

    /**
     * @Route("/", name="homepage", methods={"GET"})
     */
    public function homepage(): Response
    {
        return $this->render('app/homepage.html.twig', [
            'newToAdd' => $this->ur->getUnfriendedUsers($this->getUser()),
            'chats' => $this->cr->getUsersChatsByActivity($this->getUser()),
            'activeFriends' => $this->userServices->getActiveFriends($this->getUser()),
        ]);
    }

    /**
     * @Route("/chats", name="chats", methods={"GET"})
     */
    public function chats(): Response
    {
        return $this->render('app/chats/chats.html.twig', [
            'chats' => $this->cr->getUsersChatsByActivity($this->getUser()),
        ]);
    }

    /**
     * @Route("/friends", name="friends", methods={"GET"})
     */
    public function friends(): Response
    {
        return $this->render('app/friends.html.twig', []);
    }
}
