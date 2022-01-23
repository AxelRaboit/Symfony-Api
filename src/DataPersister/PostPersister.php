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