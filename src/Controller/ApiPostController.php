<?php

namespace App\Controller;

use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ApiPostController extends AbstractController
{
    /**
     * @Route("/api/post", name="api_post", methods={"GET"})
     */
    public function index(PostRepository $postRepository, SerializerInterface $serializerInterface): Response
    {
        $posts = $postRepository->findAll();

        $response = $this->json($posts, 200, [], ['groups' => 'post:read']);

        return $response;

    }
}
