<?php

// Déclaration de l'espace de noms pour le dépôt HistoriqueAchat
namespace App\Repository;

// Importation des classes nécessaires
use App\Entity\HistoriqueAchat;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HistoriqueAchat>
 *
 * La classe HistoriqueAchatRepository étend ServiceEntityRepository pour gérer les opérations liées à l'entité HistoriqueAchat.
 * 
 * @method HistoriqueAchat|null find($id, $lockMode = null, $lockVersion = null)
 * @method HistoriqueAchat|null findOneBy(array $criteria, array $orderBy = null)
 * @method HistoriqueAchat[]    findAll()
 * @method HistoriqueAchat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HistoriqueAchatRepository extends ServiceEntityRepository
{
    // Constructeur de la classe qui initialise le dépôt avec le registre de gestion
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HistoriqueAchat::class);
    }

    /**
     * Méthode pour récupérer les détails d'un achat spécifique.
     * 
     * @param int $achatId L'identifiant de l'achat dont on veut obtenir les détails
     * @return array Un tableau contenant les informations de l'achat
     */
    public function recupererDetailsAchat(int $achatId)
    {
        // Utilisation de QueryBuilder pour construire la requête
        return $this->createQueryBuilder('ha') // 'ha' est un alias pour HistoriqueAchat
                    ->select('a.photo_article', 'a.titre', 'lc.quantite', 'a.prix_unitaire', 'SUM(lc.quantite * a.prix_unitaire) AS prix_total') // Sélectionne les champs que l'on veut récupérer dont le calcul du prix total
                    ->leftJoin('ha.panier', 'p') // Joindre le panier associé à l'achat
                    ->leftJoin('p.ligneCommandes', 'lc') // Joindre les lignes de commande du panier
                    ->leftJoin('lc.article', 'a') // Joindre les articles associés aux lignes de commande
                    ->where('ha.id = :achatId') // Filtre par identifiant de l'achat
                    ->setParameter('achatId', $achatId) // Définit le paramètre 'achatId'
                    ->groupBy('a.photo_article', 'a.titre', 'lc.quantite', 'a.prix_unitaire') // Regroupement des résultats
                    ->getQuery() // Génère la requête finale
                    ->getResult(); // Exécute la requête et retourne les résultats
    }

    /**
     * Méthode pour récupérer l'historique des achats d'un utilisateur spécifique.
     * 
     * @param Utilisateur $utilisateur L'utilisateur dont on veut récupérer l'historique des achat
     * @return array Un tableau d'achats correspondant à l'utilisateur
     */
    public function recupererHistoriqueAchats(Utilisateur $utilisateur)
    {
        // Utilisation de QueryBuilder pour construire la requête
        return $this->createQueryBuilder('ha') // 'ha' est un alias pour HistoriqueAchat
                    ->where('ha.utilisateur = :utilisateur') // Filtre par utilisateur
                    ->setParameter('utilisateur', $utilisateur) // Définit le paramètre 'utilisateur'
                    ->getQuery() // Génère la requête finale
                    ->getResult(); // Exécute la requête et retourne les résultats
    }
}
