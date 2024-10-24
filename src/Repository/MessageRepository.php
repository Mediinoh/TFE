<?php

// Déclaration de l'espace de noms pour le dépôt Message
namespace App\Repository;

// Importation des classes nécessaires
use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Message>
 *
 * La classe MessageRepositiry étend ServiceEntityRepository pour gérer les opérations liées à l'entité Message.
 * 
 * @method Message|null find($id, $lockMode = null, $lockVersion = null)
 * @method Message|null findOneBy(array $criteria, array $orderBy = null)
 * @method Message[]    findAll()
 * @method Message[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageRepository extends ServiceEntityRepository
{
    // Constructeur de la classe qui initialise le dépôt avec le registre de gestion
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    // Méthode pour récuper les messages avec un nombre maximal de messages à retourner
    public function recupererMessages(int $maxMessages = 30)
    {
        // Création d'une requête pour récupérer les messages
        return $this->createQueryBuilder('m')
            ->leftJoin('m.reactions', 'r') // Jointure avec les réactions associées aux messages
            ->addSelect('SUM(CASE WHEN r.reaction_type = :like THEN 1 ELSE 0 END) AS likeCount') // Compte le nombre de "like" pour chaque message
            ->addSelect('SUM(CASE WHEN r.reaction_type = :dislike THEN 1 ELSE 0 END) AS dislikeCount') // Compte le nombre de "dislike" pour chaque message
            ->Where('m.reponseA IS NULL') // Filtre pour n'inclure que les messages qui ne sont pas des réponses
            ->groupBy('m.id') // Regroupe les résultats par ID de message
            ->setParameter('like', 'like') // Définit le paramètre pour le type de réaction "like"
            ->setParameter('dislike', 'dislike') // Définit le paramètre pour le type de réaction "dislike"
            ->orderBy('m.date_message', 'DESC') // Trie les messages par date dans l'ordre décroissant
            ->setMaxResults($maxMessages) // Limite le nombre de résultats retournés
            ->getQuery() // Prépare la requête
            ->getResult(); // Exécute la requête et retourne les résultats
    }
}
