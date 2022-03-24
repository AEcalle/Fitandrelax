<?php

namespace App\Repository;

use App\Entity\Notification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Notification|null find($id, $lockMode = null, $lockVersion = null)
 * @method Notification|null findOneBy(array $criteria, array $orderBy = null)
 * @method Notification[]    findAll()
 * @method Notification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }  

    public function findByUserOrStructure($userId,$structureId)
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery('SELECT n FROM App\Entity\Notification n WHERE 
        n.userId = :userId OR n.structure = :structureId OR (n.userId = 0 AND n.structure = 0) 
        ORDER BY n.createdAt DESC')->setParameters(
            array(
                'userId'=>$userId,
                'structureId'=>$structureId
        ));
        return $query->getResult();
    }

    public function findByStructureNotNull(){
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery('SELECT n FROM App\Entity\Notification n WHERE 
        n.structure is not null ORDER BY n.createdAt DESC');
        return $query->getResult();
    }
    

    // /**
    //  * @return Notification[] Returns an array of Notification objects
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
    public function findOneBySomeField($value): ?Notification
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
