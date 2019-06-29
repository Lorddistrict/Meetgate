<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Rate;
use App\Entity\Talk;
use App\Repository\EventRepository;
use App\Repository\RateRepository;
use App\Repository\TalkRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractController
{
    /**
     * @param Request $request
     * @return Response
     */
    public function events(Request $request) : Response
    {
        $doctrine = $this->getDoctrine();

        /** @var EventRepository $eventRepository */
        $eventRepository = $doctrine->getRepository(Event::class);

        /** @var Event[] $events */
        $events = $eventRepository->findAll();

        return $this->render('event/view.html.twig', [
            'event' => $events,
        ]);
    }

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
        /** @var TalkRepository $talkRepository */
        $talkRepository = $doctrine->getRepository(Talk::class);
        /** @var RateRepository $rateRepository */
        $rateRepository = $doctrine->getRepository(Rate::class);

        /** @var Event $event */
        $event = $eventRepository->findOneBy(['id' => $event->getId()]);

        /** @var Talk $talks */
//        $talks = $talkRepository->findBy(
//            ['event' => $event->getId()],
//            ['id' => 'ASC']
//        );
        $talks = $talkRepository->getTalksByRate($event->getId());
//        dd($talks);

        $reserved = $event->getUser()->count();

        return $this->render('event/view.html.twig', [
            'event' => $event,
            'reserved' => $reserved,
            'talks' => $talks,
        ]);
    }


    public function join(Request $request, Event $event) : Response
    {
        $doctrine = $this->getDoctrine();

        /** @var EventRepository $eventRepository */
        $eventRepository = $doctrine->getRepository(Event::class);

        /** @var Event $event */
        $event = $eventRepository->find($event->getId());
    }
}
