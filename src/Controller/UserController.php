<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Services\VerifyService;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Form\LoginType;
use App\Form\RegisterType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class UserController extends AbstractController
{
    public function __construct(VerifyService $verify, RequestStack $request, SessionInterface $session, UserRepository $uR, EntityManagerInterface $em)
    {
        $this->verify = $verify->verify();
        $this->request = $request->getCurrentRequest();
        $this->session = $session;
        $this->uR = $uR;
        $this->em = $em;
    }

    /**
     * @Route("/u/login", name="login")
     */
    public function login()
    {
        if ($this->verify) return $this->redirectToRoute('homepage', []);

        $login = $this->createForm(LoginType::class);
        $login->handleRequest($this->request);

        if ($login->isSubmitted() && $login->isValid()) {
            $data = $login->getData();
            $account = $this->uR->findOneBy(['Login' => $data->getLogin()]);

            if ($account && $account->getPassword() === $data->getPassword()) {
                if ($account->getUserImg()) $account->setUserImg(stream_get_contents($account->getUserImg()));
                $this->session->set('user', $account);

                return $this->redirectToRoute('homepage', []);
            }
        }

        return $this->render('user/login.html.twig', [
            'login' => $login->createView()
        ]);
    }

    /**
     * @Route("/u/register", name="register")
     */
    public function register()
    {
        if ($this->verify) return $this->redirectToRoute('homepage', []);

        $register = $this->createForm(RegisterType::class);
        $register->handleRequest($this->request);

        if ($register->isSubmitted() && $register->isValid()) {
            $data = $register->getData();
            $taken = $this->uR->registerVerify([$data->getLogin(), $data->getEmail()]);

            if (!$taken) {
                $newUser = new User();
                $newUser->setLogin($data->getLogin());
                $newUser->setPassword($data->getPassword());
                $newUser->setEmail($data->getEmail());
                $newUser->setName($data->getName());
                $newUser->setSurname($data->getSurname());

                $this->em->persist($newUser);
                $this->em->flush();

                $this->addFlash('success', 'Your account has been successfully created.');
                return $this->redirectToRoute('login', []);
            } else {
                $this->addFlash('danger', 'E-mail or Login already taken. Try again with other values.');
            }
        }

        return $this->render('user/register.html.twig', [
            'register' => $register->createView()
        ]);
    }

    /**
     * @Route("/u/logout", name="logout")
     */
    public function logout()
    {
        if ($this->verify) $this->session->clear();

        return $this->redirectToRoute('login', []);
    }

    /**
     * @Route("/u/set-image", name="user-set-image", methods={"POST"})
     */
    public function setUserImage(Request $request, ParameterBagInterface $pb)
    {
        if (!$this->verify) return $this->redirectToRoute('login', []);

        $user = $this->uR->findOneBy(['id' => $this->session->get('user')->getId()]);
        $file = $request->files->get('profileImg');
        $newName = $user->getId() . '-' . uniqid() . '.' . $file->guessExtension();
        try {
            $file->move(
                'images/users/',
                $newName
            );
            if ($user->getUserImg()) {
                $filesystem = new Filesystem();
                $filesystem->remove($pb->get('kernel.project_dir') . '/public/images/users/' . stream_get_contents($user->getUserImg()));
            }
        } catch (FileException $e) {
            throw new FileException('Error occured while uploading. Error code: %d. Try again!', sprintf($e->getMessage()));
        }
        $user->setUserImg($newName);
        $this->session->set('user', $user);
        $this->em->flush();

        return new Response();
    }
}
