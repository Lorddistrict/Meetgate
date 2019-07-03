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

class TalkController extends AbstractController
{
    public function manage(): Response
    {
        return $this->render('admin/management/talks/talks.html.twig');
    }

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

    public function validate(Request $request, Talk $talk, Swift_Mailer $mailer): Response
    {
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        /** @var User $talk_author */
        $talk_author = $talk->getAuthor();

        if( in_array('[ROLE_ADMIN]', $currentUser->getRoles()) ){
            $talk->setValidatedByAdmin(true);
            $em->flush();

            /** @var Swift_Message $message */
            $message = (new Swift_Message('Talk accepted by an Administrator'));
            $message->setFrom('contact@meetgate.com');
            $message->setTo($talk_author->getEmail());
            $message->setBody(
                $this->render('email/talk_accepted.html.twig', [
                    'firstname' => $talk_author->getFirstname(),
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

    public function refuse(Request $request, Talk $talk): Response
    {
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        if( in_array('[ROLE_ADMIN]', $currentUser->getRoles()) ){
            $talk->setValidatedByAdmin(false);
            $em->flush();

            $this->addFlash('info', 'You refused this talk, the author will be sad but who care ?');
            return $this->redirectToRoute('admin');
        }

        $this->addFlash('warning', 'You\'re not allowed to see this page.');
        return $this->redirectToRoute('index');
    }
}