<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\FreeCredit;

class FreeCreditManager{

    private $manager;

    public function __construct(EntityManagerInterface $manager){
        $this->manager = $manager;
    }

    public function add($product,$user,$amount){
        $freeCredit = new FreeCredit();
       
        $freeCredit->setProduct($product);
        $freeCredit->setUser($user);
        $freeCredit->setAmount($amount);


        $this->manager->persist($freeCredit);
        $this->manager->flush();

        return $freeCredit;
    }

}