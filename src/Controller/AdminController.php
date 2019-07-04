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
    /**
     * @return Response
     */
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
        $lastTalks = $talkRepository->getLastFiveSubmitedTalks();

        return $this->render('admin/index.html.twig', [
            'events' => $lastEvents,
            'talks' => $lastTalks,
        ]);
    }

}
