<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Ssession;
use App\Entity\User;
use App\Entity\Structure;
use App\Entity\Product;
use App\Entity\Participation;
use App\Entity\WaitingList;
use App\Entity\EmailNotification;
use App\Entity\NotificationView;
use App\Entity\Notification;
use App\Entity\Oorder;
use App\Entity\Optout;
use App\Entity\Location;
use App\Entity\Credit;
use App\Entity\Tracker;
use App\Repository\SsessionRepository;
use App\Repository\StructureRepository;
use App\Repository\ParticipationRepository;
use App\Repository\CreditRepository;
use App\Repository\ProductRepository;
use App\Repository\CatalogRepository;
use App\Repository\TicketRepository;
use App\Repository\OorderRepository;
use App\Repository\WaitingListRepository;
use App\Repository\UserRepository;
use App\Repository\RuleRepository;
use App\Repository\EmailNotificationRepository;
use App\Repository\NotificationViewRepository;
use App\Repository\NotificationRepository;
use App\Repository\TrackerRepository;
use App\Repository\ActivityRepository;
use App\Form\UserType;
use App\Form\ParticipationType;
use App\Form\WaitingListType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class UserController extends AbstractController
{    
    private $session;

    public function __construct(SessionInterface $session, StructureRepository $repo_st)
    {
        $this->session = $session;           
    }
    
    
    /**
     * @Route("/history", name="history")
     */
    public function dashboard(CreditRepository $repo_c,ParticipationRepository $repo_p, 
    SsessionRepository $repo_s, OorderRepository $repo_o, ProductRepository $repo_pr,
    EntityManagerInterface $manager)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        if ($this->session->get('structure')==null){
           return $this->redirectToRoute('home');
        }  
         $user = $this->getUser();

         $tracker = new Tracker();
         $tracker->setUserId($user->getId());
         $tracker->setPage('history');
         $tracker->setCreatedAt(new \DateTime());
         $manager->persist($tracker);
         $manager->flush();

         //Credits
         $credits = $repo_c->findByUserId($user->getId());
         $amount = 0;
         foreach ($credits as $oneCredit){
             $amount = $amount + $oneCredit->getAmount();
         } 
         
         //Participations
         $participations = $repo_p->findByUserId($user->getId());
         $ssessions = [];
         $nbParticipations = 0;         
         foreach ($participations as $oneParticipation){
            $nbParticipations++;
            $ssessions[] = $repo_s->findOneById($oneParticipation->getSsessionId());
         }

        //Invitations
        $invitations = $repo_p->findByInvitedBy($user->getId());
        $ssessions_invitations = [];
        foreach($invitations as $oneInvitation){
            $ssessions_invitations[] = $repo_s->findOneById($oneInvitation->getSsessionId());        
        }

         //Orders
         $oorders = $repo_o->findBy(['userId'=>$user->getId(),'status'=>'ok']);
         $products = [];
         foreach ($oorders as $oneOorder){
             $products[$oneOorder->getId()] = $repo_pr->findOneById($oneOorder->getProduct()->getId());
         }        

        return $this->render('user/history.html.twig',['credit'=>$amount
        ,'ssessions'=>$ssessions
        ,'nbParticipations'=>$nbParticipations
        ,'ssessions_invitations'=>$ssessions_invitations
        ,'oorders'=>$oorders
        ,'products'=>$products
        ]);
    }

     /**
     * @Route("/planning", name="planning")
     */
    public function planning(SsessionRepository $repo_s, StructureRepository $repo_st,
    ParticipationRepository $repo_p,
    TicketRepository $repo_t, CreditRepository $repo_c, WaitingListRepository $repo_w, 
    RuleRepository $repo_r, ProductRepository $repo_pr, TrackerRepository $repo_tr,
    NotificationRepository $repo_n, NotificationViewRepository $repo_nv, 
    Request $request, EntityManagerInterface $manager)
    {      
        
        if ($this->session->get('structure')==null){
            return $this->redirectToRoute('home');
         } 
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        
        $lastTracker = $repo_tr->findBy(['userId'=>$this->getUser()->getId()],['createdAt'=>'DESC'],1,0);
        if (isset($lastTracker[0])){
            if ($lastTracker[0]->getPage()=="purchase"){
                $tracker = new Tracker();
                $tracker->setUserId($this->getUser()->getId());
                $tracker->setPage('planning');
                $tracker->setCreatedAt(new \DateTime());
                $manager->persist($tracker);
                $manager->flush();
                return $this->redirectToRoute('planning');
            }
        }
         
        $tracker = new Tracker();
        $tracker->setUserId($this->getUser()->getId());
        $tracker->setPage('planning');
        $tracker->setCreatedAt(new \DateTime());
        $manager->persist($tracker);
        $manager->flush();

        //Credits
        $credits = $repo_c->findByUserId($this->getUser()->getId());
       
        $productId = 0;
        $amounts = [];
        $creditsList = [];
        $productsName = [];
        foreach ($credits as $oneCredit){
            if ($oneCredit->getProductId()!=$productId){               
                $amounts[$oneCredit->getId()] = $oneCredit->getAmount();
                $productsName [$oneCredit->getId()] = $repo_pr->findOneById($oneCredit->getProductId())->getName();
                $productId = $oneCredit->getProductId();
            }            
        }

        //List of Ssessions
       
        $ssessions = $repo_s->findByStructureFuture($this->session->get('structure'));
      
        if ($this->session->get('structure')->getPublicSsessions()==1 && $this->session->get('structure')->getId()!=23){
            $structure_enligne = $repo_st->findByCode(null);
            $ssessions_enligne = $repo_s->findByStructureFuture($structure_enligne);
            foreach ($ssessions_enligne as $oneSsession){
                $ssessions[] = $oneSsession;
            }
        }
       

        $participationsBySsession = [];       
        $already = [];
        $already_waiting = [];
        $enoughCredit = [];
        $anteriorite = [];
        $invitation = [];
        $waitingNumero = [];
        foreach ($ssessions as $oneSsession){            
            $participations = $repo_p->findBySsessionId($oneSsession->getId());
           $already[$oneSsession->getId()] = "no";
           $invitation[$oneSsession->getId()] = "non";
            foreach ($participations as $oneParticipation)
            {
                if ($oneParticipation->getUserId()== $this->getUser()->getId()){
                    $already[$oneSsession->getId()] = "yes";
                }
                if ($oneParticipation->getInvitedBy()== $this->getUser()->getId()){
                    $invitation[$oneSsession->getId()] = "yes";
                }
            }            
            $participationsBySsession[$oneSsession->getId()] = count($participations);     
          
            
            $tickets = $repo_t->findBySsessionId($oneSsession->getId());            
           // If ssession isnt free 
           if (!empty($tickets)){
                // Verify if user has credits and return credit object              
                $verify = $repo_c->verify($tickets,$this->getUser()->getId());
                if ($verify !=false){
                    $enoughCredit[$oneSsession->getId()] = "yes";
                }
                else{
                    $enoughCredit[$oneSsession->getId()] = "no";
                }                
            }
            else{
                $enoughCredit[$oneSsession->getId()] = "yes";
            }
       
            //Already on waitinglist
            
            $waitingLists = $repo_w->findBy(['ssessionId'=>$oneSsession->getId()],['id'=>'ASC']);
            $already_waiting[$oneSsession->getId()] = "no";
            $waitingNumero[$oneSsession->getId()] = 0;
            foreach ($waitingLists as $oneWaitingList)
            {
                $waitingNumero[$oneSsession->getId()]++;
                if ($oneWaitingList->getUserId()== $this->getUser()->getId()){
                    $already_waiting[$oneSsession->getId()] = "yes";
                break;
                }
            }           
         
        }
 
        //Participation Form
        $participation = new Participation();
        $form = $this->createForm(ParticipationType::class,$participation);

        $form->handleRequest($request); 
        
         //Waiting List Form
         $waitingList = new WaitingList();
         $form_waiting = $this->createForm(WaitingListType::class,$waitingList);         

         $form_waiting->handleRequest($request); 
        
        if ($form->isSubmitted() && $form->isValid() && $form->get('submit_post')->isClicked()){  
            
            $existingParticipation = $repo_p->findBy(['userId'=>$participation->getUserId(),
            'ssessionId'=>$participation->getSsessionId()]);
            if (!empty($existingParticipation))
                return $this->redirectToRoute('planning');
            
            $ssession = $repo_s->findOneById($participation->getSsessionId());
            //Check if there is still places
            if ($participationsBySsession[$ssession->getId()]>=$ssession->getParticipationMax()){
                
                $waitingList->setUserId($participation->getUserId());
                $waitingList->setSsessionId($participation->getSsessionId());
                $manager->persist($waitingList);
                $manager->flush();
                $this->addFlash('notice', 'Vous avez été placé sur liste d\'attente.');
                return $this->redirectToRoute('planning');
            }
            
            //Check if rules exists
          
            $rules = $repo_r->findBy(['structureId'=>$ssession->getStructure(),'activity'=>$ssession->getActivity()]);
           
            if (!empty($rules)){
                foreach ($rules as $oneRule){
                    if($oneRule->getName()=="anteriorite"){
                        $previousSsessions = $repo_s->previousSsession($ssession);
                        if ($previousSsessions!=false){                           
                            foreach ($previousSsessions as $onePreviousSsession){
                                $previousParticipation = $repo_p->findBy(['ssessionId'=>$onePreviousSsession->getId(),'userId'=>$this->getUser()->getId()]);
                                if (!empty($previousParticipation) && $ssession->getScheduledAt()->format('Y-m-d H:i:s')>date("Y-m-d H:i:s",strtotime("+7 days"))){
                                    
                                    $waitingList = new WaitingList();
                                    $waitingList->setUserId($this->getUser()->getId());
                                    $waitingList->setSsessionId($ssession->getId());
                                    $manager->persist($waitingList);
                                    $manager->flush();
                                    
                                    $this->addFlash(
                                        'notice',
                                        'Inscription impossible pour le moment, car vous avez déjà une inscription 
                                        enregistrée dans les deux semaines précédentes. Vous avez été placé sur liste d\'attente.'
                                        );          
                                        return $this->redirectToRoute('planning'); 
                                   
                                }      
                            }
                                                
                        }
                        $futureSsessions = $repo_s->futureSsession($ssession);
                        if ($futureSsessions!=false){                           
                            foreach ($futureSsessions as $oneFutureSsession){
                                $futureParticipation = $repo_p->findBy(['ssessionId'=>$oneFutureSsession->getId(),'userId'=>$this->getUser()->getId()]);
                                if (!empty($futureParticipation) && $ssession->getScheduledAt()->format('Y-m-d H:i:s')>date("Y-m-d H:i:s",strtotime("+7 days"))){
                                    
                                    $waitingList = new WaitingList();
                                    $waitingList->setUserId($this->getUser()->getId());
                                    $waitingList->setSsessionId($ssession->getId());
                                    $manager->persist($waitingList);
                                    $manager->flush();

                                    $this->addFlash(
                                        'notice',
                                        'Inscription impossible pour le moment, car vous avez déjà une inscription 
                                        enregistrée dans les deux semaines suivantes. Vous avez été placé sur liste d\'attente.'
                                        );  
                                    return $this->redirectToRoute('planning');                                  
                                }      
                            }
                                                
                        }
                    }
                }
            }
            
                    $tickets = $repo_t->findBySsessionId($participation->getSsessionId());            
                    // If ssession isnt free 
                    if (!empty($tickets)){
                        // Verify if user has credits and return credit object              
                        $credit = $repo_c->verify($tickets,$participation->getUserId());
                    }
                    else
                    {              
                        $credit = "free";
                    }
                    
                    if ($credit!=false){             
                                    
                        if ($credit!="free"){
                            //Update user's credit
                            $new_amount = $credit->getAmount() - 1;
                            $credit->setAmount($new_amount);
                            $manager->flush();
                            $productId = $credit->getProductId();
                        }else{
                            $productId = 0;
                        }
                        $participation->setCreatedAt(new \DateTime());
                        $participation->setInvitedBy(0);
                        $participation->setProductId($productId);
                        $participation->setPresent(0);

                        $notification = New Notification();
                        $notification->setCreatedAt(new \DateTime());
                        $notification->setStructure(null);
                        $notification->setUserId($participation->getUserId());
                        $notification->setContent("Inscription enregistrée pour une séance de ".str_replace("_"," ",$ssession->getActivity()->getName())." le ".$ssession->getScheduledAt()->format("d/m")." à ".$ssession->getScheduledAt()->format("H:i")."  !");

                        $manager->persist($notification);
                        $manager->persist($participation);
                        $manager->flush();              

                        if ($ssession->getIdZoom()==null)
                            return $this->redirectToRoute('updateNotif',['returnRoute'=>'planning']); 
                        else
                            return $this->redirectToRoute('mailZoom',['user'=>$this->getUser()->getId(),
                            'ssession'=>$ssession->getId()]);
                    }         
                }
                else if ($form->isSubmitted() && $form->isValid() && $form->get('delete_post')->isClicked()){
                    
                    $participation = $repo_p->findOneBy(['userId'=>$participation->getUserId(),
                    'ssessionId'=>$participation->getSsessionId()]);
                    $credit = $repo_c->findOneBy(['userId'=>$participation->getUserId(),
                    'productId'=>$participation->getproductId()]);
                    if (isset($credit)){
                        $credit->setAmount($credit->getAmount()+1);
                        $manager->persist($credit);
                    }
                    
                    $waitingList = $repo_w->findOneBySsessionId($participation->getSsessionId());
                    if (isset($waitingList)){
                        $tickets = $repo_t->findBySsessionId($waitingList->getSsessionId());            
                        // If ssession isnt free 
                        if (!empty($tickets)){
                            // Verify if user has credits and return credit object              
                            $credit = $repo_c->verify($tickets,$waitingList->getUserId());
                        }
                        else
                        {              
                            $credit = "free";
                        }
                        
                        if ($credit!=false){             
                                        
                            if ($credit!="free"){
                                //Update user's credit
                                $new_amount = $credit->getAmount() - 1;
                                $credit->setAmount($new_amount);
                                $manager->flush();
                                $productId = $credit->getProductId();
                            }else{
                                $productId = 0;
                            }

                        $newParticipation = new Participation();
                        $newParticipation->setUserId($waitingList->getUserId());
                        $newParticipation->setSsessionId($waitingList->getSsessionId());
                        $newParticipation->setCreatedAt(new \DateTime());
                        $newParticipation->setInvitedBy(0);
                        $newParticipation->setProductId($productId);
                        $newParticipation->setPresent(0);
                    }
                }
                    
                    if(isset($waitingList)){
                        $manager->remove($waitingList);
                    }
                    $manager->remove($participation);
                    if(isset($newParticipation)){
                        $manager->persist($newParticipation);                        
                    }
                   
                    $ssession = $repo_s->findOneById($participation->getSsessionId());
                    $notification = New Notification();
                    $notification->setCreatedAt(new \DateTime());
                    $notification->setStructure(null);
                    $notification->setUserId($participation->getUserId());
                    $notification->setContent("Désinscription enregistrée pour la séance de 
                    ".str_replace("_"," ",$ssession->getActivity()->getName())." le ".$ssession->getScheduledAt()->format("d/m")." à 
                    ".$ssession->getScheduledAt()->format("H:i")." !");

                    $manager->persist($notification);

                    $optout = new Optout();
                    $optout->setUserId($participation->getUserId());
                    $optout->setSsessionId($ssession->getId());
                    $optout->setCreatedAt(new \DateTime());

                    $manager->persist($optout);

                    $manager->flush();
                    if (isset($newParticipation)){
                        return $this->redirectToRoute('autoSubscriptionEmail',['user'=>$newParticipation->getUserId(),'ssession'=>$newParticipation->getSsessionId()]);
                    }
                    else{
                        return $this->redirectToRoute('updateNotif',['returnRoute'=>'planning']);
                    }
                    
                }        
        
               
                if ($form_waiting->isSubmitted() && $form_waiting->isValid() && $form_waiting->get('submit_post')->isClicked()){
                    $manager->persist($waitingList);
                    $manager->flush();
                    return $this->redirectToRoute('planning');
                }
                else if ($form_waiting->isSubmitted() && $form_waiting->isValid() && $form_waiting->get('delete_post')->isClicked()){
                    $waitingList = $repo_w->findOneBy(['userId'=>$waitingList->getUserId(),
                    'ssessionId'=>$waitingList->getSsessionId()]);
                    $manager->remove($waitingList);
                    $manager->flush();                     
                    return $this->redirectToRoute('planning');
                }
            
                return $this->render('user/planning.html.twig',[
                    'credits'=>$credits,
                    'amounts'=>$amounts,
                    'productsName'=>$productsName,
                    'ssessions'=>$ssessions,
                    'participations'=>$participationsBySsession,
                    'already'=>$already,
                    'already_waiting'=>$already_waiting,
                    'invitation'=>$invitation,
                    'enoughCredit' =>$enoughCredit,                                                   
                    'form'=>$form->createView(),
                    'form_waiting'=>$form_waiting->createView(),
                    'waitingNumero'=>$waitingNumero
                    ]);
    } 

    /**
     * @Route("/invitation/{id}", name="invitation")
     */
    public function invitation(Ssession $ssession,TicketRepository $repo_t, CreditRepository $repo_c, 
    UserRepository $repo_u, ParticipationRepository $repo_p, Request $request, EntityManagerInterface $manager){
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        if ($this->session->get('structure')==null){
            return $this->redirectToRoute('home');
         }

        $tracker = new Tracker();
        $tracker->setUserId($this->getUser()->getId());
        $tracker->setPage('invitation');
        $tracker->setCreatedAt(new \DateTime());
        $manager->persist($tracker);
        $manager->flush();

       if (isset($_POST['email'])){
            
            $guest = $repo_u->findOneByEmail(htmlspecialchars(trim($_POST['email'])));
            if (!isset($guest)){
                $guest = new User();
                $guest->setEmail(htmlspecialchars(trim($_POST['email'])));
                $guest->setLastname(htmlspecialchars(trim($_POST['lastname'])));
                $guest->setFirstname(htmlspecialchars(trim($_POST['firstname'])));
                $guest->setPassword(" ");
                $guest->setMobile(" ");
                $guest->setCreatedAt(new \DateTime());
                $guest->setRappel(0);
                $guest->setAadmin(0);
                $guest->setPlanningHebdo(0);
                $guest->setEmailVerify(0); 

                $manager->persist($guest);
                $manager->flush();  
            }

            $participation = $repo_p->findBy(['userId'=>$guest->getId(),'ssessionId'=>$ssession->getId()]);
            if (!empty($participation)){
                $this->addFlash(
                    'notice',
                    'Cet utilisateur est déjà inscrit pour cette séance.'
                    );  
                return $this->render('user/invitation.html.twig',['ssession'=>$ssession]);
            }

            $participation = new Participation();
            $tickets = $repo_t->findBySsessionId($ssession->getId());            
            // If ssession isnt free 
            if (!empty($tickets)){
                // Verify if user has credits and return credit object              
                $credit = $repo_c->verify($tickets,$this->getUser()->getId());
            }
            else
            {              
                $credit = "free";
            }
            
            if ($credit!=false){             
                            
                if ($credit!="free"){
                    //Update user's credit
                    $new_amount = $credit->getAmount() - 1;
                    $credit->setAmount($new_amount);
                    $manager->flush();
                    $productId = $credit->getProductId();
                }else{
                    $productId = 0;
                }
                
                $participation->setUserId($guest->getId());
                $participation->setSsessionId($ssession->getId());
                $participation->setCreatedAt(new \DateTime());
                $participation->setInvitedBy($this->getUser()->getId());
                $participation->setProductId($productId);
                $participation->setPresent(0);
                $manager->persist($participation);
                
                $notification = New Notification();
                $notification->setCreatedAt(new \DateTime());
                $notification->setStructure(null);
                $notification->setUserId($this->getUser()->getId());
                $notification->setContent("Invitation envoyée à ".$guest->getEmail()." !");
                $manager->persist($notification);

                $manager->flush();  
                return $this->redirectToRoute('invitationMail',['user'=>$this->getUser()->getId(),'guest'=>
                $guest->getId(),'ssession'=>$ssession->getId()]);  
            }         
       }
        return $this->render('user/invitation.html.twig',['ssession'=>$ssession]);
    }

    /**
     * @Route("/profile", name="profile")
     */
    public function profile(Request $request, EntityManagerInterface $manager){
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        if ($this->session->get('structure')==null){
            return $this->redirectToRoute('home');
         }
        $user = $this->getUser();

        $tracker = new Tracker();
        $tracker->setUserId($user->getId());
        $tracker->setPage('profile');
        $tracker->setCreatedAt(new \DateTime());
        $manager->persist($tracker);
        $manager->flush();

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
            $notification->setStructure(null);
            $notification->setUserId($user->getId());
            $notification->setContent("Profil modifié !");

            $manager->persist($notification);
            $manager->persist($user);
            $manager->flush(); 
            $this->addFlash('notice','Profil modifié !');
            return $this->redirectToRoute('updateNotif',['returnRoute'=>'profile']);  
        }
        return $this->render('user/profile.html.twig',['form'=>$form->createView()]);
    }

     /**
     * @Route("/modifyPassword", name="modifyPassword")
     */
    public function modifyPassword(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder){
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        if ($this->session->get('structure')==null){
            return $this->redirectToRoute('home');
         }
        $tracker = new Tracker();
        $tracker->setUserId($this->getUser()->getId());
        $tracker->setPage('modify_password');
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
       
        return $this->render('user/modifyPassword.html.twig');
    }

    /**
     * @Route("/purchase", name="purchase")
     */
    public function purchase(CatalogRepository $repo_c, ProductRepository $repo_p, 
    OorderRepository $repo_o, ParticipationRepository $repo_par,
    Request $request, EntityManagerInterface $manager){
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED'); 
        if ($this->session->get('structure')==null){
            return $this->redirectToRoute('home');
         }
        $tracker = new Tracker();
        $tracker->setUserId($this->getUser()->getId());
        $tracker->setPage('purchase');
        $tracker->setCreatedAt(new \DateTime());
        $manager->persist($tracker);
        $manager->flush();

        $structure = new Structure();
        $structure = $this->session->get('structure');      
        $catalogs = $repo_c->findByStructureId($structure->getId());
        $products = [];
        foreach($catalogs as $catalog){
            $products[] = $repo_p->findOneById($catalog->getProduct());
        } 

        //Sécance découverte en ligne unique

        $deja_utilise = $repo_par->findBy(['userId'=>$this->getUser()->getId(),'productId'=>39]);
        
        if (!empty($deja_utilise)){
            $i = 0;
            foreach ($products as $oneProduct){
                if($oneProduct->getId()==39){
                    unset($products[$i]);
                }
                $i++;
            }
        }
     
        //Régle des Régle des 6 massages à 5 euros RIVP
        if ($structure->getId()==13){
            $nbr_massages = $repo_o->rivp_nbr_massages($this->getUser()->getId());
         
                   $i = 0;
                foreach ($products as $oneProduct){ 
                   
                    if ($nbr_massages<5 && $oneProduct->getId()==37){
                        unset($products[$i]);
                    }
                    elseif($nbr_massages==5 && ($oneProduct->getId()==37 || $oneProduct->getId()==33)){
                        unset($products[$i]);
                    }
                    elseif($nbr_massages>5 && ($oneProduct->getId()==32 || $oneProduct->getId()==33)){
                        unset($products[$i]);
                    }
                    $i++;
                }
               
            
        }
 
       
        return $this->render('user/purchase.html.twig',['products'=>$products]);
    }

     /**
     * @Route("/payment", name="payment")
     */
    public function payment(EntityManagerInterface $manager, ProductRepository $repo){
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED'); 
        if ($this->session->get('structure')==null){
            return $this->redirectToRoute('home');
         }
        if (isset($_POST['product'])){
            $product = $repo->findOneById(htmlspecialchars(trim($_POST['product'])));
            $oorder = new Oorder();
            $oorder->setUserId($this->getUser()->getId());
            $oorder->setProduct($product);
            $oorder->setCreatedAt(new \DateTime());
            $oorder->setStatus("");
            $oorder->setMode("");

            $manager->persist($oorder);
            $manager->flush();

           

            if (htmlspecialchars(trim($_POST['plateforme']))=="payplug"){
                require_once("payplug-php-3.0.0/lib/init.php");
                \Payplug\Payplug::setSecretKey('sk_live_3k5R6QsTLK63yWiqrwTxTO');
                $email = $this->getUser()->getEmail();
                $amount = $product->getPrice();
                $customer_id = $this->getUser()->getId();

                $payment = \Payplug\Payment::create(array(
                'amount'           => $amount * 100,
                'currency'         => 'EUR',
                'billing'  => array(
                'title'        => 'mr',
                'first_name'   => $this->getUser()->getFirstname(),
                'last_name'    => $this->getUser()->getLastname(),
                'email'        => $email,
                'address1'      => '221B Baker Street',
		        'postcode'      => 'NW16XE',
		        'city'          => 'London',
		        'country'       => 'GB',
		        'language'      => 'fr',
                ),
                'shipping'  => array(
                'title'         => 'mr',
                'first_name'    => $this->getUser()->getFirstname(),
                'last_name'     => $this->getUser()->getLastname(),
                'email'         => $email,
                'address1'      => '221B Baker Street',
		        'postcode'      => 'NW16XE',
		        'city'          => 'London',
		        'country'       => 'GB',
		        'language'      => 'fr',
                'delivery_type' => 'BILLING'
                ),
                'hosted_payment'   => array(
                'return_url'     => 'https://www.relaxeo.net/paymentReturn?cm='.$oorder->getId(),
                'cancel_url'     => 'https://www.relaxeo.net/purchase'
                ),
                'notification_url' => 'https://www.relaxeo.net/',
                'metadata'         => array(
                'customer_id'    => $customer_id
                )
                ));

                $payment_url = $payment->hosted_payment->payment_url;
                $payment_id = $payment->id;		
                return $this->redirect($payment_url);
            }
            else{
                echo $product->getPaypal().'<br /><br /><p style="text-align:center;color:rgb(44,155,191);font-size:40px;">Veuillez patienter, vous allez être redirigé dans quelques secondes vers Paypal.</p>';	
                echo '<script>document.getElementById(\'paypal\').style.display = "none"</script>';		
                echo '<script>document.getElementById(\'custom\').value='.$oorder->getId().'</script>';
                echo '<script>document.getElementById(\'paypal\').submit()</script>';
                return new Response();
            }
        }else{
            return $this->redirectToRoute('purchase');
        }
       
        
    }

    /**
     * @Route("/paymentReturn", name="paymentReturn")
     */

    public function paymentReturn(OorderRepository $repo_o, ProductRepository $repo_p, CreditRepository $repo_c,
    EntityManagerInterface $manager){
       //On enlève cette partie car Paypal casse la session
        // $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
       // if ($this->session->get('structure')==null){
       //    return $this->redirectToRoute('home');
       //  }

        if (isset($_REQUEST['cm'])){
            $oorder = $repo_o->findOneById($_REQUEST['cm']);
            if ($oorder->getStatus()==""){
                $product = $repo_p->findOneById($oorder->getProduct()->getId());
                $credit = $repo_c->findOneBy(['userId'=>$oorder->getUserId(),'productId'=>$product->getId()]);
                if (isset($credit)){
                    $credit->setAmount($credit->getAmount()+$product->getCredits());                    
                }
                else{
                    $credit = new Credit();
                    $credit->setUserId($oorder->getUserId());
                    $credit->setProductId($product->getId());
                    $credit->setAmount($product->getCredits());
                    $credit->setStructureId(0);
                }
                $manager->persist($credit);
                $oorder->setStatus("ok");
                $manager->flush();
                $this->addFlash('notice','Merci pour votre paiement ! Pour vous inscrire à une séance, sélectionnez une date dans le planning.'); 
                
                      

                return $this->redirectToRoute('planning');             
            }
        }
        return $this->redirectToRoute('planning');
     }

    /**
     * @Route("/parrainage", name="parrainage")
     */
    public function parrainage(EntityManagerInterface $manager){
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED'); 
        if ($this->session->get('structure')==null){
            return $this->redirectToRoute('home');
         }

         $user = $this->getUser();

         $tracker = new Tracker();
         $tracker->setUserId($user->getId());
         $tracker->setPage('parrainage');
         $tracker->setCreatedAt(new \DateTime());
         $manager->persist($tracker);
         $manager->flush();
       
        return $this->redirectToRoute('planning');
    }

     /**
     * @Route("/preferences", name="preferences")
     */
    public function preferences(EmailNotificationRepository $repo_en, EntityManagerInterface $manager){
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        if ($this->session->get('structure')==null){
            return $this->redirectToRoute('home');
         }
        $tracker = new Tracker();
        $tracker->setUserId($this->getUser()->getId());
        $tracker->setPage('preferences');
        $tracker->setCreatedAt(new \DateTime());
        $manager->persist($tracker);
        $manager->flush();    
      
        
        if (isset($_POST['valid'])){            
            
            if (isset($_POST['planningHebdo'])){                
                $this->getUser()->setPlanningHebdo(1);
            }else{
                $this->getUser()->setPlanningHebdo(0);
            }
            
            if (isset($_POST['rappel'])){                
                $this->getUser()->setRappel(1);
            }else{
                $this->getUser()->setRappel(0);
            }
            $manager->persist($this->getUser());
            $manager->flush();            
          $this->addFlash('notice','Préférences enregistrées');
        }    
       
        $eNotifs = $repo_en->findByUserId($this->getUser()->getId());
        $rappel = $this->getUser()->getRappel();
        $planningHebdo = $this->getUser()->getPlanningHebdo();

        return $this->render('user/preferences.html.twig',['eNotifs'=>$eNotifs,'rappel'=>$rappel,'planningHebdo'=>$planningHebdo]);        
    }

     /**
     * @Route("/contact", name="contact")
     */
    public function contact(EntityManagerInterface $manager){
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED'); 
        if ($this->session->get('structure')==null){
            return $this->redirectToRoute('home');
         }
        $tracker = new Tracker();
        $tracker->setUserId($this->getUser()->getId());
        $tracker->setPage('contact');
        $tracker->setCreatedAt(new \DateTime());
        $manager->persist($tracker);
        $manager->flush();
       
        return $this->render('user/contact.html.twig');
    }

     /**
     * @Route("/cgv", name="cgv")
     */
    public function cgv(){
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED'); 
        if ($this->session->get('structure')==null){
            return $this->redirectToRoute('home');
         }
       
        return $this->render('user/cgv.html.twig');
    }

    /**
     * @Route("/mentions", name="mentions")
     */
    public function mentions(){
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED'); 
        if ($this->session->get('structure')==null){
            return $this->redirectToRoute('home');
         }
       
        return $this->render('user/mentions.html.twig');
    }

    /**
     * @Route("/notif", name="notifications")
     */
    public function notifications(){
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        if ($this->session->get('structure')==null){
            return $this->redirectToRoute('home');
         }
       
        return $this->render('user/notifications.html.twig');
    }

    /**
     * @Route("/markNotifAsView", name="markNotifAsView")
     */
    public function markNotifAsView(Request $request, EntityManagerInterface $manager, NotificationRepository $repo_n,
    NotificationViewRepository $repo_nv){
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $notifications = $this->session->get('notifications');
        foreach ($notifications as $oneNotification){
            $notificationView = $repo_nv->findOneBy(['notificationId'=>$oneNotification->getId(),'userId'=>$this->getUser()->getId()]);
            if (!isset($notificationView)){
                $notificationView = new NotificationView();
                $notificationView->setNotificationId($oneNotification->getId());
                $notificationView->setUserId($this->getUser()->getId());
                $manager->persist($notificationView);
                $manager->flush();
            }
           
        }
                     
                return $this->redirectToRoute('home'); 
      
    }  
     
}
