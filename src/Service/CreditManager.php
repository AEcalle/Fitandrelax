<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Credit;

class CreditManager{

    private $manager;

    public function __construct(EntityManagerInterface $manager){
        $this->manager = $manager;
    }

    public function add($productId,$userId,$amount,$structureId){
        $credit = new Credit();
       
        $credit->setProductId($productId);
        $credit->setUserId($userId);
        $credit->setAmount($amount);
        $credit->setStructureId($structureId);

        $this->manager->persist($credit);
        $this->manager->flush();

        return $credit;
    }

    public function removeList($credits){
        foreach($credits as $credit){
            $this->manager->remove($credit);
            $this->manager->flush();
        }       
    }

    public function setAmount($credit,$diff){

        $credit->setAmount($credit->getAmount() + $diff);
        $this->manager->persist($credit);
        $this->manager->flush();

    }

}