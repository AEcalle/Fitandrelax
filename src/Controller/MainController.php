<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Notification;
use App\Repository\StructureRepository;
use App\Repository\NotificationRepository;
use App\Repository\NotificationViewRepository;

class MainController extends AbstractController
{
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @Route("/", name="home")
     */
    public function index(StructureRepository $repo_s, NotificationRepository $repo_n, 
    NotificationViewRepository $repo_nv,EntityManagerInterface $manager)
    {
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
            return $this->redirectToRoute('adminDashboard');
        }
        else if ($this->get('security.authorization_checker')->isGranted('ROLE_COACH')){
            return $this->redirectToRoute('coachPlanning');
        }
        else if(!isset($user)){
            if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')){
                $user = $this->getUser();                
                $structure = $repo_s->findOneById($user->getStructure());                
                $this->session->set('structure', $structure);
                $notifications = $repo_n->findByUserId($user->getId());
                if (empty($notifications)){
                    //First Notification
                    $notification = New Notification();
                    $notification->setCreatedAt(new \DateTime());
                    $notification->setStructure(null);
                    $notification->setUserId($user->getId());
                    $notification->setContent("Notre équipe vous souhaite la bienvenue sur votre espace Relaxeo.");
                    $manager->persist($notification);
                    $manager->flush();                   
                }    
                $notifications = $repo_n->findByUserOrStructure($user->getId(),$structure->getId());
                $this->session->set('notifications', $notifications);                        
                $notificationViews = $repo_nv->findByUserId($user->getId());
                $newNotifications = count($notifications) - count($notificationViews);
                $this->session->set('newNotifications', $newNotifications);
                return $this->redirectToRoute('planning');        
            }
            return $this->redirectToRoute('security_login');
        }
        $user = $this->getUser();
        $structure = $repo_s->findOneById($user->getStructure());
        $this->session->set('structureName', $structure->getName());     
        return $this->redirectToRoute('dashboard');        
    }

     /**
     * @Route("/updateNotif/{returnRoute}", name="updateNotif")
     */
    public function updateNotification(NotificationRepository $repo_n, 
    NotificationViewRepository $repo_nv, $returnRoute){
        $user = $this->getUser();

        if($this->session->get('structure')){
            $structureId = $this->session->get('structure')->getId();
        }else{
            $structureId = -1;
        }     
        $notifications = $repo_n->findByUserOrStructure($user->getId(),$structureId); 
        $this->session->set('notifications', $notifications);
        $notificationViews = $repo_nv->findByUserId($user->getId());
        $newNotifications = count($notifications) - count($notificationViews);
        $this->session->set('newNotifications', $newNotifications);
        return $this->redirectToRoute($returnRoute);  
    }

}
