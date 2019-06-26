<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserLoginType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserController extends AbstractController
{
    /**
     * @Route("/user", name="user")
     */
    public function index()
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    /**
     * @Route("/user/login", name="login")
     */
    public function login(Request $request, AuthenticationUtils $authenticationUtils)
    {
        $user = new User();
        $userForm = $this->createForm(UserLoginType::class, $user);
        $userForm->handleRequest($request);
        $error = $authenticationUtils->getLastAuthenticationError();
        //dump($error);
        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render('user/index.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'userForm' => $userForm->createView(),
        ]);
    }

    /**
     * @Route(“/user/logout”, name=“logout”)
     */
    public function logout()
    {
        throw new \Exception("Logout");
    }
}
