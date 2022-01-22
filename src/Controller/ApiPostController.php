<?php

namespace App\Controller;

use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ApiPostController extends AbstractController
{
    /**
     * @Route("/api/post", name="api_post", methods={"GET"})
     */
    public function index(PostRepository $postRepository, NormalizerInterface $normalizer): Response
    {
        $posts = $postRepository->findAll();

        $postsNormalized = $normalizer->normalize($posts, null, ['groups' => 'post:read']);
        
        $json = json_encode($postsNormalized);

        $response = new Response($json, 200, [
            "Content-Type" => "application/json"
        ]);

        return $response;

    }
}
