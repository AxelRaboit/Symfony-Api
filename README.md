### Projet de learning pour créer une api sur symfony

## Creation et installation de la base de donnée mysql (symfony console d:d:c)
- Creation d'une base de donnée "symfonyapi"

## Creation des entités
- Post
- Comment

## Creation des fixtures pour remplir de donnée les deux entités
- Creation de fixtures dans le fichier AppFixtures

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