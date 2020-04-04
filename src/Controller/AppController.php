<?php

namespace App\Controller;

use App\Entity\Chats;
use App\Entity\Messages;
use App\Form\ChatType;
use App\Repository\ChatsRepository;
use App\Repository\UserRepository;
use App\Services\VerifyService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
{
    public function __construct(VerifyService $verify, UserRepository $uR, EntityManagerInterface $em, SessionInterface $session, ChatsRepository $cR)
    {
        $this->verify = $verify->verify();
        $this->uR = $uR;
        $this->session = $session;
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
            'chats' => $this->uR->findOneBy(['id' => $this->session->get('user')->getId()])->getChats()
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

            $verify = $this->checkIfChatExists($data['members']);
            if ($verify !== false) return $this->redirectToRoute('chat', ['hash' => $verify]);
            unset($verify);

            $chat = new Chats();
            $hash = $this->generateChatHash();
            if ($this->cR->findOneBy(['chatHash' => $hash])) {
                $i = false;
                while ($i == false) {
                    $hash = $this->generateChatHash();
                    if (!$this->cR->findOneBy(['chatHash' => $hash])) $i = true;
                }
            }
            $chat->setChatHash($hash);
            $chat->addMember($this->uR->findOneBy(['id' => $this->session->get('user')->getId()]));
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

    private function generateChatHash()
    {
        $output = rand(0, 9);
        for ($i = 0; $i < 15; $i++) {
            $output .= rand(0, 9);
        }
        return (int) $output;
    }
    private function checkIfChatExists($members, $temp = [])
    {
        foreach ($members as $m) {
            $temp[] = $m->getId();
        }
        $members = $temp;
        sort($members);

        $chats = $this->uR->findOneBy(['id' => $this->session->get('user')->getId()])->getChats();
        foreach ($chats as $chat) {
            $temp = array();
            foreach ($chat->getMembers() as $member) {
                if ($member->getId() !== $this->session->get('user')->getId()) $temp[] = $member->getId();
            }
            sort($temp);
            if ($members === $temp) return $chat->getChatHash();
        }
        return false;
    }

    /**
     * @Route("/chat/{hash}", name="chat")
     */
    public function chat(int $hash)
    {
        if (!$this->verify) return $this->redirectToRoute('login', []);

        return $this->render('app/chat.html.twig', []);
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
        $message->setSender($this->uR->findOneBy(['id' => $this->session->get('user')->getId()]));
        $message->setChat($this->cR->findOneBy(['chatHash' => $hash]));

        $this->em->persist($message);
        $this->em->flush();

        return new Response();
    }
}
