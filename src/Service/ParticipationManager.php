<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Participation;

class ParticipationManager{

    private $manager;

    public function __construct(EntityManagerInterface $manager){
        $this->manager = $manager;
    }

    public function add($userId,$ssessionId,$createdAt,$inviteBy,$productId,$present){
        $participation = new Participation();
        
        $participation->setUserId($userId);
        $participation->setSsessionId($ssessionId);
        $participation->setCreatedAt($createdAt);
        $participation->setInvitedBy($inviteBy);
        $participation->setProductId($productId);
        $participation->setPresent($present);

        $this->manager->persist($participation);
        $this->manager->flush();

        return $participation;
    }

    public function setPresent($participations,$userIds){
        
        foreach ($participations as $oneParticipation){
            if (isset($userIds[$oneParticipation->getUserId()])){
                $oneParticipation->setPresent(1);
            }
            else{
                $oneParticipation->setPresent(0);
            }
            $this->manager->persist($oneParticipation);
            $this->manager->flush();
        }
    }

    public function delete($participation){
        $this->manager->remove($participation);
        $this->manager->flush();
    }

}