<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\PasswordRecoveryType;
use App\Form\PasswordResetType;
use App\Repository\UserRepository;
use Swift_Mailer;
use Swift_Message;
use App\Form\UserLoginType;
use App\Form\UserRegisterType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserController extends AbstractController
{
    /**
     * @param Request $request
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function login(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        /** @var User $user */
        $user = new User();

        $userForm = $this->createForm(UserLoginType::class, $user);
        $userForm->handleRequest($request);

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'userForm' => $userForm->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param Swift_Mailer $mailer
     * @param TokenGeneratorInterface $tokenGenerator
     * @return Response
     */
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


            return $this->render('security/preconfirm.html.twig', [
                'email' => $user->getEmail(),
            ]);
        }


        return $this->render('security/register.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     */
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

            $this->addFlash('success', 'Your account has been validated !');
            return $this->render('security/confirm.html.twig', array(
                'valid' => true,
            ));
        }

        $this->addFlash('danger', 'We couldn\'t validate this account with this token. Please try again, if it happend again, contact the support.');
        return $this->render('security/confirm.html.twig', array(
            'valid' => false,
        ));
    }

    /**
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @param Swift_Mailer $mailer
     * @param TokenGeneratorInterface $tokenGenerator
     * @return Response
     */
    public function password(Request $request, UserPasswordEncoderInterface $encoder, Swift_Mailer $mailer, TokenGeneratorInterface $tokenGenerator): Response
    {
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();

        /** @var User $user */
        $user = new User();

        $form = $this->createForm(PasswordRecoveryType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var User $userObj */
            $userObj = $form->getData();

            /** @var UserRepository $userRepository */
            $userRepository = $doctrine->getRepository(User::class);

            /* @var User $user */
            $user = $userRepository->findOneBy([
                'email' => $userObj->getEmail(),
            ]);

            if ( empty($user) ) {
                $this->addFlash('warning', 'Unknown email');
                return $this->redirectToRoute('password');
            }

            $token = $tokenGenerator->generateToken();
            $user->setResetToken($token);

            // No need to persist() because the object is already on the DB
            $em->flush();

            $url = $this->generateUrl('reset', [
                'token' => $token
            ],UrlGeneratorInterface::ABSOLUTE_URL);

            /** @var Swift_Message $message */
            $message = new Swift_Message('Password recovery');
            $message
                ->setFrom('contact@meetgate.com')
                ->setTo($user->getEmail())
                ->setBody(
                    $this->renderView('email/passwordRecovery.html.twig', [
                            'firstname' => $user->getFirstname(),
                            'url' => $url,
                        ]
                    ), 'text/html'
                );
            $mailer->send($message);

            $this->addFlash('success', ' An email has been sent');
            return $this->redirectToRoute('password');
        }

        return $this->render('security/passwordRecovery.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return Response
     */
    public function resetPassword(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();

        /** @var User $user */
        $user = new User();

        $form = $this->createForm(PasswordResetType::class, $user);
        $form->handleRequest($request);

        $token = $request->get('token');

        if( !empty($token) ){

            /** @var UserRepository $userRepository */
            $userRepository = $doctrine->getRepository(User::class);

            /** @var User $user */
            $user = $userRepository->findOneBy([
                'resetToken' => $token,
            ]);

            if( !empty($user) ){

                if ($form->isSubmitted() && $form->isValid()) {

                    $newPassword = $form->getData()->getPassword();

                    if(strlen($newPassword) < 6){
                        $this->addFlash('warning', 'Password to short (6 characters min) !');
                        return $this->render('security/passwordReset.html.twig', [
                            'form' => $form->createView(),
                        ]);
                    }

                    $passwordEncoded = $passwordEncoder->encodePassword($user, $newPassword);
                    $user->setResetToken(null);
                    $user->setPassword($passwordEncoded);

                    // No need to persist() because the object is already on the DB
                    $em->flush();

                    $this->addFlash('success', 'Password updated !');
                    return $this->redirectToRoute('login');
                }

            } else {
                $this->addFlash('warning', 'The token has expired or has never been created. Please retry or contact our support.');
                return $this->redirectToRoute('login');
            }

        }else{

            $this->addFlash('warning', 'Sorry, something\'s wrong, please retry or contact our support.');
            return $this->render('security/passwordReset.html.twig', [
                'form' => $form->createView(),
            ]);

        }

        return $this->render('security/passwordReset.html.twig', [
            'form' => $form->createView(),
        ]);

    }

    /**
     * @throws \Exception
     */
    public function logout()
    {
        throw new \Exception("Logout");
    }

    /**
     * @param Request $request
     * @param User $user
     * @return Response
     */
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
}
