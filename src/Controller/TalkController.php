<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Rate;
use App\Entity\Talk;
use App\Entity\User;
use App\Form\AddTalkType;
use App\Repository\EventRepository;
use App\Repository\RateRepository;
use App\Repository\TalkRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TalkController extends AbstractController
{
    public function view(Request $request, Talk $talk): Response
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

    public function add(Request $request, Event $event): Response
    {
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();

        /** @var Talk $talk */
        $talk = new Talk();

        $form = $this->createForm(AddTalkType::class, $talk);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var Talk $talk */
            $talk = $form->getData();

            /** @var DateTime $now */
            $now = new DateTime();
            $talk->setCreated($now);
            $em->persist($talk);
            $em->flush();

            $this->addFlash('success', 'A Talk has been created !');
            return $this->redirectToRoute('admin');
        }

        return $this->render('admin/management/talks/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
