<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Ticket;

class TicketManager{

    private $manager;

    public function __construct(EntityManagerInterface $manager){
        $this->manager = $manager;
    }

    public function add($ssessionId,$productId){
        $ticket = new Ticket();
        $ticket->setSsessionId($ssessionId);
        $ticket->setProductId($productId);

        $this->manager->persist($ticket);
        $this->manager->flush();

        return $ticket;
    }

    public function removeList($tickets){
        foreach($tickets as $ticket){
            $this->manager->remove($ticket);
            $this->manager->flush();
        }       
    }

}