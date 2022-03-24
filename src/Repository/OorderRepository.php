<?php

namespace App\Repository;

use App\Entity\Oorder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Oorder|null find($id, $lockMode = null, $lockVersion = null)
 * @method Oorder|null findOneBy(array $criteria, array $orderBy = null)
 * @method Oorder[]    findAll()
 * @method Oorder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OorderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Oorder::class);
    }

    public function rivp_nbr_massages($userId){
        $entityManager = $this->getEntityManager();
        $nbr = 0;
        $query = $entityManager->createQuery(
            'SELECT o
            FROM App\Entity\Oorder o
            WHERE o.userId = :userId AND (o.product = 32 OR o.product = 33) AND o.status = \'ok\''
        )->setParameters(array('userId'=>$userId));

        $oorders = $query->getResult();

        foreach ($oorders as $oneOorder){
            if ($oneOorder->getProduct()->getId()==32)
            $nbr++;
            else
            $nbr = $nbr +2;
        }      
        return $nbr;
    }

    public function findByMonth($month,$year){
        $entityManager = $this->getEntityManager();
        $firstDate = date("Y-m-d H:i:s",mktime(0,0,0,$month,1,$year));
        $secondDate = date("Y-m-d H:i:s",mktime(0,0,0,($month+1),1,$year));
        $query = $entityManager->createQuery("SELECT o FROM App\Entity\Oorder o WHERE 
        o.createdAt>:firstDate AND o.createdAt<:secondDate AND o.status = 'ok'")->setParameters(
            array(               
                'firstDate'=>$firstDate,
                'secondDate'=>$secondDate             
        ));
        
        return $query->getResult();
    }

    // /**
    //  * @return Oorder[] Returns an array of Oorder objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Oorder
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
