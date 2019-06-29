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
        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'userForm' => $userForm->createView(),
        ]);
    }

    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, Swift_Mailer $mailer, TokenGeneratorInterface $tokenGenerator) :Response {

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
            $message->setFrom('contact@meetgate.com');
            $message->setTo($user->getEmail());
            $message->setBody(
                $this->render('email/registration.html.twig', [
                    'firstname' => $user->getFirstname(),
                    'certified_token' => $token,
                ]),
                'text/html'
            );
            $mailer->send($message);

//            Keep it
//            $this->addFlash('success', 'Inscription OK - Connectez vous !');

            return $this->render('security/preconfirm.html.twig', [
                'email' => $user->getEmail(),
            ]);
        }
        return $this->render('security/register.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function confirm(Request $request): Response
    {
        $token = $request->attributes->get('token');

        if(is_null($token)){
            return $this->render('security/confirm.html.twig', array(
                'valid' => false,
            ));
        }

        $em = $this->getDoctrine()->getManager();

        /** @var UserRepository $userRepository */
        $userRepository = $em->getRepository(User::class);

        /** @var User $user */
        $user = $userRepository->findOneBy(['certifiedToken' => $token]);

        if (!empty($user)) {
            $user->setIsCertified(true);
            $em->persist($user);
            $em->flush();
            return $this->render('security/confirm.html.twig', array(
                'valid' => true,
            ));
        }
        return $this->render('security/confirm.html.twig', array(
            'valid' => false,
        ));
    }

    public function password(Request $request, UserPasswordEncoderInterface $encoder, Swift_Mailer $mailer, TokenGeneratorInterface $tokenGenerator): Response {
        if ($request->isMethod('POST')) {

            $email = $request->request->get('email');
            $entityManager = $this->getDoctrine()->getManager();

            /** @var UserRepository $userRepository */
            $userRepository = $entityManager->getRepository(User::class);

            /* @var User $user */
            $user = $userRepository->findOneBy(['email' => $email]);

            if ($user === null) {
                $this->addFlash('danger', 'Email Inconnu');
                return $this->render('security/forgotPasswordForm.html.twig', [
                    'alert' => true,
                    'type' => 'danger',
                    'title' => 'Erreur',
                    'msg' => 'Cet e-mail n\'existe pas.',
                ]);
            }

            $token = $tokenGenerator->generateToken();

            try {

                $user->setResetToken($token);
                $entityManager->flush();

            } catch (\Exception $e) {

                $this->addFlash('warning', $e->getMessage()); // Demander prof

                return $this->render('security/forgotPasswordForm.html.twig', [
                    'alert' => true,
                    'type' => 'danger',
                    'title' => 'Erreur',
                    'msg' => 'Une erreur est survenue...',
                ]);
            }

            $url = $this->generateUrl('reset', [
                'token' => $token
            ],UrlGeneratorInterface::ABSOLUTE_URL);

            /** @var Swift_Message $message */
            $message = new Swift_Message('Mot de passe oublié');

            $message->setFrom('contact@helomaker.com')
                ->setTo($user->getEmail())
                ->setBody(
                    $this->renderView('email/forgotPasswordForm.html.twig', [
                            'name' => $user->getName(),
                            'url' => $url,
                        ]
                    ), 'text/html'
                );
            $mailer->send($message);

            $this->addFlash('notice', 'Mail envoyé'); // ???
            //return $this->redirectToRoute('blog');
            return $this->redirectToRoute('password');
        }

        return $this->render('security/forgotPasswordForm.html.twig');
    }

    public function logout()
    {
        throw new \Exception("Logout");
    }

    public function view(Request $request, User $user) : Response
    {
        $doctrine = $this->getDoctrine();

        /** @var UserRepository $userRepository */
        $userRepository = $doctrine->getRepository(User::class);

        /** @var User $user */
        $user = $userRepository->find($user->getId());

        return $this->render('user/view.html.twig', [
            'user' => $user,
        ]);
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
