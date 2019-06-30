<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Rate;
use App\Entity\Talk;
use App\Entity\User;
use App\Repository\EventRepository;
use App\Repository\RateRepository;
use App\Repository\TalkRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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

    public function rateATalk(Request $request) : JsonResponse
    {
        /** @var Talk $talk */
        $talkId = htmlspecialchars((int)($request->get('talk')));
        $userRate = htmlspecialchars((int)($request->get('rate')));

        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();

        /** @var RateRepository $rateRepository */
        $rateRepository = $doctrine->getRepository(Rate::class);

        /** @var TalkRepository $talkRepository */
        $talkRepository = $doctrine->getRepository(Talk::class);

        $talk = $talkRepository->find($talkId);

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        /** @var Rate $rate */
        $existRate = $rateRepository->findBy([
            'user' => $currentUser->getId(),
            'talk' => $talk->getId(),
        ]);

        if( empty($existRate) ){

            $rate = new Rate();

            $rate->setUser($currentUser);
            $rate->setTalk($talk);
            $rate->setStars($userRate);

            $em->persist($rate);
            $em->flush();

            return new JsonResponse(
                array(
                    true,
                )
            );
        }

        return new JsonResponse(
            array(
                false,
            )
        );
    }
}
