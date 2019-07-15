<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use App\Entity\User;
use App\Form\AddEventType;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use DateTime;
use Swift_Mailer;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EventController extends AbstractController
{
    /**
     * @param Request $request
     * @param Swift_Mailer $mailer
     * @return Response
     * @throws \Exception
     */
    public function add(Request $request, Swift_Mailer $mailer): Response
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
                    $this->render('email/event/eventCreated.html.twig', [
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

    /**
     * @return Response
     */
    public function manage(): Response
    {
        return $this->render('admin/management/events/events.html.twig');
    }

    /**
     * @return Response
     */
    public function top(): Response
    {
        $doctrine = $this->getDoctrine();

        /** @var EventRepository $eventRepository */
        $eventRepository = $doctrine->getRepository(Event::class);

        $events = $eventRepository->getTop10Events();

        return $this->render('admin/top/events.html.twig', [
            'events' => $events,
        ]);
    }
}