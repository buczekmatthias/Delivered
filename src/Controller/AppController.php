<?php

namespace App\Controller;

use App\Entity\ChatFiles;
use App\Entity\Chats;
use App\Entity\JoinRequests;
use App\Entity\Messages;
use App\Form\ChatType;
use App\Repository\ChatsRepository;
use App\Repository\JoinRequestsRepository;
use App\Repository\UserRepository;
use App\Services\ChatService;
use App\Services\VerifyService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
{
    // TODO: Popup if there is already a chat that uesr is trying to create but doen't belong to
    // TODO: Join requests
    public function __construct(VerifyService $verify, JoinRequestsRepository $jrR, ChatService $chat, UserRepository $uR, EntityManagerInterface $em, SessionInterface $session, ChatsRepository $cR)
    {
        $this->verify = $verify->verify();
        $this->chat = $chat;
        $this->uR = $uR;
        $this->user = $session->get('user');
        $this->cR = $cR;
        $this->em = $em;
        $this->jrR = $jrR;
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
    public function newChat(Request $request, $popData = false)
    {
        if (!$this->verify) return $this->redirectToRoute('login', []);

        $newChat = $this->createForm(ChatType::class);
        $newChat->handleRequest($request);

        if ($newChat->isSubmitted() && $newChat->isValid()) {
            $data = $newChat->getData();

            //Checks if already exists chat containing same users as those from form
            $verify = $this->chat->checkIfChatExists($data['members']);
            if ($verify !== false) {
                if (array_key_exists('hash', $verify)) return $this->redirectToRoute('chat', ['hash' => $verify]);
                else {
                    $popData = $this->cR->findOneBy(['chatHash' => $verify['request']]);
                    if ($popData->getImage()) $popData->setImage(stream_get_contents($popData->getImage()));
                }
            } else {
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
            unset($verify);
        }

        return $this->render('app/newChat.html.twig', [
            'new' => $newChat->createView(),
            'pop' => $popData
        ]);
    }

    protected function requestPopup(int $hash)
    {
        if (!$this->verify) return $this->redirectToRoute('login', []);

        $chat = $this->cR->findOneBy(['chatHash' => $hash]);
        $chat->setImage(stream_get_contents($chat->getImage()));
        return $this->render('app/chatpopup.html.twig', [
            'chatPop' => $chat
        ]);
    }

    /**
     * @Route("/chat/{hash}/request", name="requestChatJoin", methods={"POST"})
     */
    public function requestJoin(int $hash)
    {
        if (!$this->verify) return $this->redirectToRoute('login', []);

        $chat = $this->cR->findOneBy(['chatHash' => $hash]);
        $user = $this->uR->findOneBy(['id' => $this->user->getId()]);

        $chatRequest = new JoinRequests();
        $chatRequest->setUser($user);
        $chatRequest->setChat($chat);

        $this->em->persist($chatRequest);
        $this->em->flush();
        return new Response();
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
            $members[] = [
                'name' => $member->getName() . " " . $member->getSurname()
            ];
        }
        return $this->render('app/chat.html.twig', [
            'hash' => $hash,
            'name' => $chat->getChatName(),
            'members' => $members,
            'image' => $chat->getImage() ? stream_get_contents($chat->getImage()) : null
        ]);
    }

    /**
     * @Route("/chat/{hash}/requestList", name="chatRequestList")
     */
    public function requestList(int $hash)
    {
        if (!$this->verify) return $this->redirectToRoute('login', []);

        $list = [];
        $temp = $this->jrR->findBy(['chat.chat_hash' => $hash]);
        foreach ($temp as $t) {
            $user = $t->getUser();
            $user->setUserImg(stream_get_contents($user->getUserImg()));
            $list[] = $user;
        }
        unset($temp);
        unset($user);

        return $this->render('requestlist.html.twig', [
            'list' => $list
        ]);
    }

    /**
     * @Route("/request/{reqId}/approve", name="approveRequest", methods={"POST"})
     */
    public function approveRequest(int $reqId)
    {
        if (!$this->verify) return $this->redirectToRoute('login', []);

        $request = $this->jrR->findOneBy(['id' => $reqId]);
        $chat = $request->getChat();
        $chat->addMember($request->getUser());
        $this->em->remove($request);
        $this->em->flush();

        return new Response();
    }

    /**
     * @Route("/request/{reqId}/remove", name="removeRequest", methods={"POST"})
     */
    public function removeRequest(int $reqId)
    {
        if (!$this->verify) return $this->redirectToRoute('login', []);

        $request = $this->jrR->findOneBy(['id' => $reqId]);
        $this->em->remove($request);
        $this->em->flush();

        return new Response();
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
                'content' => $message->getContent() ? $message->getContent() : null,
                'date' => $message->getDate()->format('H:i:s d.m.Y'),
                'author' => $message->getSender()->getName(),
                'authorId' => $message->getSender()->getId(),
                'file' => $message->getFile() ? stream_get_contents($message->getFile()->getFile()) : null
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
        $content = $request->request->get('message');
        $file = $request->files->get('file');
        $chat = $this->cR->findOneBy(['chatHash' => $hash]);
        $user = $this->uR->findOneBy(['id' => $this->user->getId()]);

        $message = new Messages();
        $message->setDate($date);
        if ($content) $message->setContent($content);
        if ($file) {
            $f = new ChatFiles();
            $f->setUser($user);
            $f->setChat($chat);
            $newName = date('Ymd') . '-' . uniqid() . '.' . $file->guessExtension();
            try {
                $file->move(
                    'images/chatFiles/',
                    $newName
                );
            } catch (FileException $e) {
                throw new FileException('Error occured while uploading. Error code: %d. Try again!', sprintf($e->getMessage()));
            }
            $f->setFile($newName);
            $this->em->persist($f);
            $this->em->flush();
            $message->setFile($f);
        }
        $message->setSender($user);
        $message->setChat($chat);
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

    /**
     * @Route("/chat/{hash}/set-image", name="setImageChat", methods={"POST"})
     */
    public function setImageChat(int $hash, Request $request, ParameterBagInterface $pb)
    {
        if (!$this->verify) return $this->redirectToRoute('login', []);

        $chat = $this->cR->findOneBy(['chatHash' => $hash]);
        $file = $request->files->get('file');
        $newName = $chat->getChatHash() . '-' . uniqid() . '.' . $file->guessExtension();
        try {
            $file->move(
                'images/chat/',
                $newName
            );
            if ($chat->getImage()) {
                $filesystem = new Filesystem();
                $filesystem->remove($pb->get('kernel.project_dir') . '/public/images/chat/' . stream_get_contents($chat->getImage()));
            }
        } catch (FileException $e) {
            throw new FileException('Error occured while uploading. Error code: %d. Try again!', sprintf($e->getMessage()));
        }
        $chat->setImage($newName);
        $this->em->flush();

        return new Response();
    }
}
