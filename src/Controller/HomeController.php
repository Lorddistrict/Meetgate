<?php

namespace App\Controller;

use App\Entity\Event;
use App\Repository\EventRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    public function index(Request $request, PaginatorInterface $paginator) : Response
    {
        $doctrine = $this->getDoctrine();

        /** @var EventRepository $eventRepository */
        $eventRepository = $doctrine->getRepository(Event::class);

        $events = $paginator->paginate(
            $eventRepository->findHomeEventsQuery(),
            $request->query->getInt('page', 1),
            6
        );

        return $this->render('home/home.html.twig', [
            'events' => $events,
            'controller_name' => 'HomeController',
        ]);
    }
}
