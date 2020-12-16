<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserController extends AbstractController
{
    /**
     * @Route("/u/login", name="login", methods={"GET", "POST"})
     */
    public function login(AuthenticationUtils $au)
    {
        return $this->render('user/login.html.twig', [
            'error' => $au->getLastAuthenticationError(),
            'username' => $au->getLastUsername(),
        ]);
    }

    /**
     * @Route("/u/logout", name="logout")
     */
    public function logout()
    {
        return $this->redirectToRoute('login', []);
    }
}
