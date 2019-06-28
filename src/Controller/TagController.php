<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Repository\TagRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TagController extends AbstractController
{
    /**
     * @param Request $request
     * @return Response
     */
    public function tags(Request $request) : Response
    {
        $doctrine = $this->getDoctrine();

        /** @var TagRepository $tagRepository */
        $tagRepository = $doctrine->getRepository(Tag::class);

        /** @var Tag[] $tags */
        $tags = $tagRepository->findAll();

        return $this->render('tag/tags.html.twig', [
            'tags' => $tags,
        ]);
    }

    /**
     * @param Request $request
     * @param Tag $tag
     * @return Response
     */
    public function view(Request $request, Tag $tag) : Response
    {
        $doctrine = $this->getDoctrine();

        /** @var TagRepository $tagRepository */
        $tagRepository = $doctrine->getRepository(Tag::class);

        /** @var Tag $tags */
        $tag = $tagRepository->find($tag->getId());

        return $this->render('tag/view.html.twig', [
            'tag' => $tag,
        ]);
    }
}
