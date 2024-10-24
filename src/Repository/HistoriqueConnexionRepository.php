<?php

// Déclaration de l'espace de noms pour le dépôt HistoriqueConnexion
namespace App\Repository;

// Importation des classes nécessaires
use App\Entity\HistoriqueConnexion;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
/**
 * @extends ServiceEntityRepository<HistoriqueConnexion>
 *
 * La classe HistoriqueConnexionRepository étend ServiceEntityRepository pour gérer les opérations liées à l'entité HistoriqueConnexion.
 * 
 * @method HistoriqueConnexion|null find($id, $lockMode = null, $lockVersion = null)
 * @method HistoriqueConnexion|null findOneBy(array $criteria, array $orderBy = null)
 * @method HistoriqueConnexion[]    findAll()
 * @method HistoriqueConnexion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HistoriqueConnexionRepository extends ServiceEntityRepository
{
    // Constructeur de la classe qui initialise le dépôt avec le registre de gestion
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HistoriqueConnexion::class);
    }

    /**
     * Récupère le nombre de connexions pour un utilisateur donné.
     * 
     * @param Utilisateur $utilisateur L'utilisateur dont on souhaite récupérer le nombre de connexions.
     * @param bool $week Indique si l'on veut le nombre de connexions des 7 derniers jours (true) ou de la journée actuelle (false).
     * @return int Le nombre de connexions correspondant aux critères spécifiés.
     */
    public function recupererNbConnexions(Utilisateur $utilisateur, bool $week = false)
    {
        $queryBuilder = $this->createQueryBuilder('hc') // Création d'un QueryBuilder pour construire la requête
                                ->select('COUNT(hc.id) AS NbConnexions') // Sélectionne le nombre de connexions
                                ->where('hc.utilisateur = :utilisateur') // Filtre par utilisateur
                                ->setParameter('utilisateur', $utilisateur); // Définit le paramètre pour l'utilisateur
        
                                    // Si l'option de semaine est activée, on filtre les connexions des 7 derniers jours
        if ($week) {
            $date = new \DateTime('-7 days'); // Date 7 jours dans le passé
            $date->setTime(0, 0, 0); // Réinitialise l'heure à 00:00:00
            $queryBuilder->andWhere('hc.date_connexion >= :date') // Filtre par date de connexion
                            ->setParameter('date', $date);            
        } else {
            // Si l'option de semaine est désactivée, on filtre pour aujourd'hui
            $todayStart = new \DateTime(); // Récupère la date actuelle
            $todayStart->setTime(0, 0, 0); // Réinitialise l'heure à 00:00:00
            
            $todayEnd = new \DateTime(); // Récupère la date actuelle
            $todayEnd->setTime(23, 59, 59); // Réinitialise l'heure à 23:59:59

            // Filtre les connexions qui ont eu lieu aujourd'hui
            $queryBuilder->andWhere('hc.date_connexion BETWEEN :todayStart AND :todayEnd')
                            ->setParameter('todayStart', $todayStart) // Définit le début de la plage
                            ->setParameter('todayEnd', $todayEnd); // Définit la fin de la plage
        }

        // Exécute la requête et rtourne le résultat unique (nombre de connexions)
        return $queryBuilder->getQuery()->getSingleScalarResult();
    }
}
