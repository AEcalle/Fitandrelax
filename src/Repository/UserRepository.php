<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        // set the new encoded password on the User object
        $user->setPassword($newEncodedPassword);

        // execute the queries on the database
        $this->getEntityManager()->flush($user);
    }

    public function findByMonth($month,$year){
        $entityManager = $this->getEntityManager();
        $firstDate = date("Y-m-d H:i:s",mktime(0,0,0,$month,1,$year));
        $secondDate = date("Y-m-d H:i:s",mktime(0,0,0,($month+1),1,$year));
        $query = $entityManager->createQuery("SELECT u FROM App\Entity\User u WHERE 
        u.createdAt>:firstDate AND u.createdAt<:secondDate AND u.lastname!='test'")->setParameters(
            array(               
                'firstDate'=>$firstDate,
                'secondDate'=>$secondDate             
        ));
        
        return $query->getResult();
    }

    public function reminder(){
        $entityManager = $this->getEntityManager();
        $firstDate = date("2020-09-01 00:00:00");
        $query = $entityManager->createQuery("SELECT u FROM App\Entity\User u WHERE 
        u.createdAt>:firstDate AND u.structure = 23 AND u.coach = 0 AND u.password != '' AND u.email!='supprimé' AND u.email!='blutch70@hotmail.com' 
        AND u.id NOT IN (SELECT p.userId FROM App\Entity\Participation p) AND u.id IN (SELECT c.userId FROM App\Entity\Credit c WHERE
        c.amount>0)")->setParameters(
            array(               
                'firstDate'=>$firstDate,                            
        ));

        return $query->getResult();
    }

    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
