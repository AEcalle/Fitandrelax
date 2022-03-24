<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Optout;

class OptoutManager{

    private $manager;

    public function __construct(EntityManagerInterface $manager){
        $this->manager = $manager;
    }

    public function add($userId,$ssessionId,$createdAt){
        $optout = new Optout();
        $optout->setUserId($userId);
        $optout->setSsessionId($ssessionId);
        $optout->setCreatedAt($createdAt);

        $this->manager->persist($optout);
        $this->manager->flush();

        return $optout;
    }  

}