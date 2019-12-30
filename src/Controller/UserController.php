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
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Faker\Factory;

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
        if ($this->verify) {
            return $this->redirectToRoute('homepage', []);
        }

        $login = $this->createForm(LoginType::class);
        $login->handleRequest($this->request);

        if ($login->isSubmitted() && $login->isValid()) {
            $data = $login->getData();
            $account = $this->uR->findOneBy(['Login' => $data->getLogin()]);

            if ($account && $account->getPassword() === $data->getPassword()) {
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
        if ($this->verify) {
            return $this->redirectToRoute('homepage', []);
        }

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
        if ($this->verify) {
            $this->session->clear();

            return $this->redirectToRoute('login', []);
        }
    }
}
