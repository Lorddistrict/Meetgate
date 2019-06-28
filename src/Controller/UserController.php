<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Swift_Mailer;
use Swift_Message;
use App\Form\UserLoginType;
use App\Form\UserRegisterType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserController extends AbstractController
{
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

    public function register(
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder,
        Swift_Mailer $mailer,
        TokenGeneratorInterface $tokenGenerator
    ) :Response {

        /** @var User $user */
        $user = new User();

        $form = $this->createForm(UserRegisterType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $token = $tokenGenerator->generateToken();

            /** @var User $user */
            $user = $form->getData();
            $user->setRoles(['ROLE_USER']);
            $user->setCertifiedToken($token);
            $password = $passwordEncoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            /** @var Swift_Message $message */
            $message = (new Swift_Message('Validate your account'));
            $message->setFrom('contact@betrocket.com');
            $message->setTo($user->getEmail());
            $message->setBody(
                $this->renderView('email/registerValider.html.twig', [
                    'name' => $user->getName(),
                    'certified_token' => $token,
                    'randomString' => $token
                ]),
                'text/html'
            );
            $mailer->send($message);

            $this->addFlash('success', 'Inscription OK - Connectez vous !');
        }
        return $this->render(
            'user/register.html.twig',
            ['form' => $form->createView()]
        );
    }

    public function confirm(Request $request): Response
    {
        $certification = $request->attributes->get('certified_token');
        $form = $this->createForm(UserRegisterType::class);
        $entityManager = $this->getDoctrine()->getManager();
        $userRepository = $entityManager->getRepository(User::class);

        /**
         * @var User $user
         */
        $user = $userRepository->findOneBy(['certifiedToken' => $certification]);

        if (!empty($user)) {
            $user->setIsCertified(true);
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->render('user/confirm.html.twig', array(
                'UserRegistrationForm' => $form->createView(),
                'exist' => true
            ));
        }
        return $this->render('user/confirm.html.twig', array(
            'UserRegistrationForm' => $form->createView(),
            'exist' => false
        ));
    }

    public function logout()
    {
        throw new \Exception("Logout");
    }

//    public function accountInfo()
//    {
//        // allow any authenticated user - we don't care if they just
//        // logged in, or are logged in via a remember me cookie
//        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
//
//        // ...
//    }
//
//    public function resetPassword()
//    {
//        // require the user to log in during *this* session
//        // if they were only logged in via a remember me cookie, they
//        // will be redirected to the login page
//        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
//    }
}
