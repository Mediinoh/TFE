<?php

// Déclaration de l'espace de noms pour le dépôt Commentaire
namespace App\Repository;

// Importation des classes nécessaires
use App\Entity\Commentaire;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Commentaire>
 *
 * La classe CommentaireRepository étend ServiceEntityRepository pour gérer les opérations liées à l'entité Commentaire.
 * 
 * @method Commentaire|null find($id, $lockMode = null, $lockVersion = null)
 * @method Commentaire|null findOneBy(array $criteria, array $orderBy = null)
 * @method Commentaire[]    findAll()
 * @method Commentaire[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentaireRepository extends ServiceEntityRepository
{
    // Constructeur de la classe qui initialise le dépôt avec le registre de gestion
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commentaire::class);
    }

    /**
     * Méthode pour récupérer les commentaires faits par un utilisateur spécifique.
     * 
     * @param Utilisateur $utilisateur L'utilisateur dont on veut récupérer les commentaires
     * @param int $maxCommentaires Le nombre maximum de commentaires à récupérer (par défaut : 15)
     * @return array Un tableau contenant les commentaires de l'utilisateur avec des détails sur les articles associés
     */
    public function recupererCommentairesUtilisateur(Utilisateur $utilisateur, int $maxCommentaires = 15)
    {
        // Utilisation de QueryBuilder pour construire la requête
        return $this->createQueryBuilder('c') // 'c' est un alias pour Commentaire
                    ->select('c AS commentaire, a.photo_article, a.titre') // Sélection des commentaires et des détails des articles
                    ->leftJoin('c.article', 'a') // Jointure avec l'entité Article pour accéder aux détails de l'article
                    ->where('c.utilisateur = :utilisateur') // Filtre les commentaires par utilisateur
                    ->setParameter('utilisateur', $utilisateur) // Définit le paramètre utilisateur
                    ->orderBy('c.date_commentaire', 'DESC') // Tri par date de commentaire (du plus récent au plus ancien)
                    ->setMaxResults($maxCommentaires) // Limite le nombre de résultats à $maxCommentaires
                    ->getQuery() // Génère la requête finale
                    ->getResult(); // Exécute la requête et retourne les résultats
    }
}
