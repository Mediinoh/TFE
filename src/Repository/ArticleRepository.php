<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 *
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    public function recupererNouveautes(int $maxResults = 15)
    {
        return $this->createQueryBuilder('a')
                    ->orderBy('a.id', 'DESC')
                    ->setMaxResults($maxResults)
                    ->getQuery()
                    ->getResult();
    }

    public function recupererMeilleuresVentes(int $total_ventes = 5)
    {
        return $this->createQueryBuilder('a')
                    ->select('a, c, SUM(lc.quantite) AS total_ventes')
                    ->leftJoin('a.categorie', 'c')
                    ->leftJoin('a.ligneCommandes', 'lc')
                    ->leftJoin('lc.panier', 'p')
                    ->leftJoin('p.historiqueAchats', 'ha')
                    ->groupBy('a.id')
                    ->having('total_ventes >= :total_ventes')
                    ->setParameter('total_ventes', $total_ventes)
                    ->orderBy('total_ventes', 'DESC')
                    ->getQuery()
                    ->getResult();
    }

    public function filtrerArticlesPar($categorie, $motCle) {
        $queryBuilder = $this->createQueryBuilder('a');

        if (!empty($categorie)) {
            $queryBuilder->andWhere('a.categorie = :categorie')
                            ->setParameter('categorie', $categorie);
        }

        if (!empty($motCle)) {
            $queryBuilder->andWhere('a.titre LIKE :motCle')
                            ->setParameter('motCle', '%' . $motCle . '%');
        }

        return $queryBuilder->getQuery()->getResult();
    }

//    /**
//     * @return Article[] Returns an array of Article objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Article
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
