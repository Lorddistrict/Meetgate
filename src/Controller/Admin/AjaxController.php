<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use App\Repository\EventRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AjaxController extends AbstractController
{
    public function sort(Request $request): JsonResponse
    {
        $sortId = $request->get('sort');

        $doctrine = $this->getDoctrine();

        /** @var EventRepository $eventRepository */
        $eventRepository = $doctrine->getRepository(Event::class);

        $now = new DateTime();

        $nbDaysthisMonth = date_format($now, 't');

        $day = date_format($now, 'd');
        $month = date_format($now, 'm');
        $year = date_format($now, 'Y');

        $mondayThisWeek = date( 'Y-m-d H:i:s', strtotime( 'monday this week' ) );
        $mondayThisWeekDay = date( 'd', strtotime( 'monday this week' ) );
        $sundayThisWeek = date( 'Y-m-d H:i:s', strtotime( 'sunday this week' ) );
        $sundayThisWeekDay = date( 'd', strtotime( 'sunday this week' ) );

        $firstDayThisMonth = $year.'-'.$month.'-01 00:00:00';
        $lastDayThisMonth = $year.'-'.$month.'-30 23:59:59';
        if($nbDaysthisMonth == 31){
            $lastDayThisMonth = $year.'-'.$month.'-31 23:59:59';
        }

        /*
         * 0: default option - disabled
         * 1: week
         * 2: month
         * 3: year
         */
        switch ($sortId){
            case 0:
                $display = [];
                $events = [];
                break;
            case 1:
                $display = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
//                $display = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                $events = $eventRepository->getEventsThisWeek($mondayThisWeek, $sundayThisWeek);

                for($i = $mondayThisWeekDay; $i <= $sundayThisWeekDay; $i++){
                    $data[$i] = 0;
                    foreach($events as $key => $event){
                        if($event['day'] == $i){
                            $data[$i] = (int)$event['num'];
                        }
                    }
                }

                break;
            case 2:
//                $display = ['week 1', 'week 2', 'week 3', 'week 4'];
//                $events = $eventRepository->getEventsThisMonth($firstDayThisMonth, $lastDayThisMonth);
//
//                dd($events);
//
//                for($i = 1; $i <= $nbDaysthisMonth; $i++){
//                    $data[$i] = 0;
//                    foreach($events as $key => $event){
//                        if($event['day'] == $i){
//                            $data[$i] = (int)$event['num'];
//                        }
//                    }
//                }
//
//                dd($events);
//
                $display = [];
                $events = [];
                break;
            case 3:
//                $display = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
//                $display = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'June', 'July', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec'];
//                $events = $eventRepository->getEventsThisYear();
                $display = [];
                $events = [];
                break;
        }

        foreach ($data as $key => $value){
            $newData[] = $value;
        }

        return new JsonResponse([
            'display' => $display,
            'data' => $newData,
        ]);
    }
}
