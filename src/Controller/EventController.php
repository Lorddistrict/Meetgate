<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Participation;
use App\Entity\Rate;
use App\Entity\Talk;
use App\Entity\User;
use App\Repository\EventRepository;
use App\Repository\ParticipationRepository;
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

        /** @var ParticipationRepository $participationRepository */
        $participationRepository = $doctrine->getRepository(Participation::class);

        /** @var Event $event */
        $event = $eventRepository->findOneBy([
            'id' => $event->getId()
        ]);

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        /** @var Talk $talks */
        $talks = $talkRepository->getTalksByRate($event->getId());

        /** @var Participation $participations */
        $participations = $participationRepository->findBy([
            'event' => $event->getId(),
        ]);
        $reserved = count($participations);

        if( !empty($currentUser) ){

            $currentUserParticipation = $participationRepository->findBy([
                'user' => $currentUser->getId(),
                'event' => $event->getId(),
            ]);

            if( empty($currentUserParticipation) ){

                return $this->render('event/view.html.twig', [
                    'event' => $event,
                    'reserved' => $reserved,
                    'talks' => $talks,
                    'canParticipate' => true,
                ]);

            }
        }

        return $this->render('event/view.html.twig', [
            'event' => $event,
            'reserved' => $reserved,
            'talks' => $talks,
            'canParticipate' => false,
        ]);
    }

    public function join(Request $request, Event $event) : Response
    {
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();

        /** @var EventRepository $eventRepository */
        $eventRepository = $doctrine->getRepository(Event::class);

        /** @var ParticipationRepository $participationRepository */
        $participationRepository = $doctrine->getRepository(Participation::class);

        /** @var Event $event */
        $event = $eventRepository->find($event->getId());

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        /** @var Participation $participation */
        // Try to find if the one logged has already a participation
        $participation = $participationRepository->findBy([
            'event' => $event->getId(),
            'user' => $currentUser->getId()
        ]);

        if( empty($participation) ) {

            /** @var Participation $participation */
            $participation = new Participation();

            $participation->setEvent($event);
            $participation->setUser($currentUser);
            $em->persist($participation);
            $em->flush();
        }
        return $this->redirectToRoute('event', [
            'id' => $event->getId(),
        ]);
    }
}
