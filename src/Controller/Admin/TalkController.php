<?php

namespace App\Controller\Admin;

use App\Entity\Talk;
use App\Entity\User;
use App\Form\AddTalkType;
use App\Repository\TalkRepository;
use DateTime;
use Swift_Mailer;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TalkController extends AbstractController
{
    /**
     * @return Response
     */
    public function manage(): Response
    {
        return $this->render('admin/management/talks/talks.html.twig');
    }

    /**
     * @return Response
     */
    public function top(): Response
    {
        $doctrine = $this->getDoctrine();

        /** @var TalkRepository $talkRepository */
        $talkRepository = $doctrine->getRepository(Talk::class);

        $talks = $talkRepository->getTop10Talks();

        return $this->render('admin/top/talks.html.twig', [
            'talks' => $talks,
        ]);
    }

    /**
     * @param Request $request
     * @param Talk $talk
     * @param Swift_Mailer $mailer
     * @return Response
     */
    public function validate(Request $request, Talk $talk, Swift_Mailer $mailer): Response
    {
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();

        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $isAdmin = $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN');

        /** @var User $talk_author */
        $talk_author = $talk->getAuthor();

        if( $isAdmin ){
            $talk->setValidatedByAdmin(true);
            $em->flush();

            $url = $this->generateUrl('talk', [
                'id' => $talk->getId(),
            ],UrlGeneratorInterface::ABSOLUTE_URL);

            /** @var Swift_Message $message */
            $message = (new Swift_Message('Talk accepted by an Administrator'));
            $message->setFrom('contact@meetgate.com');
            $message->setTo($talk_author->getEmail());
            $message->setBody(
                $this->render('email/talk/accepted.html.twig', [
                    'firstname' => $talk_author->getFirstname(),
                    'title' => $talk->getTitle(),
                    'url' => $url,
                ]),
                'text/html'
            );
            $mailer->send($message);

            $this->addFlash('success', 'You validated this talk, the author will be happy :D');
            return $this->redirectToRoute('admin');
        }

        $this->addFlash('warning', 'You\'re not allowed to see this page.');
        return $this->redirectToRoute('index');
    }

    /**
     * @param Request $request
     * @param Talk $talk
     * @param Swift_Mailer $mailer
     * @return Response
     */
    public function refuse(Request $request, Talk $talk, Swift_Mailer $mailer): Response
    {
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();

        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $isAdmin = $this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN');

        /** @var User $talk_author */
        $talk_author = $talk->getAuthor();

        if( $isAdmin ){
            $talk->setValidatedByAdmin(false);
            $em->flush();

            /** @var Swift_Message $message */
            $message = (new Swift_Message('Talk refused by an Administrator'));
            $message->setFrom('contact@meetgate.com');
            $message->setTo($talk_author->getEmail());
            $message->setBody(
                $this->render('email/talk/refused.html.twig', [
                    'firstname' => $talk_author->getFirstname(),
                    'reason' => 'A good reason',
                ]),
                'text/html'
            );
            $mailer->send($message);

            $this->addFlash('info', 'You refused this talk, the author will be sad but who care ?');
            return $this->redirectToRoute('admin');
        }

        $this->addFlash('warning', 'You\'re not allowed to see this page.');
        return $this->redirectToRoute('index');
    }
}