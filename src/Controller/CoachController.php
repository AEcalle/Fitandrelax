<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\SsessionRepository;
use App\Repository\ParticipationRepository; 
use App\Repository\UserRepository;
use App\Entity\Ssession;
use App\Entity\Notification;
use App\Entity\Tracker;
use App\Form\UserType;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/coach")
 */
class CoachController extends AbstractController
{
    /**
     * @Route("/planning", name="coachPlanning")
     */
    public function planning(SsessionRepository $repo_s, ParticipationRepository $repo_p, EntityManagerInterface $manager)
    {
        $tracker = new Tracker();
        $tracker->setUserId($this->getUser()->getId());
        $tracker->setPage('planning');
        $tracker->setCreatedAt(new \DateTime());
        $manager->persist($tracker);
        $manager->flush();

        $ssessions = $repo_s->findByCoach($this->getUser()->getId());

        //List of Participations
        $nbParticipationsBySsession = [];
          
        foreach ($ssessions as $oneSsession){            
            $participations = $repo_p->findBySsessionId($oneSsession->getId());               
            $nbParticipationsBySsession[$oneSsession->getId()] = count($participations);      
        }
 

        return $this->render('coach/planning.html.twig',['ssessions'=>$ssessions,
        'nbParticipations'=>$nbParticipationsBySsession]);
    }

    /**
    * @Route("/participations/{id}", name="coachParticipations")
    */

    public function participations(Ssession $ssession, ParticipationRepository $repo_p, SsessionRepository $repo_s, UserRepository $repo_u,
    EntityManagerInterface $manager){

        $tracker = new Tracker();
        $tracker->setUserId($this->getUser()->getId());
        $tracker->setPage('participations');
        $tracker->setCreatedAt(new \DateTime());
        $manager->persist($tracker);
        $manager->flush();
        
        if ($ssession->getActivity()=="Massage"){
            $participations = [];
            $ssessions = $repo_s->findBy(['activity'=>'Massage','structure'=>$ssession->getStructure(),'coach'=>$ssession->getCoach()]);
            foreach($ssessions as $oneSsession){
                if($oneSsession->getScheduledAt()->format('Y-m-d')==$ssession->getScheduledAt()->format('Y-m-d'))
                $participations[] = $repo_p->findOneBySsessionId($oneSsession->getId()); 
            }
        }else{
            $participations = $repo_p->findBySsessionId($ssession->getId());  
        }
        
          
        if (isset($_POST['presents'])){
            foreach ($participations as $oneParticipation){
                if (isset($oneParticipation)){
                    if (isset($_POST[$oneParticipation->getUserId()])){
                        $oneParticipation->setPresent(1);                    
                    }
                    else{
                        $oneParticipation->setPresent(0);
                    }
                    $manager->persist($oneParticipation);
                    $manager->flush();
                }
               
             
            }  
            $this->addFlash('notice','Présents enregistrés, merci !');  
            return $this->redirectToRoute('coachParticipations',['id'=>$ssession->getId()]);        
        }
        
        
        if ($ssession->getActivity()=="Massage"){
            $participations = [];
            $ssessions = $repo_s->findBy(['activity'=>'Massage','structure'=>$ssession->getStructure(),'coach'=>$ssession->getCoach()]);
            foreach($ssessions as $oneSsession){
                if($oneSsession->getScheduledAt()->format('Y-m-d')==$ssession->getScheduledAt()->format('Y-m-d'))
                $participations[] = $repo_p->findOneBySsessionId($oneSsession->getId()); 
            }
        }else{
            $participations = $repo_p->findBySsessionId($ssession->getId());  
        }     
        
        $listParticipants = [];    
        foreach ($participations as $oneParticipation){
            if (isset($oneParticipation)){
                $participant = $repo_u->findOneById($oneParticipation->getUserId());         
       
                $listParticipants[$oneParticipation->getId()] = $participant;
            }
         
        }
        return $this->render('coach/participations.html.twig',[
        'ssession'=>$ssession,
        'participations'=>$participations,
        'listParticipants'=>$listParticipants]);
    }

         /**
     * @Route("/profile", name="profileCoach")
     */
    public function profile(Request $request, EntityManagerInterface $manager){
        $tracker = new Tracker();
        $tracker->setUserId($this->getUser()->getId());
        $tracker->setPage('profile');
        $tracker->setCreatedAt(new \DateTime());
        $manager->persist($tracker);
        $manager->flush();
       
        $user = $this->getUser();
        if (isset($_POST['deleteUser'])){
            $user->setEmail("supprimé");
            $user->setPassword("");
            $user->setFirstname("supprimé");
            $user->setLastname("supprimé");
            $user->setMobile("supprimé");
            $user->setDeletedAt(new \DateTime());
            $manager->persist($user);
            $manager->flush(); 
            return $this->redirectToRoute('security_logout');
        }

       
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()){
            $notification = New Notification();
            $notification->setCreatedAt(new \DateTime());
            $notification->setStructureId(0);
            $notification->setUserId($user->getId());
            $notification->setContent("Profil modifié !");
            $manager->persist($notification);
            $manager->persist($user);
            $manager->flush(); 

            $this->addFlash('notice','Profil modifié !');
            return $this->redirectToRoute('updateNotif',['returnRoute'=>'profileCoach']);  
        }
        return $this->render('coach/profile.html.twig',['form'=>$form->createView()]);
    }
    /**
     * @Route("/modifyPassword", name="modifyPasswordCoach")
     */
    public function modifyPassword(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder){
    
        $tracker = new Tracker();
        $tracker->setUserId($this->getUser()->getId());
        $tracker->setPage('modifyPassword');
        $tracker->setCreatedAt(new \DateTime());
        $manager->persist($tracker);
        $manager->flush();

        $msg = "";
        if (isset($_POST['password'])){
            $old_pwd = $request->get('formerPassword'); 
            $new_pwd = $request->get('password'); 
       
            $user = $this->getUser();
            $checkPass = $encoder->isPasswordValid($user, $old_pwd);
            if($checkPass === true) {
                $new_pwd_encoded = $encoder->encodePassword($user, $new_pwd);
                $user->setPassword( $new_pwd_encoded);
                $manager->persist($user);
                $manager->flush();
                $this->addFlash(
                    'notice',
                    'Mot de passe modifié'
                    );  
            } else {
                $this->addFlash(
                    'notice',
                    'Mot de passe invalide'
                    );  
            }
        }
       
        return $this->render('coach/modifyPassword.html.twig');
    }
}


