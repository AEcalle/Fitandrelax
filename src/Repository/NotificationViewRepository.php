<?php

namespace App\Repository;

use App\Entity\NotificationView;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method NotificationView|null find($id, $lockMode = null, $lockVersion = null)
 * @method NotificationView|null findOneBy(array $criteria, array $orderBy = null)
 * @method NotificationView[]    findAll()
 * @method NotificationView[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationViewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NotificationView::class);
    }

    // /**
    //  * @return NotificationView[] Returns an array of NotificationView objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('n.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?NotificationView
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
