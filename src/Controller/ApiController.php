<?php

namespace App\Controller;

use App\Entity\Invitations;
use App\Entity\Notifications;
use App\Repository\ChatsRepository;
use App\Repository\InvitationsRepository;
use App\Repository\UserRepository;
use App\Services\ChatServices;
use App\Services\UserServices;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    private $ir;
    private $ur;
    private $cr;
    private $em;
    private $userServices;
    private $chatServices;

    public function __construct(UserServices $userServices, ChatServices $chatServices, InvitationsRepository $ir, UserRepository $ur, ChatsRepository $cr, EntityManagerInterface $em)
    {
        $this->ir = $ir;
        $this->ur = $ur;
        $this->cr = $cr;
        $this->em = $em;
        $this->userServices = $userServices;
        $this->chatServices = $chatServices;
    }

    /**
     * @Route("/api/friends/send-invitation", methods={"POST"})
     */
    public function friendsInvitationSend(Request $request): Response
    {
        (int) $userId = $request->request->get("userId");

        if ($this->userServices->checkIfUserInFriends($this->getUser(), $userId)) {
            return new Response("This user is already on your friends list", 500);
        }

        if ($this->ir->findOneBy(['sender' => $this->getUser()->getId(), 'toWho' => $userId])) {
            return new Response("There is awaiting invitation between you and this users", 409);
        }

        $invitation = new Invitations;
        $invitation->setSender($this->getUser());
        $invitation->setToWho($this->ur->findOneBy(['id' => $userId]));
        $invitation->setRequestedAt(new \DateTime);

        $this->em->persist($invitation);
        $this->em->flush();

        return new Response();
    }

    /**
     * @Route("/api/friends/remove-invitation", methods={"POST"})
     */
    public function friendsRemoveInvitation(Request $request): Response
    {
        (int) $userId = $request->request->get("userId");

        $invitation = $this->ir->findOneBy(['sender' => $this->getUser()->getId(), 'toWho' => $userId]);

        if (!$invitation) {
            return new Response("There is no invitation between you and this users", 409);
        }

        $this->em->remove($invitation);
        $this->em->flush();

        return new Response();
    }

    /**
     * @Route("/api/friends/invitation/accept", methods={"POST"})
     */
    public function acceptFriendsInvitation(Request $request): Response
    {
        (int) $invitationId = $request->request->get("invitationId");

        $invitation = $this->ir->findOneBy(['id' => $invitationId]);

        if (!$invitation) {
            return new Response("There is no invitation between you and this users", 409);
        }

        $sender = $this->ur->findOneBy(['id' => $invitation->getSender()->getId()]);
        $receiver = $this->ur->findOneBy(['id' => $invitation->getToWho()->getId()]);

        $sender->addFriend($receiver);
        $receiver->addFriend($sender);

        $notification = new Notifications;
        $notification->setToWho($sender);
        $notification->setContent("<img src='{$receiver->getImage()}'><p>{$receiver->getName()['first']} accepted your friend invitation</p>");
        $notification->setReceivedAt(new \DateTime);

        $this->em->persist($notification);
        $this->em->remove($invitation);
        $this->em->flush();

        return new Response();
    }

    /**
     * @Route("/api/friends/invitation/refuse", methods={"POST"})
     */
    public function refuseFriendsInvitation(Request $request): Response
    {
        (int) $invitationId = $request->request->get("invitationId");

        $invitation = $this->ir->findOneBy(['id' => $invitationId]);

        if (!$invitation) {
            return new Response("There is no invitation between you and this users", 409);
        }

        $this->em->remove($invitation);
        $this->em->flush();

        return new Response();
    }

    /**
     * @Route("/api/friends/remove-friend", methods={"POST"})
     */
    public function removeFriend(Request $request): Response
    {
        (int) $friendId = $request->request->get("friendId");

        $friends = $this->userServices->getFriends($this->getUser());

        if (empty($friends)) {
            return new Response("You have no friends on your list", 500);
        }

        $friend = $this->ur->findOneBy(['id' => $friendId]);

        if (!$friend) {
            return new Response("No user is matching", 404);
        }

        $userFriends = $friend->getFriends();

        if (empty($userFriends)) {
            return new Response("This user have no friends", 500);
        }

        $user = $this->ur->findOneBy(['id' => $this->getUser()->getId()]);

        foreach ($friends as $index => $value) {
            if ($value->getId() == $friendId) {
                array_splice($friends, $index, 1);
            }
        }

        $user->setFriends($friends);

        foreach ($userFriends as $index => $value) {
            if ($value->getId() === $this->getUser()->getId()) {
                array_splice($userFriends, $index, 1);
            }
        }

        $friend->setFriends($userFriends);

        $this->em->flush();

        return new Response();
    }

    /**
     * @Route("/api/notifications/view", methods={"POST"})
     */
    public function seeNotifications(): Response
    {
        $user = $this->ur->findOneBy(['id' => $this->getUser()->getId()]);

        foreach ($user->getNotifications() as $noti) {
            $noti->setSeen(true);
        }

        $this->em->flush();

        return new Response();
    }

    /**
     * @Route("/api/invitations/view", methods={"POST"})
     */
    public function seeInvitations(): Response
    {
        $user = $this->ur->findOneBy(['id' => $this->getUser()->getId()]);

        foreach ($user->getReceivedInvitations() as $inv) {
            $inv->setSeen(true);
        }

        $this->em->flush();

        return new Response();
    }

    /**
     * @Route("/api/messages/send/{id}", methods={"POST"}, requirements={"id"="\d+"})
     */
    public function sendMessage(int $id, Request $request): Response
    {
        $content = $request->request->get('content');
        $files = $request->files->get('file');

        $fls = [];

        if ($files) {
            $last = $this->chatServices->getLastFileId($id);
            foreach ($files as $file) {
                try {
                    $name = ($last + 1) . ".{$file->guessExtension()}";

                    $file->move(
                        "images/chats/$id/",
                        $name
                    );

                    $last = $last + 1;
                    $fls[] = $name;
                } catch (FileException $e) {
                    throw new Exception("One of files wasn't able to be uploaded. Please refresh page and try again", 500);
                }
            }
        }
        $now = new \DateTime();

        $message = new \App\Entity\Messages;
        $message->setContent([
            'text' => $content,
            'links' => [],
            'files' => $fls,
        ]);
        $message->setSender($this->getUser());
        $message->setChat($this->cr->findOneBy(['id' => $id]));
        $message->setSendAt($now);

        $this->em->persist($message);
        $this->em->flush();

        return $this->json([
            'chatId' => $id,
            'sender' => [
                'id' => $this->getUser()->getId(),
                'name' => $this->getUser()->getFullName(),
                'img' => $this->getUser()->getImage(),
            ],
            'content' => $message->getContent(),
            'date' => date_format($now, "d-m-Y, H:i"),
        ]);
    }

    /**
     * @Route("/api/messages/get/{id}", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function getChatMessages(int $id): Response
    {
        $chat = $this->cr->findOneBy(['id' => $id]);

        $messages = [];

        foreach ($chat->getMessages() as $m) {
            $date = $m->getSendAt();

            $messages[] = [
                'sender' => [
                    'id' => $m->getSender()->getId(),
                    'img' => $m->getSender()->getImage(),
                    'name' => $m->getSender()->getFullName(),
                ],
                'content' => $m->getContent(),
                'date' => date_format($date, "d-m-Y, H:i"),
            ];
        }

        return $this->json($messages);
    }

    /**
     * @Route("/getUserId", methods={"GET"})
     */
    public function getUserId(): Response
    {
        return new Response($this->getUser()->getId());
    }
}
