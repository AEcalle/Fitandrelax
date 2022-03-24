<?php

namespace App\Repository;

use App\Entity\Participation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Ssession;
/**
 * @method Participation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Participation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Participation[]    findAll()
 * @method Participation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParticipationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Participation::class);
    }

    public function participations($userId,$charged,$time,$invitedBy=false){
        $entityManager = $this->getEntityManager();
        $now = date("Y-m-d H:i:s");
        if ($invitedBy==0){
            $userIdorInvitedBy = "p.userId";
        }else
        {
            $userIdorInvitedBy = "p.invitedBy";
        }       
        $query = $entityManager->createQuery('SELECT p FROM App\Entity\Participation p WHERE 
        p.productId '.$charged.' 0 AND '.$userIdorInvitedBy.'=:userId AND p.ssessionId in (SELECT s.id FROM App\Entity\Ssession s
        WHERE s.scheduledAt'.$time.':nnow)')->setParameters(
            array(       
                'userId'=>$userId,
                'nnow'=>$now              
        ));
        return $query->getResult();
    }

    public function findByMonth($month,$year){
        $entityManager = $this->getEntityManager();
        $firstDate = date("Y-m-d H:i:s",mktime(0,0,0,$month,1,$year));
        $secondDate = date("Y-m-d H:i:s",mktime(0,0,0,($month+1),1,$year));
        $query = $entityManager->createQuery("SELECT p FROM App\Entity\Participation p WHERE 
        p.ssessionId in (SELECT s.id FROM App\Entity\Ssession s WHERE s.scheduledAt>:firstDate
        AND s.scheduledAt<:secondDate)")->setParameters(
            array(               
                'firstDate'=>$firstDate,
                'secondDate'=>$secondDate             
        ));
        
        return $query->getResult();
    }

    

    // /**
    //  * @return Participation[] Returns an array of Participation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Participation
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
