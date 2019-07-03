<?php

namespace App\Controller\Admin;

use App\Entity\Talk;
use App\Form\AddTalkType;
use App\Repository\TalkRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TalkController extends AbstractController
{
    public function add(Request $request): Response
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

    public function manage(): Response
    {
        return $this->render('admin/management/talks/talks.html.twig');
    }

    public function top(): Response
    {
        $doctrine = $this->getDoctrine();

        /** @var TalkRepository $talkRepository */
        $talkRepository = $doctrine->getRepository(Talk::class);

        $talks = $talkRepository->getTop10Talks();

        return $this->render('admin/top/talks.html.twig', [
            'talks' => $talks,
        ]);
    }
}