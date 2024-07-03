<?php

namespace App\Repository;

use App\Entity\HistoriqueConnexion;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr;

/**
 * @extends ServiceEntityRepository<HistoriqueConnexion>
 *
 * @method HistoriqueConnexion|null find($id, $lockMode = null, $lockVersion = null)
 * @method HistoriqueConnexion|null findOneBy(array $criteria, array $orderBy = null)
 * @method HistoriqueConnexion[]    findAll()
 * @method HistoriqueConnexion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HistoriqueConnexionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HistoriqueConnexion::class);
    }

    public function recupererNbConnexions(Utilisateur $utilisateur, bool $week = false)
    {
        $queryBuilder = $this->createQueryBuilder('hc')
                                ->select('COUNT(hc.id) AS NbConnexions')
                                ->where('hc.utilisateur = :utilisateur')
                                ->setParameter('utilisateur', $utilisateur);
        
        if ($week) {
            $date = new \DateTime('-7 days');
            $date->setTime(0, 0, 0);
            $queryBuilder->andWhere('hc.date_connexion >= :date')
                            ->setParameter('date', $date);            
        } else {
            $todayStart = new \DateTime();
            $todayStart->setTime(0, 0, 0);
            
            $todayEnd = new \DateTime();
            $todayEnd->setTime(23, 59, 59);

            $queryBuilder->andWhere('hc.date_connexion BETWEEN :todayStart AND :todayEnd')
                            ->setParameter('todayStart', $todayStart)
                            ->setParameter('todayEnd', $todayEnd);
        }

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

//    /**
//     * @return HistoriqueConnexion[] Returns an array of HistoriqueConnexion objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('h')
//            ->andWhere('h.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('h.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?HistoriqueConnexion
//    {
//        return $this->createQueryBuilder('h')
//            ->andWhere('h.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
