<?php

namespace App\Controller;

use App\Entity\Talk;
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

        /** @var Talk $talk */
        $talk = $talkRepository->find($talk->getId());

        return $this->render('talk/view.html.twig', [
            'talk' => $talk,
        ]);
    }
}
