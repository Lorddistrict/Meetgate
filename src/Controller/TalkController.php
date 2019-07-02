<?php

namespace App\Controller;

use App\Entity\Rate;
use App\Entity\Talk;
use App\Entity\User;
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
        $doctrine = $this->getDoctrine();

        /** @var TalkRepository $talkRepository */
        $talkRepository = $doctrine->getRepository(Talk::class);

        /** @var RateRepository $rateRepository */
        $rateRepository = $doctrine->getRepository(Rate::class);

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        /** @var Talk $talk */
        $talk = $talkRepository->find($talk->getId());

        /** @var Rate $rates */
        $rates = $rateRepository->findBy([
            'talk' => $talk->getId()
        ]);

        $nbRates = count($rates);
        $rate = 0;

        if($nbRates != 0) {

            $stars = 0;

            /**
             * @var int $key
             * @var Rate $rate
             */
            foreach ($rates as $key => $rate){
                $stars = $stars += $rate->getStars();
            }
            $rate = $stars/$nbRates;
        }

        if( empty($currentUser) ){

            return $this->render('talk/view.html.twig', [
                'talk' => $talk,
                'rate' => $rate,
                'allowRate' => false,
            ]);

        }

        /** @var Rate[] $currentUserRate */
        $currentUserRate = $rateRepository->findBy([
            'user' => $currentUser->getId(),
            'talk' => $talk->getId()
        ]);

        if( !empty($currentUserRate) ){

            $starsGivenCurrentTalk = $currentUserRate[0]->getStars();

            return $this->render('talk/view.html.twig', [
                'talk' => $talk,
                'rate' => $rate,
                'allowRate' => false,
                'starsGivenCurrentTalk' => $starsGivenCurrentTalk,
            ]);
        }

        return $this->render('talk/view.html.twig', [
            'talk' => $talk,
            'rate' => $rate,
            'allowRate' => true,
        ]);


    }
}
