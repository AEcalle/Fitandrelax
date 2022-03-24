<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Notification;

class NotificationManager{

    private $manager;

    public function __construct(EntityManagerInterface $manager){
        $this->manager = $manager;
    }

    public function add($createdAt,$structureId,$userId,$content){
        $notification = new Notification();

        $notification->setCreatedAt($createdAt);
        $notification->setStructure($structureId);
        $notification->setUserId($userId);
        $notification->setContent($content);
      

        $this->manager->persist($notification);
        $this->manager->flush();

        return $notification;
    }
}