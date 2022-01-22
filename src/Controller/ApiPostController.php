<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiPostController extends AbstractController
{
    /**
     * @Route("/api/post", name="api_post")
     */
    public function index(): Response
    {
        return $this->render('api_post/index.html.twig', [
            'controller_name' => 'ApiPostController',
        ]);
    }
}
