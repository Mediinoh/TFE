<?php

// Déclaration de l'espace de noms pour le dépôt MessageReaction
namespace App\Repository;

// Importation des classes nécessaires
use App\Entity\MessageReaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MessageReaction>
 *
 * La classe MessageReactionRepository étend ServiceEntityRepository pour gérer les opérations liées à l'entité MessageReaction
 * 
 * @method MessageReaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method MessageReaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method MessageReaction[]    findAll()
 * @method MessageReaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageReactionRepository extends ServiceEntityRepository
{
    // Constructeur de la classe qui initialise le dépôt avec le registre de gestion
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MessageReaction::class);
    }
}
