<?php

namespace App\Controller;

use App\Entity\Event;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractController
{
    /**
     * @param Request $request
     * @param Event $event
     * @return Response
     */
    public function view(Request $request, Event $event) : Response
    {
        $doctrine = $this->getDoctrine();

        /** @var EventRepository $eventRepository */
        $eventRepository = $doctrine->getRepository(Event::class);

        /** @var Event $event */
        $oneEvent = $eventRepository->findOneBy(['id' => $event->getId()]);

        return $this->render('event/view.html.twig', [
            'event' => $oneEvent,
        ]);
    }
}
