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
        dd($event);
        $em = $this->getDoctrine()->getManager();

        /** @var EventRepository $eventRepository */
        $eventRepository = $em->getRepository(EventRepository::class);

        /** @var Event $event */
        $event = $eventRepository->findOneBy(['id' => $event->getId()]);
        dd($event);

        return $this->render('event/view.html.twig', [
            'event' => $event,
        ]);
    }
}
