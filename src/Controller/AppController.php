<?php

namespace App\Controller;

use App\Entity\Messages;
use App\Repository\MessagesRepository;
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
    public function __construct(VerifyService $verify, UserRepository $uR, EntityManagerInterface $em, SessionInterface $session, MessagesRepository $mR)
    {
        $this->verify = $verify->verify();
        $this->uR = $uR;
        $this->session = $session;
        $this->mR = $mR;
        $this->em = $em;
    }
    /**
     * @Route("/", name="homepage")
     */
    public function homepage()
    {
        if (!$this->verify) {
            return $this->redirectToRoute('login', []);
        }

        $newConvs = $this->uR->findPotential();
        $activeConvs = $this->uR->findConversations($this->session->get('user')->getId());

        return $this->render('app/homepage.html.twig', [
            'possibilities' => $newConvs,
            'active' => $activeConvs
        ]);
    }

    /**
     * @Route("/chat/{id}", name="chat")
     */
    public function chat(int $id)
    {
        if (!$this->verify) {
            return $this->redirectToRoute('login', []);
        }

        $receiver = $this->uR->findOneBy(['id' => $id]);
        $messages = $this->mR->getConversation($receiver->getId());

        return $this->render('app/chat.html.twig', [
            'partner' => $receiver->getName() . ' ' . $receiver->getSurname(),
            'messages' => $messages,
            'dataId' => $receiver->getId()
        ]);
    }

    /**
     * @Route("/chat/{id}/send", name="sendMessage", methods={"POST"})
     */
    public function sendMessage(int $id, Request $request)
    {
        if (!$this->verify) {
            return $this->redirectToRoute('login', []);
        }

        $receiver = $this->uR->findOneBy(['id' => $id]);
        $sender = $this->uR->findOneBy(['id' => $this->session->get('user')->getId()]);
        $date = new \DateTime();
        $content = $request->get('message');

        $message = new Messages();
        $message->setSource($sender);
        $message->setTarget($receiver);
        $message->setDate($date);
        $message->setContent($content);

        $this->em->persist($message);
        $this->em->flush();

        return new Response();
    }
}
