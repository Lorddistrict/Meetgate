<?php

namespace App\Controller;

use App\Entity\Event;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AjaxController extends AbstractController
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function searchBarAjax(Request $request) : JsonResponse
    {
        $research = $request->query->get('research');
        $doctrine = $this->getDoctrine();

        /** @var EventRepository $eventRepository */
        $eventRepository = $doctrine->getRepository(Event::class);

        /** @var Event $events */
        $events = $eventRepository->findAll();

        $titles = [];

        /**
         * @var int $key
         * @var Event $event
         */
        foreach ($events as $key => $event) {
            array_push($titles, $event->getTitle());
        }

        return new JsonResponse(
            array(
                $titles,
            )
        );
    }

    public function searchByEventName(Request $request) : JsonResponse
    {
        $research = $request->query->get('research');
        $doctrine = $this->getDoctrine();

        /** @var EventRepository $eventRepository */
        $eventRepository = $doctrine->getRepository(Event::class);

        /** @var Event[] $event */
        $event = $eventRepository->findBy([
            'title' => $research,
        ]);

        return new JsonResponse(
            array(
                $event[0]->getId(),
            )
        );
    }
}
