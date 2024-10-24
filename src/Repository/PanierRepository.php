<?php

//  Déclaration de l'espace de noms pour le dépôt Panier
namespace App\Repository;

// Importation des classes nécessaires
use App\Entity\Panier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Panier>
 *
 * La classe PanierRepository étend ServiceEntityRepository pour fournir des méthodes de recherche pour l'entité Panier.
 * 
 * @method Panier|null find($id, $lockMode = null, $lockVersion = null)
 * @method Panier|null findOneBy(array $criteria, array $orderBy = null)
 * @method Panier[]    findAll()
 * @method Panier[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PanierRepository extends ServiceEntityRepository
{
    // Constructeur de la classe qui initialise le dépôt avec le registre de gestion
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Panier::class);
    }

    // Méthode pour trouver le dernier panier qui n'est pas dans l'historique des achats d'un utilisateur
    public function findLastPanierNotInHistoriqueAchats($utilisateurId)
    {
        // Création d'une requête pour obtenir le dernier panier d'un utilisateur
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.historiqueAchats', 'ha') // Jointure avec l'historique des achats
            ->leftJoin('p.ligneCommandes', 'lc') // Jointure les lignes de commande
            ->leftJoin('lc.article', 'a') // Jointure avec les articles associés aux lignes de commande
            ->addSelect('lc', 'a') // Sélection des lignes de commande et des articles
            ->where('p.utilisateur = :utilisateur') // Filtre par utilisateur
            ->andWhere('ha.id IS NULL') // S'assure que le panier n'est pas dans l'historique d'achats
            ->setParameter('utilisateur', $utilisateurId) // Définit le paramètre utilisateur
            ->orderBy('p.createdAt', 'DESC') // Trie par date de création (s'assurer que le champ createAt existe)
            ->setMaxResults(1); // Limite le résultat à un seul panier

        // Exécute la requête et retourne le résultat
        return $qb->getQuery()->getOneOrNullResult();
    }
}
