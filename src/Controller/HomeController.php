<?php

namespace App\Controller;

use App\Entity\Event;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    public function index(Request $request) : Response
    {
        $doctrine = $this->getDoctrine();

        /** @var EventRepository $eventRepository */
        $eventRepository = $doctrine->getRepository(Event::class);

        /** @var Event $events */
        $events = $eventRepository->getNextEvents();

        return $this->render('home/home.html.twig', [
            'events' => $events,
            'controller_name' => 'HomeController',
        ]);
    }
}
