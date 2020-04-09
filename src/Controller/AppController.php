<?php

namespace App\Controller;

use App\Entity\Chats;
use App\Entity\Messages;
use App\Form\ChatType;
use App\Repository\ChatsRepository;
use App\Repository\UserRepository;
use App\Services\ChatService;
use App\Services\VerifyService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
{
    public function __construct(VerifyService $verify, ChatService $chat, UserRepository $uR, EntityManagerInterface $em, SessionInterface $session, ChatsRepository $cR)
    {
        $this->verify = $verify->verify();
        $this->chat = $chat;
        $this->uR = $uR;
        $this->user = $session->get('user');
        $this->cR = $cR;
        $this->em = $em;
    }

    /**
     * @Route("/", name="homepage")
     */
    public function homepage()
    {
        if (!$this->verify) return $this->redirectToRoute('login', []);

        return $this->render('app/homepage.html.twig', [
            'chats' => $this->uR->findOneBy(['id' => $this->user->getId()])->getChats()
        ]);
    }

    /**
     * @Route("/new-chat", name="chatCreate")
     */
    public function newChat(Request $request)
    {
        if (!$this->verify) return $this->redirectToRoute('login', []);

        $newChat = $this->createForm(ChatType::class);
        $newChat->handleRequest($request);
        if ($newChat->isSubmitted() && $newChat->isValid()) {
            $data = $newChat->getData();

            //Checks if already exists chat containing same users as those from form
            $verify = $this->chat->checkIfChatExists($data['members']);
            if ($verify !== false) return $this->redirectToRoute('chat', ['hash' => $verify]);
            unset($verify);

            $chat = new Chats();
            //Generates chat hash used as url parameter
            $hash = $this->chat->generateChatHash();
            if ($this->cR->findOneBy(['chatHash' => $hash])) {
                $i = false;
                while ($i == false) {
                    $hash = $this->chat->generateChatHash();
                    if (!$this->cR->findOneBy(['chatHash' => $hash])) $i = true;
                }
            }
            $chat->setChatHash($hash);
            $chat->addMember($this->uR->findOneBy(['id' => $this->user->getId()]));
            foreach ($data['members'] as $member) {
                $chat->addMember($member);
            }
            $chat->setChatName($data['name']);

            $this->em->persist($chat);
            $this->em->flush();

            return $this->redirectToRoute('chat', ['hash' => $hash]);
        }

        return $this->render('app/newChat.html.twig', [
            'new' => $newChat->createView()
        ]);
    }

    /**
     * @Route("/chat/{hash}", name="chat")
     */
    public function chat(int $hash)
    {
        if (!$this->verify) return $this->redirectToRoute('login', []);

        //Get some basic info about chat channel like name or members
        $chat = $this->cR->findOneBy(['chatHash' => $hash]);
        foreach ($chat->getMessages() as $message) {
            $message->addDisplayed($this->uR->findOneBy(['id' => $this->user->getId()]));
        }
        $this->em->flush();
        $members = [];
        foreach ($chat->getMembers() as $member) {
            if ($member->getId() !== $this->user->getId())
                $members[] = [
                    'name' => $member->getName() . " " . $member->getSurname()
                ];
        }
        return $this->render('app/chat.html.twig', [
            'hash' => $hash,
            'name' => $chat->getChatName(),
            'members' => $members
        ]);
    }
    /**
     * @Route("/chat/{hash}/json", name="chatJSON", methods={"GET"})
     */
    public function chatJSON(int $hash)
    {
        if (!$this->verify) return $this->redirectToRoute('login', []);

        //Create internal API for getting messages
        $temp = $this->cR->findOneBy(['chatHash' => $hash]);
        $output = [];
        foreach ($temp->getMessages() as $message) {
            $output[] = [
                'content' => $message->getContent(),
                'date' => $message->getDate()->format('H:i:s d.m.Y'),
                'author' => $message->getSender()->getName(),
                'authorId' => $message->getSender()->getId()
            ];
        }
        unset($temp);

        return $this->json([json_encode($output)]);
    }

    /**
     * @Route("/chat/{hash}/send", name="sendMessage", methods={"POST"})
     */
    public function sendMessage(int $hash, Request $request)
    {
        if (!$this->verify) return $this->redirectToRoute('login', []);
        $date = new \DateTime();
        $content = $request->get('message');

        $message = new Messages();
        $message->setDate($date);
        $message->setContent($content);
        $message->setSender($this->uR->findOneBy(['id' => $this->user->getId()]));
        $message->setChat($this->cR->findOneBy(['chatHash' => $hash]));
        $message->addDisplayed($this->uR->findOneBy(['id' => $this->user->getId()]));

        $this->em->persist($message);
        $this->em->flush();

        return new Response();
    }

    /**
     * @Route("/chat/{hash}/name-set", name="setChatName", methods={"POST"})
     */
    public function setChatName(int $hash, Request $request)
    {
        if (!$this->verify) return $this->redirectToRoute('login', []);

        $name = $request->get('name');
        $chat = $this->cR->findOneBy(['chatHash' => $hash]);
        $chat->setChatName($name);
        $this->em->flush();

        return new Response();
    }
}
