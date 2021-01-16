<?php

namespace App\CRUD;

use App\Form\NewChatType;
use App\Repository\ChatsRepository;
use App\Services\ChatServices;
use App\Services\UserServices;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class Chats extends AbstractController
{
    private $chatServices;
    private $userServices;
    private $em;
    private $cr;

    public function __construct(ChatServices $chatServices, UserServices $userServices, EntityManagerInterface $em, ChatsRepository $cr)
    {
        $this->chatServices = $chatServices;
        $this->userServices = $userServices;
        $this->em = $em;
        $this->cr = $cr;
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

            if (sizeof($data['members']) < 1) {
                $error = "Invalid amount of members";
            } else {
                $chat = new \App\Entity\Chats($this->chatServices);

                if (sizeof($data['members']) === 1) {
                    $chat->setMembers(['admins' => [$this->getUser(), $data['members'][0]], 'members' => []]);
                    $chat->setImage("/images/users/images/{$data['members'][0]->getImage()}");
                } else {
                    $temp = [];
                    $temp['admins'][] = $this->getUser();
                    foreach ($data['members'] as $user) {
                        $temp['members'][] = $user;
                    }

                    $chat->setMembers($temp);
                    $chat->setImage("/images/chats/default.png");
                }

                $chat->setName($data['name'] ?? "");

                $this->em->persist($chat);
                $this->em->flush();

                $filesystem = new Filesystem;
                $filesystem->mkdir("{$pb->get('kernel.project_dir')}/public/images/chats/{$chat->getId()}");

                if ($data['image']) {
                    try {
                        $new = "chatImage.{$data['image']->guessExtension()}";
                        $data['image']->move(
                            "images/chats/{$chat->getId()}",
                            $new
                        );
                    } catch (FileException $e) {
                        throw new Exception("Chat has been created but there was problem with uploading your file. Try again in created chat", 500);
                    }

                    $chat->setImage("/images/chats/{$chat->getId()}/chatImage.{$data['image']->guessExtension()}");
                    $this->em->flush();
                }

                return $this->redirectToRoute('viewChat', ['id' => $chat->getId()]);
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
}
