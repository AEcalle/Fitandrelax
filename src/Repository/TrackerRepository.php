<?php

namespace App\Repository;

use App\Entity\Tracker;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Tracker|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tracker|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tracker[]    findAll()
 * @method Tracker[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrackerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tracker::class);
    }

    public function lastTrackers($limit){
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery('SELECT DISTINCT t.userId, MAX(t.createdAt) FROM App\Entity\Tracker t
        GROUP BY t.userId ORDER BY MAX(t.createdAt) DESC')->setMaxResults($limit);
        
        $results = $query->getResult();
       
        $trackers = [];
       foreach($results as $oneResult){
            $query = $entityManager->createQuery('SELECT t FROM App\Entity\Tracker t
            WHERE t.userId = :userId ORDER BY t.createdAt DESC')
            ->setParameters(array('userId'=>$oneResult['userId']))
            ->setMaxResults(1);
            $results2 = $query->getResult();
            foreach($results2 as $oneResult2){
                $trackers[] = $oneResult2;
            }
            
        }
     
        return $trackers;

    }

    // /**
    //  * @return Tracker[] Returns an array of Tracker objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Tracker
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
