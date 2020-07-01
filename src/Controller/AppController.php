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
use Error;
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
    // TODO: User can check created requests
    // TODO: Adding users (JSON + js + dynamic search)
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
                if (sizeof($data['members']) + 1 == 2) {
                    $chat->addAdmin($this->uR->findOneBy(['id' => $this->user->getId()]));
                    $chat->addAdmin($this->uR->findOneBy(['id' => $data['members'][0]->getId()]));
                } else $chat->addAdmin($this->uR->findOneBy(['id' => $this->user->getId()]));

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

    /**
     * @Route("/chat/{hash}", name="chat")
     */
    public function chat(int $hash, $admin = false)
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
                'id' => $member->getId(),
                'image' => $member->getUserImg() ? stream_get_contents($member->getUserImg()) : null,
                'name' => $member->getName() . " " . $member->getSurname()
            ];
        }
        foreach ($chat->getAdmins() as $admin) {
            if ($admin->getId() === $this->user->getId()) {
                $admin = true;
                break;
            }
        }
        return $this->render('app/chat.html.twig', [
            'hash' => $hash,
            'name' => $chat->getChatName(),
            'members' => $members,
            'image' => $chat->getImage(),
            'admin' => $admin,
            'messages' => $chat->getMessages()
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
     * @Route("/chat/{hash}/json/request-list", name="chatRequestList", methods={"GET"})
     */
    public function requestList(int $hash)
    {
        if (!$this->verify) return $this->redirectToRoute('login', []);

        $list = [];
        $temp = $this->jrR->getChatRequests($hash);
        foreach ($temp as $chat) {
            $user = $chat->getUser();
            $list[] = [
                'request-id' => $chat->getId(),
                'image' => $user->getUserImg() ? stream_get_contents($user->getUserImg()) : null,
                'name' => $user->getName() . ' ' . $user->getSurname()
            ];
        }

        return $this->json([json_encode($list)]);
    }

    /**
     * @Route("/request/{reqId}/approve", name="approveRequest", methods={"POST"})
     */
    public function approveRequest(int $reqId)
    {
        if (!$this->verify) return $this->redirectToRoute('login', []);

        $request = $this->jrR->findOneBy(['id' => $reqId]);
        $request->getChat()->addMember($request->getUser());

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
     * @Route("/current-user", name="messageJSON", methods={"GET"})
     */
    public function userJSON()
    {
        if (!$this->verify) return $this->redirectToRoute('login', []);

        //Create internal API for current user

        return $this->json([json_encode(['id' => $this->user->getId(), 'name' => $this->user->getName(), 'img' => $this->user->getUserImg()])]);
    }

    /**
     * @Route("/chat/{hash}/json/members-to-add", name="memberToAdd", methods={"GET"})
     */
    public function memberToAdd(int $hash, $admin = false)
    {
        if (!$this->verify) return $this->redirectToRoute('login', []);

        $chat = $this->cR->findOneBy(['chatHash' => $hash]);
        foreach ($chat->getAdmins() as $admin) {
            if ($admin->getId() === $this->user->getId()) {
                $admin = true;
                break;
            }
        }
        if ($admin) {
            $users = $this->uR->findAll();
            $temp = [];
            foreach ($users as $user) {
                $temp[] = $user->getId();
            }
            sort($temp);
            $members = [];
            foreach ($chat->getMembers() as $member) {
                $members[] = $member->getId();
            }
            sort($members);
            $nonMemebers = [];
            foreach ($temp as $user) {
                if (!in_array($user, $members)) {
                    foreach ($users as $usr) {
                        if ($usr->getId() === $user) {
                            $nonMemebers[] = [
                                'id' => $usr->getId(),
                                'name' => $usr->getName() . ' ' . $usr->getSurname(),
                                'image' => stream_get_contents($usr->getUserImg())
                            ];
                            break;
                        }
                    }
                }
            }

            return $this->json([json_encode($nonMemebers)]);
        } else {
            throw new Error('You got no permission to get this', 403);
        }
    }

    /**
     * @Route("/chat/{hash}/{member}/delete", name="deleteChatMember", methods={"POST"})
     */
    public function deleteChatMember(int $hash, int $member, $admin = false, ParameterBagInterface $pb)
    {
        if (!$this->verify) return $this->redirectToRoute('login', []);

        $chat = $this->cR->findOneBy(['chatHash' => $hash]);
        foreach ($chat->getAdmins() as $admin) {
            if ($admin->getId() === $this->user->getId()) {
                $admin = true;
                break;
            }
        }
        if ($admin) {
            $member = $this->uR->findOneBy(['id' => $member]);
            $chat->removeMember($member);
            foreach ($chat->getAdmins() as $admin) {
                if ($admin->getId() === $member->getId()) {
                    $chat->removeAdmin($member);
                    break;
                }
            }
            if (sizeof($chat->getMembers()) < 2) {
                foreach ($chat->getMessages() as $m) {
                    $this->em->remove($m);
                }
                $filesystem = new Filesystem();

                foreach ($chat->getChatFiles() as $f) {
                    $filesystem->remove($pb->get('kernel.project_dir') . '/public/images/chatFiles/' . stream_get_contents($f->getFile()));
                    $this->em->remove($f);
                }
                foreach ($chat->getJoinRequests() as $r) {
                    $this->em->remove($r);
                }
                if ($chat->getImage()) {
                    $filesystem->remove($pb->get('kernel.project_dir') . '/public/images/chat/' . stream_get_contents($chat->getImage()));
                }
                $this->em->remove($chat);
            }
            $this->em->flush();
        }

        return new Response();
    }

    /**
     * @Route("/chat/{hash}/send-message", name="sendMessage", methods={"POST"})
     */
    public function sendMessage(int $hash, Request $request)
    {
        if (!$this->verify) return $this->redirectToRoute('login', []);

        $content = $request->request->get('message');
        $chat = $this->cR->findOneBy(['chatHash' => $hash]);
        $user = $this->uR->findOneBy(['id' => $this->user->getId()]);

        $message = new Messages();
        $message->setDate(new \DateTime());
        $message->setContent($content);
        $message->setSender($user);
        $message->setChat($chat);
        $message->addDisplayed($user);

        $this->em->persist($message);
        $this->em->flush();

        return new Response();
    }

    /**
     * @Route("/chat/{hash}/send-file", name="sendFile", methods={"POST"})
     */
    public function sendFile(int $hash, Request $request)
    {
        if (!$this->verify) return $this->redirectToRoute('login', []);

        $file = $request->request->get('file');
        $chat = $this->cR->findOneBy(['chatHash' => $hash]);
        $user = $this->uR->findOneBy(['id' => $this->user->getId()]);

        $message = new Messages;
        $message->setDate(new \DateTime());
        $message->setSender($user);
        $message->setChat($chat);
        $message->addDisplayed($user);

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
        $message->setFile($f);

        $this->em->persist($f);
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
