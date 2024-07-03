<?php

namespace App\Repository;

use App\Entity\HistoriqueAchat;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HistoriqueAchat>
 *
 * @method HistoriqueAchat|null find($id, $lockMode = null, $lockVersion = null)
 * @method HistoriqueAchat|null findOneBy(array $criteria, array $orderBy = null)
 * @method HistoriqueAchat[]    findAll()
 * @method HistoriqueAchat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HistoriqueAchatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HistoriqueAchat::class);
    }

    public function recupererDetailsAchat(int $achatId)
    {
        return $this->createQueryBuilder('ha')
                    ->select('a.photo_article', 'a.titre', 'lc.quantite', 'a.prix_unitaire', 'SUM(lc.quantite * a.prix_unitaire) AS prix_total')
                    ->leftJoin('ha.panier', 'p')
                    ->leftJoin('p.ligneCommandes', 'lc')
                    ->leftJoin('lc.article', 'a')
                    ->where('ha.id = :achatId')
                    ->setParameter('achatId', $achatId)
                    ->groupBy('a.photo_article', 'a.titre', 'lc.quantite', 'a.prix_unitaire')
                    ->getQuery()
                    ->getResult();
    }

    public function recupererHistoriqueAchats(Utilisateur $utilisateur)
    {
        return $this->createQueryBuilder('ha')
                    ->where('ha.utilisateur = :utilisateur')
                    ->setParameter('utilisateur', $utilisateur)
                    ->getQuery()
                    ->getResult();
    }

//    /**
//     * @return HistoriqueAchat[] Returns an array of HistoriqueAchat objects
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

//    public function findOneBySomeField($value): ?HistoriqueAchat
//    {
//        return $this->createQueryBuilder('h')
//            ->andWhere('h.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
