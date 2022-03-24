<?php

namespace App\Repository;

use App\Entity\Ssession;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @method Ssession|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ssession|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ssession[]    findAll()
 * @method Ssession[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SsessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ssession::class);
    }

    public function findAll(){
        return $this->findBy(array(), array('scheduledAt' => 'ASC'));
    }

    public function findByStructureFuture($structure){
        $entityManager = $this->getEntityManager();
        $now = date("Y-m-d H:i:s");
        $query = $entityManager->createQuery('SELECT s FROM App\Entity\Ssession s WHERE 
        s.finishedAt>:nnow AND s.structure=:structure ORDER BY s.scheduledAt ASC')->setParameters(
            array(
                'nnow'=>$now,
                'structure'=>$structure
        ));
        return $query->getResult();
    }
    
    public function previousSsession(Ssession $ssession){
        $entityManager = $this->getEntityManager();
        $scheduledtAt = $ssession->getScheduledAt()->format("Y-m-d H:i:s");
        $limitDate = date("Y-m-d H:i:s",strtotime($scheduledtAt."-15 days"));
        $query = $entityManager->createQuery('SELECT s FROM App\Entity\Ssession s
        WHERE s.structure = :structure AND 
        s.activity = :activity AND s.location =:Llocation AND s.scheduledAt<:scheduledAt AND s.scheduledAt>:limitDate
        ORDER BY s.scheduledAt DESC')->setParameters(
            array('structure'=>$ssession->getStructure(),
            'activity'=>$ssession->getActivity(),
            'Llocation'=>$ssession->getLocation(),
            'scheduledAt'=>$ssession->getScheduledAt(),
            'limitDate'=>$limitDate
        ));
       
        $ssessions = $query->getResult(); 
        if (!empty($ssessions)){
            return $ssessions;
        }else{
            return false;
        }   
    }

    public function futureSsession(Ssession $ssession){
        $entityManager = $this->getEntityManager();
        $scheduledtAt = $ssession->getScheduledAt()->format("Y-m-d H:i:s");
        $limitDate = date("Y-m-d H:i:s",strtotime($scheduledtAt."+15 days"));
        $query = $entityManager->createQuery('SELECT s FROM App\Entity\Ssession s
        WHERE s.structure = :structure AND 
        s.activity = :activity AND s.location =:Llocation AND s.scheduledAt>:scheduledAt AND s.scheduledAt<:limitDate
        ORDER BY s.scheduledAt DESC')->setParameters(
            array('structure'=>$ssession->getStructure(),
            'activity'=>$ssession->getActivity(),
            'Llocation'=>$ssession->getLocation(),
            'scheduledAt'=>$ssession->getScheduledAt(),
            'limitDate'=>$limitDate
        ));
       
        $ssessions = $query->getResult(); 
        if (!empty($ssessions)){
            return $ssessions;
        }else{
            return false;
        }   
    }
    
    public function findSsessionsByDate($date){
        $entityManager = $this->getEntityManager();
        $startDay = date("Y-m-d 00:00:01",strtotime($date));
        $endDay = date("Y-m-d 23:59:59",strtotime($date));
        $query = $entityManager->createQuery('SELECT s FROM App\Entity\Ssession s WHERE 
        s.scheduledAt>:startDay AND s.scheduledAt<:endDay')->setParameters(
            array(
                'startDay'=>$startDay,
                'endDay'=>$endDay               
        ));
        return $query->getResult();
    }

    public function findActivities($structure){
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery('SELECT DISTINCT(s.activity) FROM App\Entity\Ssession s 
        WHERE s.structure=:structure')->setParameters(
            array(             
                'structure'=>$structure
        ));
        return $query->getResult();
    }

    public function findByMonth($month,$year){
        $entityManager = $this->getEntityManager();
        $firstDate = date("Y-m-d H:i:s",mktime(0,0,0,$month,1,$year));
        $secondDate = date("Y-m-d H:i:s",mktime(0,0,0,($month+1),1,$year));
        $query = $entityManager->createQuery("SELECT s FROM App\Entity\Ssession s WHERE 
        s.scheduledAt>:firstDate AND s.scheduledAt<:secondDate")->setParameters(
            array(               
                'firstDate'=>$firstDate,
                'secondDate'=>$secondDate             
        ));
        
        return $query->getResult();
    }

    

    // /**
    //  * @return Ssession[] Returns an array of Ssession objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Ssession
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
