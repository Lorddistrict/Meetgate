<?php

namespace App\Controller;

use App\Entity\Rate;
use App\Entity\Talk;
use App\Repository\RateRepository;
use App\Repository\TalkRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TalkController extends AbstractController
{
    public function view(Request $request, Talk $talk) : Response
    {

        dd($user = $this->getUser());

        $doctrine = $this->getDoctrine();

        /** @var TalkRepository $talkRepository */
        $talkRepository = $doctrine->getRepository(Talk::class);

        /** @var RateRepository $rateRepository */
        $rateRepository = $doctrine->getRepository(Rate::class);

        /** @var Rate $rates */
        $rates = $rateRepository->findBy(['talk' => $talk->getId()]);
        $nbRates = count($rates);
        $stars = 0;

        /**
         * @var int $key
         * @var Rate $rate
         */
        foreach ($rates as $key => $rate){
            $stars = $stars += $rate->getStars();
            if($rate->getUser()->getId() == 3){
                die;
            }
        }
        $rate = $stars/$nbRates;

        /** @var Talk $talk */
        $talk = $talkRepository->find($talk->getId());

        return $this->render('talk/view.html.twig', [
            'talk' => $talk,
            'rate' => $rate,
        ]);
    }
}
