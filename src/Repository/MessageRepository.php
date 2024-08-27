<?php

namespace App\Repository;

use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Message>
 *
 * @method Message|null find($id, $lockMode = null, $lockVersion = null)
 * @method Message|null findOneBy(array $criteria, array $orderBy = null)
 * @method Message[]    findAll()
 * @method Message[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    public function recupererMessages(int $maxMessages = 30)
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.reactions', 'r')
            ->addSelect('SUM(CASE WHEN r.reaction_type = :like THEN 1 ELSE 0 END) AS likeCount')
            ->addSelect('SUM(CASE WHEN r.reaction_type = :dislike THEN 1 ELSE 0 END) AS dislikeCount')
            ->Where('m.reponseA IS NULL')
            ->groupBy('m.id')
            ->setParameter('like', 'like')
            ->setParameter('dislike', 'dislike')
            ->orderBy('m.date_message', 'DESC')
            ->setMaxResults($maxMessages)
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Message[] Returns an array of Message objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Message
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
