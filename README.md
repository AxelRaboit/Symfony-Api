### Projet de learning pour créer une api sur symfony

Video de reference: https://www.youtube.com/watch?v=SG7GgcnR1F4

## Creation et installation de la base de donnée mysql (symfony console d:d:c)
- Creation d'une base de donnée "symfonyapi"

## Creation des entités
- Post
- Comment

## Creation des fixtures pour remplir de donnée les deux entités
- Creation de fixtures dans le fichier AppFixtures

# PREMIERE PARTIE POUR RECUPERER LES DATAS DE LA BASE DE DONNÉE

## Creation du controller pour créer l'api
- Creation du fichier ApiPostController
- Suppression du template lié car il est inutile dans le cadre d'une api

## Serialization des données (Cela va éviter les references circulaires)
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

## Serialization de la seconde entité lié à la premiere
- Pour cela il faut ajouter "* @Groups("post:read")" à la propriété "comments" presente dans l'entité "Post"
- Puis il faut alors aller dans l'entité "Comment" et ajouter aussi à toutes les propriété que l'on veut ciblé cela "* @Groups("post:read")". Surtout ne pas ajouter la la relation menant de Comment à Post, car cela a deja été dans le sens inverse.

## Methodes pour recuperer la data au format json

1ere methode
```
```

2eme methode
```
```

3eme methode
```
```

4eme methode
```
```

# SECONDE PARTIE POUR POSTER DE LA DATAS VERS LA BASE DE DONNÉE

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

