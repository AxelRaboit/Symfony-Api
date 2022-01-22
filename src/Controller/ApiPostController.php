<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ApiPostController extends AbstractController
{
    /**
     * @Route("/api/post", name="api_get_data", methods={"GET"})
     */
    public function getData(PostRepository $postRepository): Response
    {
        return $this->json($postRepository->findAll(), 200, [], ['groups' => 'post:read']);
    }

    /**
     * @Route("/api/post", name="api_post_data", methods={"POST"})
     */
    public function postData(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    )
    {
        $jsonGot = $request->getContent();

        try {
            //Recuperation des datas posté
            $post = $serializer->deserialize($jsonGot, Post::class, 'json');
    
            //Setter la date de creation comme demandé dans les require de l'entité
            $post->setCreatedAt(new \DateTime());

            //Si il y a erreur, recuperation des erreurs
            $errors = $validator->validate($post);

            //Si il y a des erreurs superieur à 0, retourner les erreurs au format json
            if(count($errors) > 0) {
                return $this->json($errors, 400);
            }
    
            //Envoyer les datas à la base de donnée
            $em->persist($post);
            $em->flush();

            //Retourner les datas au format json
            return $this->json($post, 201, [], ['groups' => 'post:read']);
        
        } catch(NotEncodableValueException $e) {

            //Si les datas ne sont pas encodable renvoyer les erreurs en format json
            return $this->json([
                'status' => 400,
                'message' => $e->getMessage()
            ], 400);
        }

    }
}
