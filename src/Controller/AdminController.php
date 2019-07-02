<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Rate;
use App\Entity\Talk;
use App\Entity\User;
use App\Form\AddEventType;
use App\Form\AddTalkType;
use App\Repository\EventRepository;
use App\Repository\RateRepository;
use App\Repository\TalkRepository;
use App\Repository\UserRepository;
use DateTime;
use Swift_Mailer;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AdminController extends AbstractController
{
    public function index() : Response
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        if(!in_array('ROLE_ADMIN', $currentUser->getRoles())){
            return $this->redirectToRoute('index');
        }

        $doctrine = $this->getDoctrine();

        /** @var EventRepository $eventRepository */
        $eventRepository = $doctrine->getRepository(Event::class);

        /** @var TalkRepository $talkRepository */
        $talkRepository = $doctrine->getRepository(Talk::class);

        /** @var RateRepository $rateRepository */
        $rateRepository = $doctrine->getRepository(Rate::class);

        /** @var Event $lastEvents */
        $lastEvents = $eventRepository->getLastFiveEvents();

        /** @var Talk $lastTalks */
        $lastTalks = $talkRepository->getLastFiveTalks();

        return $this->render('admin/index.html.twig', [
            'events' => $lastEvents,
            'talks' => $lastTalks,
        ]);
    }

    public function addEvent(Request $request, Swift_Mailer $mailer) : Response
    {
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();

        /** @var UserRepository $userRepository */
        $userRepository = $doctrine->getRepository(User::class);

        /** @var Event $event */
        $event = new Event();

        $form = $this->createForm(AddEventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var Event $event */
            $event = $form->getData();

            /** @var DateTime $now */
            $now = new DateTime();
            $event->setCreated($now);
            $event->setPicture('https://lorempixel.com/640/480/');
            $em->persist($event);
            $em->flush();

            // send a mail for all users with allow = 1
            $users = $userRepository->findBy([
                'allowMails' => true,
            ]);

            $url = $this->generateUrl('event', [
                'id' => $event->getId(),
            ],UrlGeneratorInterface::ABSOLUTE_URL);

            foreach ($users as $key => $user){

                /** @var Swift_Message $message */
                $message = new Swift_Message('An event has been created !');
                $message->setFrom('contact@meetgate.com');
                $message->setTo($user->getEmail());
                $message->setBody(
                    $this->render('email/eventCreated.html.twig', [
                        'firstname' => $user->getFirstname(),
                        'event' => $event,
                        'url' => $url,
                    ]),
                    'text/html'
                );
                $mailer->send($message);

            }

            $this->addFlash('success', 'An Event has been created !');
            return $this->redirectToRoute('admin');
        }

        return $this->render('admin/management/events/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function addTalk(Request $request) : Response
    {
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();

        /** @var Talk $talk */
        $talk = new Talk();

        $form = $this->createForm(AddTalkType::class, $talk);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var Talk $talk */
            $talk = $form->getData();

            /** @var DateTime $now */
            $now = new DateTime();
            $talk->setCreated($now);
            $em->persist($talk);
            $em->flush();

            $this->addFlash('success', 'A Talk has been created !');
            return $this->redirectToRoute('admin');
        }

        return $this->render('admin/management/talks/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function manageEvents() : Response
    {
        return $this->render('admin/management/events/events.html.twig');
    }

    public function manageTalks() : Response
    {
        return $this->render('admin/management/talks/talks.html.twig');
    }

    public function manageUsers() : Response
    {
        return $this->render('admin/management/users/users.html.twig');
    }

    public function topEvents() : Response
    {
        $doctrine = $this->getDoctrine();

        /** @var EventRepository $eventRepository */
        $eventRepository = $doctrine->getRepository(Event::class);

        $events = $eventRepository->getTop10Events();

        return $this->render('admin/top/events.html.twig', [
            'events' => $events,
        ]);
    }

    public function topTalks() : Response
    {
        $doctrine = $this->getDoctrine();

        /** @var TalkRepository $talkRepository */
        $talkRepository = $doctrine->getRepository(Talk::class);

        $talks = $talkRepository->getTop10Talks();

        return $this->render('admin/top/talks.html.twig', [
            'talks' => $talks,
        ]);
    }
}
