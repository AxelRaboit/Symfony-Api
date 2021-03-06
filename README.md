# Projet de learning pour créer une api sur symfony

Video de reference: 

1ere partie
https://www.youtube.com/watch?v=SG7GgcnR1F4

2eme partie
https://www.youtube.com/watch?v=kO1gVLKCTvM

### Creation et installation de la base de donnée mysql (symfony console d:d:c)
- Creation d'une base de donnée "symfonyapi"

### Creation des entités
- Post
- Comment

### Creation des fixtures pour remplir de donnée les deux entités
- Creation de fixtures dans le fichier AppFixtures

## PREMIERE PARTIE POUR RECUPERER LES DATAS DE LA BASE DE DONNÉE

### Creation du controller pour créer l'api
- Creation du fichier ApiPostController
- Suppression du template lié car il est inutile dans le cadre d'une api

### Serialization des données (Cela va éviter les references circulaires)
Dans le fichier de l'entité "Post"
- Importer le namespace et ajouter la ligne
- Puis ajouter "@Groups("post:read")" le faire sur chaque propriété que l'on souhaite en extraire les données
- Dans le cas du projet toutes les propriétés seront tagués sauf celle de la relation entre les deux entités

```
* @Groups("post:read")
//Le choix du nom est au choix
```

```
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Id
 * @ORM\GeneratedValue
 * @ORM\Column(type="integer")
 * @Groups("post:read")
 */
private $id;
```

### Serialization de la seconde entité lié à la premiere
- Pour cela il faut ajouter "* @Groups("post:read")" à la propriété "comments" presente dans l'entité "Post"
- Puis il faut alors aller dans l'entité "Comment" et ajouter aussi à toutes les propriété que l'on veut ciblé cela "* @Groups("post:read")". Surtout ne pas ajouter la la relation menant de Comment à Post, car cela a deja été dans le sens inverse.

### Methodes pour recuperer la data au format json

1ere methode
```
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @Route("/api/post", name="api_get_data", methods={"GET"})
 */
public function getData(PostRepository $postRepository, NormalizerInterface $normalizer): Response
{
    $posts = $postRepository->findAll();
    $postsNormalized = $normalizer->normalize($posts, null, ['groups' => 'post:read']);
    $json = json_encode($postsNormalized);
    $response = new Response($json, 200, [
        "Content-Type" => "application/json"
    ]);

    return $response;
}
```

2eme methode
```
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/api/post", name="api_get_data", methods={"GET"})
 */
public function getData(PostRepository $postRepository, SerializerInterface $serializer): Response
{
    $posts = $postRepository->findAll();
    $json = $serializer->serialize($posts, 'json', ['groups' => 'post:read']);
    $response = new Response($json, 200, [
        "Content-Type" => "application/json"
    ]);

    return $response;
}
```

3eme methode
```
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/api/post", name="api_get_data", methods={"GET"})
 */
public function getData(PostRepository $postRepository, SerializerInterface $serializer): Response
{
    $posts = $postRepository->findAll();
    $json = $serializer->serialize($posts, 'json', ['groups' => 'post:read']);
    $response = new JsonResponse($json, 200, [], true);

    return $response;
}
```

## SECONDE PARTIE POUR POSTER DE LA DATAS VERS LA BASE DE DONNÉE

Dans le fichier apiPostController, ajouter cette methode

```
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
```

Ajouter dans l'entité "Post", les constraints de validation

```
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Assert\Length(min=3)
 * ...
 * ...
 * ...
 */
private $title;
```

## TROISIEME PARTIE API PLATFORM (Librairie apiplatform)

- Installation de api platform (avec symfony flex)
```
composer require api

ou

composer require api-platform/core
```

Par la suite pour eviter les conflit, il faut aller dans le fichier api_platform.yaml et faire comme suit

```
api_platform:
    resource: .
    type: api_platform
    prefix: /apip
```

Ensuite api platform est disponible a l'url suivante
```
http://localhost:8000/apip
```

Pour greffer api platform à une entité, il faut aller dans le fichier de l'entité en question puis ajouter ces lignes
```
use ApiPlatform\Core\Annotation\ApiResource;

/**
 * @ORM\Entity(repositoryClass=PostRepository::class)
 * @ApiResource
 */
class Post
{
    //Code
}
```

Il existe plusieurs fonctionnalité de ApiPlatform, la formation de Lior Chamla sur React js et ApiPlatform parle de plusieurs fonctionnalitées

Dans le fichier src, Creation d'un dossier que l'on nomme DataPersister, puis un fichier dans ce dossier que l'on va nommer, PostPersister.php.

```
<?php

namespace App\DataPersister;

use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use ApiPlatform\Core\DataPersister\DataPersisterInterface;

class PostPersister implements DataPersisterInterface
{
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em;
    }

    public function supports($data): bool
    {
        return $data instanceof Post;
    }

    public function persist($data)
    {
        // 1. Mettre une date de creaion sur le post
        $data->setCreatedAt(new \DateTime());

        // 2. Demander a doctrine de persister
        $this->em->persist($data);
        $this->em->flush();
    }

    public function remove($data)
    {
        // 1. Demander a doctrine de supprimer le post
        $this->em->remove($data);
        $this->em->flush();
    }
}
```

Dans le code si dessus, nous avons fait en sorte de setter la date de creation qui de base n'est pas renseigné.

Donc grâce a api platform, le code que nous avons effectué dans le controller de ApiPostController ne sert plus à rien car api platform prend le relais, et passe dans PostPersister.php.
