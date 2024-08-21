<?php

namespace App\Repository;

use App\Entity\Panier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Panier>
 *
 * @method Panier|null find($id, $lockMode = null, $lockVersion = null)
 * @method Panier|null findOneBy(array $criteria, array $orderBy = null)
 * @method Panier[]    findAll()
 * @method Panier[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PanierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Panier::class);
    }

    public function findLastPanierNotInHistoriqueAchats($utilisateurId)
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.historiqueAchats', 'ha')
            ->leftJoin('p.ligneCommandes', 'lc') // Joindre les lignes de commande
            ->leftJoin('lc.article', 'a') // Joindre les articles associés aux lignes de commande
            ->addSelect('lc', 'a') // Sélectionner les lignes de commande et les articles
            ->where('p.utilisateur = :utilisateur')
            ->andWhere('ha.id IS NULL')
            ->setParameter('utilisateur', $utilisateurId)
            ->orderBy('p.createdAt', 'DESC') // Assurez-vous que vous avez un champ `createdAt` dans votre table Panier
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

//    /**
//     * @return Panier[] Returns an array of Panier objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Panier
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
