<?php

namespace App\CRUD;

use App\Form\NewChatType;
use App\Repository\ChatsRepository;
use App\Repository\UserRepository;
use App\Services\ChatServices;
use App\Services\UserServices;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class Chats extends AbstractController
{
    private $chatServices;
    private $userServices;
    private $em;
    private $cr;
    private $ur;

    public function __construct(ChatServices $chatServices, UserServices $userServices, EntityManagerInterface $em, ChatsRepository $cr, UserRepository $ur)
    {
        $this->chatServices = $chatServices;
        $this->userServices = $userServices;
        $this->em = $em;
        $this->cr = $cr;
        $this->ur = $ur;
    }

    /**
     * @Route("/chats/new", name="newChat", methods={"GET", "POST"})
     */
    public function create(Request $request, $error = null, ParameterBagInterface $pb): Response
    {
        $form = $this->createForm(NewChatType::class, null, ['currentUser' => $this->getUser()]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if ($chat = $this->chatServices->createChat($data, $this->getUser(), $pb)) {
                return $this->redirectToRoute('viewChat', ['id' => $chat]);
            } else {
                throw new Exception("Chat wasn't created. Refresh page and try again", 500);
            }
        }

        return $this->render('app/chats/new.html.twig', [
            'form' => $form->createView(),
            'error' => $error,
        ]);
    }

    /**
     * @Route("/chat/{id}", name="viewChat", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function view(int $id): Response
    {
        $chat = $this->cr->findOneBy(['id' => $id]);

        if (!$chat) {
            throw new Exception("Such chat is not existing", 404);
        }

        if (!$this->userServices->isUserInList($this->getUser(), $chat->getMembers())) {
            throw new Exception("You have no permission to do this", 403);
        }

        return $this->render('app/chats/view.html.twig', [
            'chat' => $chat,
        ]);
    }

    /**
     * @Route("/chat/open/{id}", name="openChat", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function openChat(int $id, ParameterBagInterface $pb): Response
    {
        $chatId = $this->chatServices->findChatId($id, $this->getUser());

        if ($chatId === 0) {
            $user = $this->ur->findOneBy(['id' => $id]);
            $chatId = $this->chatServices->createChat(['members' => [$user]], $this->getUser(), $pb);
        }

        return $this->redirectToRoute('viewChat', [
            'id' => $chatId,
        ]);
    }
}
