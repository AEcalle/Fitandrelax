<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\User;

class UserManager{

    private $manager;
    private $encoder;

    public function __construct(EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder){
        $this->manager = $manager;
        $this->encoder = $encoder;
    }

    public function add($lastname,$firstname,$email,$password,$roles,$structure,$mobile,
    $createdAt,$coach,$rappel,$aadmin){

        $user = new User();

        $user->setLastname($lastname);
        $user->setFirstname($firstname);
        $user->setEmail($email);
        $hash = $this->encoder->encodePassword($user, $password);
        $user->setPassword($hash); 
        $user->setRoles($roles);
        $user->setStructure($structure);
        $user->setMobile($mobile);       
        $user->setCreatedAt($createdAt);   
        $user->setCoach($coach);
        $user->setRappel($rappel);
        $user->setAadmin($aadmin);

        $this->manager->persist($user);
        $this->manager->flush();

        return $user;
    }

    public function update($user,$lastname,$firstname,$email,$password,$roles,$structure,$mobile,
    $createdAt,$coach,$rappel,$aadmin,$emailVerify){        

        $user->setLastname($lastname);
        $user->setFirstname($firstname);
        $user->setEmail($email);
        $hash = $this->encoder->encodePassword($user, $password);
        $user->setPassword($hash); 
        $user->setRoles($roles);
        $user->setStructure($structure);
        $user->setMobile($mobile);       
        $user->setCreatedAt($createdAt);   
        $user->setCoach($coach);
        $user->setRappel($rappel);
        $user->setAadmin($aadmin);
        $user->setEmailVerify($emailVerify);

        $this->manager->persist($user);
        $this->manager->flush();

        return $user;
    }

}