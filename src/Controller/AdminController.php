<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\Ssession;
use App\Entity\Event;
use App\Entity\Notification;
use App\Entity\Optout;
use App\Entity\Participation;
use App\Entity\User;
use App\Entity\Structure;
use App\Entity\Location;
use App\Entity\Product;
use App\Entity\Oorder;
use App\Entity\Catalog;
use App\Entity\Refund;
use App\Entity\FreeCredit;
use App\Repository\SsessionRepository;
use App\Repository\EventRepository;
use App\Repository\ParticipationRepository;
use App\Repository\ProductRepository;
use App\Repository\CatalogRepository;
use App\Repository\StructureRepository;
use App\Repository\TicketRepository;
use App\Repository\UserRepository;
use App\Repository\OptoutRepository;
use App\Repository\WaitingListRepository;
use App\Repository\CreditRepository;
use App\Repository\LocationRepository;
use App\Repository\OorderRepository;
use App\Repository\RedlistRepository;
use App\Repository\RefundRepository;
use App\Repository\TrackerRepository;
use App\Repository\NotificationRepository;
use App\Repository\NotificationViewRepository;
use App\Repository\FreeCreditRepository;
use App\Repository\SponsorshipRepository;
use App\Form\SsessionType;
use App\Form\EventType;
use App\Form\UserType;
use App\Form\StructureType;
use App\Form\CoachType;
use App\Form\ProductType;
use App\Form\OorderType;
use App\Form\CatalogType;
use App\Form\RefundType;
use App\Form\NotificationType;
use App\Form\FreeCreditType;
use App\Service\TicketManager;
use App\Service\ParticipationManager;
use App\Service\UserManager;
use App\Service\CreditManager;
use App\Service\NotificationManager;
use App\Service\AnalyticsManager;

/**
 * @Route("/admin")
 */
class AdminController extends AbstractController
{
    private $repo_u;
    private $repo_ss;
    private $repo_pa;
    private $repo_pr;
    private $manager;


    public function __construct(UserRepository $repo_u, SsessionRepository $repo_ss, ParticipationRepository $repo_pa, 
    ProductRepository $repo_pr, EntityManagerInterface $manager){
        $this->repo_u = $repo_u;
        $this->repo_ss = $repo_ss;
        $this->repo_pa = $repo_pa;
        $this->repo_pr = $repo_pr;
        $this->manager = $manager;       
    }
   
    /**
     * @Route("/dashboard", name="adminDashboard")
     */
    public function dashboard(OptoutRepository $repo_o, TrackerRepository $repo_t, SponsorshipRepository $repo_sp){    
        
        $now = date("Y-m-d H:i:s");
        $tommorow = date("Y-m-d H:i:s",strtotime("+1 day")); 
        $todaySsessions = $this->repo_ss->findSsessionsByDate($now);
        $tommorowSsessions = $this->repo_ss->findSsessionsByDate($tommorow);
        $today_nb_ssessions = count($todaySsessions);
        $tommorow_nb_ssessions = count($tommorowSsessions);
    
        
        $participations = $this->repo_pa->findAll();
        $users = $this->repo_u->findAll();
        $ssessions = $this->repo_ss->findAll();
        $optouts = $repo_o->findAll();
        $participationsStructures = [];
        $participationsActivity = [];
        $participationsUsers = [];
        $participationsScheduledAt = [];
        $optoutsSsessionScheduledAt = [];
        $optoutsStructures = [];
        $optoutsActivity = [];
        $optoutsUsers = [];    
        $usersList = [];
        $ssessionsList = [];
        $trackersUser = [];

        foreach($users as $oneUser){ 
            $usersList[$oneUser->getId()] = $oneUser;            
        }
        foreach($ssessions as $oneSsession){ 
            $ssessionsList[$oneSsession->getId()] = $oneSsession;           
        }
       

        foreach($participations as $oneParticipation){
            if (isset($ssessionsList[$oneParticipation->getSsessionId()])){
                $ssession = $ssessionsList[$oneParticipation->getSsessionId()];
            }
       
         
            if (isset($ssession)){
                $participationsStructures[$oneParticipation->getId()] = $ssession->getStructure()->getName(); 
                $participationsScheduledAt[$oneParticipation->getId()] = $ssession->getScheduledAt();     
            }     
                
            $participationsActivity[$oneParticipation->getId()] = isset($ssession) ? $ssession->getActivity() : "inconnu";

     
            if (isset($usersList[$oneParticipation->getUserId()])){
                $participationsUsers[$oneParticipation->getId()] = $usersList[$oneParticipation->getUserId()];
            }
            
        }   
        
        foreach($optouts as $oneOptout){
            if (isset($ssessionsList[$oneOptout->getSsessionId()])){
                $ssession = $ssessionsList[$oneOptout->getSsessionId()];
            }        
            
            $optoutsStructures[$oneOptout->getId()] = isset($ssession) ? $ssession->getStructure()->getName() : "inconnu";

            $optoutsActivity[$oneOptout->getId()] = isset($ssession) ? $ssession->getActivity() : "inconnu";
     
            if (isset($usersList[$oneOptout->getUserId()])){
                $optoutsUsers[$oneOptout->getId()] = $usersList[$oneOptout->getUserId()];
            }
            
            $optoutsSsessionScheduledAt[$oneOptout->getId()] = isset($ssession) ? $ssession->getScheduledAt() : "inconnu";
        }        
        
        $this->repo_t = $repo_t;  

    
        $trackers = $this->repo_t->lastTrackers(20);       
        

        foreach ($trackers as $oneTracker){
        $trackersUser[$oneTracker->getId()] = $this->repo_u->findOneById($oneTracker->getUserId());
        }  
        
      
          
        $sponsorships = $repo_sp->findAll();
        $godsonsUsers = [];
        $godsonsParticipations = [];
        foreach ($sponsorships as $oneSponsorship){
            $godson = $this->repo_u->findOneByEmail($oneSponsorship->getGodson());
            if ($godson !== null){
                $godsonsUsers[$oneSponsorship->getId()] = "Oui";
                $godsonsParticipations[$oneSponsorship->getId()] = count($this->repo_pa->findBy(['userId'=>$godson->getId(),'present'=>1]));
            }else{
                $godsonsUsers[$oneSponsorship->getId()] = "Non";
                $godsonsParticipations[$oneSponsorship->getId()] = 0;
            }
        }
        

        return $this->render('admin/dashboard.html.twig',[
        'today_nb_ssessions'=>$today_nb_ssessions,
        'tommorow_nb_ssessions'=>$tommorow_nb_ssessions,
        'participations'=>$participations,
        'optouts'=>$optouts,
        'users'=>$users,        
        'participationsStructures'=>$participationsStructures,
        'participationsActivity'=>$participationsActivity,
        'participationsUsers'=>$participationsUsers,
        'participationsScheduledAt'=>$participationsScheduledAt,
        'optoutsSsessionScheduledAt'=>$optoutsSsessionScheduledAt,
        'optoutsStructures'=>$optoutsStructures,
        'optoutsActivity'=>$optoutsActivity,
        'optoutsUsers'=>$optoutsUsers,
        'trackers'=>$trackers,
        'trackersUser'=>$trackersUser,
        'sponsorships'=>$sponsorships,
        'godsonsUsers'=>$godsonsUsers,
        'godsonsParticipations'=>$godsonsParticipations
        ]);
    }

    /**
     * @Route("/graphs", name="adminGraphs")
    */

    public function graphs(OorderRepository $repo_o, AnalyticsManager $analyticsManager){

        $data_p = [];
        $data_u = [];
        $data_o = [];
        $labels = [];
        $frenchMonth = ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];
        $actualYear = intval(date("Y"));
        $actualMonth = intval(date("n"));

        for ($j = 2017; $j<=$actualYear ; $j++){
                
                for ($i = 1; $i<=12 ; $i++){
                    if ($j==2017 && $i==1)
                        $i = 8;
                    if($j==$actualYear && $i>$actualMonth)
                        break;

                    $participations = $this->repo_pa->findByMonth($i,$j);
                    $data_p[] = count($participations);

                    $users = $this->repo_u->findByMonth($i,$j);
                    $data_u[] = count($users);

                    $oorders = $repo_o->findByMonth($i,$j);
                    $total = 0;

                    $ssessions = $this->repo_ss->findByMonth($i,$j);

                    foreach ($oorders as $oneOorder){
                        $product = $this->repo_pr->findOneById($oneOorder->getProduct());
                        if(!empty($product)){
                            $total = $total + $product->getPrice();
                        }              
                    }

                    foreach($ssessions as $oneSsession){
                        if ($oneSsession->getGratuite()==0){
                            if ($oneSsession->getStructure()->getCode()=="rivp75" 
                            && $oneSsession->getActivity()->getName()=="Yoga_sur_chaise"){
                                $total = $total + 108;
                            }
                            if ($oneSsession->getStructure()->getCode()=="rivp75" 
                            && $oneSsession->getActivity()->getName()=="Massage"){
                                $total = $total + 13.5;
                            }
                            if ($oneSsession->getStructure()->getCode()=="UDAF93" 
                            && $oneSsession->getActivity()->getName()=="Yoga"){
                                $total = $total + 96;
                            }
                        }
                        

                    }

                    $data_o[] = $total;
                    $month = $frenchMonth[intval(date("n", mktime(0,0,0,$i,1,$j)))-1];
                    
                    $labels[] = $month.' '.$j;
                }
        }
        
        $analyse = $analyticsManager->initializeAnalytics();
        
        $startDate = date("2020-06-15");
        $endDate = date("2020-06-21");
        $data_a = [];
        $data_a2 = [];
        $labels_a = [];
      
        
        while ($startDate<date("Y-m-d")){
            $response = $analyticsManager->getReport($analyse,$startDate,$endDate,"229678391");
            $response2 = $analyticsManager->getReport($analyse,$startDate,$endDate,"221161068");
      
            if ($response!="")
                $data_a[] = $analyticsManager->printResults($response);
            else
                $data_a[] = 0;
            $data_a2[] = $analyticsManager->printResults($response2);
            $labels_a[] = date("d/m/y",strtotime($startDate)).' - '.date("d/m/y",strtotime($endDate));
            $startDate = date("Y-m-d",strtotime($startDate." +7 day "));
            $endDate = date("Y-m-d",strtotime($endDate." +7 day"));         
        }
        
        return $this->render('admin/graphs.html.twig',[
            'data_p'=>$data_p, 
            'data_u'=>$data_u, 
            'data_o'=>$data_o,
            'data_a'=>$data_a,
            'data_a2'=>$data_a2,
            'labels'=>$labels,
            'labels_a'=>$labels_a
            ]);
    }  

     /**
     * @Route("/planning", name="adminPlanning")
     */
    public function planning(EventRepository $repo_e){      
        $ssessions = $this->repo_ss->findAll();

        //List of Participations
        $participationsBySsession = [];       
        foreach ($ssessions as $oneSsession){            
            $participations = $this->repo_pa->findBySsessionId($oneSsession->getId());               
            $participationsBySsession[$oneSsession->getId()] = count($participations);
        }
        $events = $repo_e->findAll();
        return $this->render('admin/planning.html.twig',['ssessions'=>$ssessions,'events'=>$events,'participations'=>$participationsBySsession]);
    }

    /**
     * @Route("/addSession", name="addSsession")
     */
    public function addSsession(CatalogRepository $repo_c, TicketManager $ticketManager, Request $request){             
   
            $ssession = new Ssession();
                 
            $form = $this->createForm(SsessionType::class ,$ssession);
            
            $form->handleRequest($request);             
        
            if ($form->isSubmitted() && $form->isValid()){         
              
                if (isset($_POST['off'])){
                    $ssession->setOff(1);
                }
                $this->manager->persist($ssession);
                $this->manager->flush();

                $catalogs = $repo_c->findByStructureId($ssession->getStructure()->getId());
                foreach ($catalogs as $oneCatalog){
                    if (isset($_POST[$oneCatalog->getProduct()->getId()])){
                       
                        $ticketManager->add($ssession->getId(),$oneCatalog->getProduct()->getId());   
                    
                    }
                }
                $catalogs = $repo_c->findAll();
                $products = $this->repo_pr->findAll(); 
                $this->addFlash('notice','Séance créée !');
                return $this->render('admin/addSsession.html.twig',['form'=>$form->createView(),
                'catalogs'=>$catalogs,'products'=>$products]);
            } 
           
            $catalogs = $repo_c->findAll();
            $products = $this->repo_pr->findAll(); 
          
            return $this->render('admin/addSsession.html.twig',['form'=>$form->createView(),'catalogs'=>$catalogs,
            'products'=>$products]);
    }

     /**
     * @Route("/modifySsession/{id}", name="modifySsession")
     */
    public function modifySsession(Ssession $ssession, CatalogRepository $repo_c, TicketRepository $repo_t, 
    WaitingListRepository $repo_w, CreditRepository $repo_cr, RedlistRepository $repo_red, TicketManager $ticketManager, 
    ParticipationManager $participationManager, CreditManager $creditManager, Request $request){       
        
        $users = $this->repo_u->findBy(['structure'=>$ssession->getStructure()],['lastname'=>'ASC']);

        if(isset($_POST['addParticipation'])){
            $participation = $this->repo_pa->findBy(['userId'=>$_POST['addParticipation'],'ssessionId'=>$ssession->getId()]);
            if (!empty($participation)){
                $this->addFlash(
                    'notice',
                    'Cet utilisateur est déjà inscrit pour cette séance.'
                    );  
                return $this->redirectToRoute('modifySsession',['id'=>$ssession->getId()]);
            }

          
            $tickets = $repo_t->findBySsessionId($ssession->getId());            
            // If ssession isnt free 
            if (!empty($tickets)){
                // Verify if user has credits and return credit object              
                $credit = $repo_cr->verify($tickets,$_POST['addParticipation']);
            }
            else
            {              
                $credit = "free";
            }
            
            if ($credit!=false){             
                            
                if ($credit!="free"){
                    
                    $creditManager->setAmount($credit,-1);

                    $productId = $credit->getProductId();
                }else{
                    $productId = 0;
                }
                
                $participationManager->add($_POST['addParticipation'],$ssession->getId(),
                new \DateTime(),0,$productId,0);        

             
                $this->addFlash(
                    'notice',
                    'Inscription enregistrée'
                    );    
        }else{
            $this->addFlash(
                'notice',
                'Cet utilisateur n\'a pas assez de crédit'
                );  
            return $this->redirectToRoute('modifySsession',['id'=>$ssession->getId()]);
        }
    }    
        $participations = $this->repo_pa->findBySsessionId($ssession->getId());    
        if (isset($_POST['presents'])){
            
            $participationManager->setPresent($participations,$_POST);
           
            $this->addFlash('notice','Présents enregistrés'); 
        }
        
        $participations = $this->repo_pa->findBySsessionId($ssession->getId());
        $listParticipants = [];
        $listInvitedBy = [];
        $redlist = [];
        foreach ($participations as $oneParticipation){
            $participant = $this->repo_u->findOneById($oneParticipation->getUserId());
            $invitedBy = $this->repo_u->findOneById($oneParticipation->getInvitedBy());
            $listInvitedBy[$oneParticipation->getId()] = $invitedBy;
            $listParticipants[$oneParticipation->getId()] = $participant;
            $redlist[$oneParticipation->getId()] = $repo_red->findOneByEmail($participant->getEmail());
        }

        $waitingLists = $repo_w->findBySsessionId($ssession->getId());
       
        $listWaitings = [];
        foreach ($waitingLists as $oneWaitingList){
            $listWaiting = $this->repo_u->findOneById($oneWaitingList->getUserId());
            $listWaitings[$oneWaitingList->getId()] = $listWaiting;
        }
        
                
        $form = $this->createForm(SsessionType::class ,$ssession);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid() && $form->get('submit_post')->isClicked()){
            if (isset($_POST['off'])){
                $ssession->setOff(1);
            }else{
                $ssession->setOff(0);
            }
            $this->manager->persist($ssession);
            $this->manager->flush();

                $catalogs = $repo_c->findByStructureId($ssession->getStructure()->getId());
                foreach ($catalogs as $oneCatalog){
                    if (isset($_POST[$oneCatalog->getProduct()->getId()])){
                       
                            $tickets = $repo_t->findBy(['productId'=>$oneCatalog->getProduct()->getId(),'ssessionId'=>$ssession->getId()]);
                            if(empty($tickets)){

                                $ticketManager->add($ssession->getId(),$oneCatalog->getProduct()->getId());    
                                
                            }
                                           
                    }
                    else{
                        $tickets = $repo_t->findBy(['productId'=>$oneCatalog->getProduct()->getId(),'ssessionId'=>$ssession->getId()]);                   
                        $ticketManager->removeList($tickets);                      
                    }
                }
                $tickets = $repo_t->findBySsessionId($ssession->getId());

                $catalogs = $repo_c->findAll();
                $products = $this->repo_pr->findAll();
                
                $this->addFlash('notice','Séance modifiée !');
                 return $this->render('admin/modifySsession.html.twig',[
                 'users'=>$users,
                 'ssession'=>$ssession,
                 'participations'=>$participations,
                 'listInvitedBy'=>$listInvitedBy,
                 'listParticipants'=>$listParticipants,
                 'waitingLists'=>$waitingLists,
                 'listWaitings'=>$listWaitings,                 
                 'form'=>$form->createView(),
                 'catalogs'=>$catalogs,'products'=>$products,'tickets'=>$tickets,'redlist'=>$redlist]);
        }
        elseif ($form->isSubmitted() && $form->isValid() && $form->get('delete_post')->isClicked()){
           
            $tickets = $repo_t->findBySsessionId($ssession->getId());            
            $ticketManager->removeList($tickets);
           
            $this->manager->remove($ssession);
            $this->manager->flush();
            $this->addFlash('notice','Séance supprimée !');
            return $this->redirectToRoute('adminPlanning');
        }
        $tickets = $repo_t->findBySsessionId($ssession->getId());

        $catalogs = $repo_c->findAll();
        $products = $this->repo_pr->findAll(); 
        return $this->render('admin/modifySsession.html.twig',[ 'ssession'=>$ssession,
        'users'=>$users,
        'participations'=>$participations,
        'listInvitedBy'=>$listInvitedBy,
        'listParticipants'=>$listParticipants,
        'waitingLists'=>$waitingLists,
        'listWaitings'=>$listWaitings,
        'form'=>$form->createView(),
        'catalogs'=>$catalogs,'products'=>$products,'tickets'=>$tickets,'redlist'=>$redlist]);
    }

    /**
     * @Route("/deleteParticipation/{participation}", name="deleteParticipation")
     */
    public function deleteParticipation(Participation $participation, CreditRepository $repo_c,WaitingListRepository $repo_w, 
    TicketRepository $repo_t, ParticipationManager $participationManager, CreditManager $creditManager, Request $request){

        $credit = $repo_c->findOneBy(['userId'=>$participation->getUserId(),
        'productId'=>$participation->getproductId()]);
        if (isset($credit)){
            $creditManager->setAmount($credit,+1);
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
                    $creditManager->setAmount($credit,-1);

                    $productId = $credit->getProductId();
                }else{
                    $productId = 0;
                }
            
            $participationManager->add($waitingList->getUserId(),$waitingList->getSsessionId(),
            new \DateTime(),0,$productId,0);         
            
        }
    }
        
        if(isset($waitingList)){
            $this->manager->remove($waitingList);
        }
        $this->manager->remove($participation);
                        
        $ssession = $this->repo_ss->findOneById($participation->getSsessionId());
        $optout = new Optout();
        $optout->setUserId($participation->getUserId());
        $optout->setSsessionId($ssession->getId());
        $optout->setCreatedAt(new \DateTime());

        $this->manager->persist($optout);

     
        $this->manager->remove($participation);
        $this->manager->flush();
        $this->addFlash(
            'notice',
            'Participation supprimée'
            ); 
        return $this->redirectToRoute("modifySsession",['id'=>$ssession->getId()]);
    }

    /**
     * @Route("/addEvent", name="addEvent")
     */

     public function addEvent(Request $request){
        $event = new Event();
        $form = $this->createForm(EventType::class ,$event);
            
        $form->handleRequest($request);             
    
        if ($form->isSubmitted() && $form->isValid()){ 
            $event->setCreatedBy($this->getUser()->getId());           
            $this->manager->persist($event);
            $this->manager->flush();
            $this->addFlash('notice','Evénement créé');
        } 
        return $this->render('admin/addEvent.html.twig',['form'=>$form->createView()]);
     }

     /**
     * @Route("/modifyEvent/{id}", name="modifyEvent")
     */

    public function modifyEvent(Event $event, Request $request){
       
        $form = $this->createForm(EventType::class ,$event);            
        
        $form->handleRequest($request);             
    
        if ($form->isSubmitted() && $form->isValid() && $form->get('submit_post')->isClicked()){ 
           
            $this->manager->persist($event);
            $this->manager->flush();
            $this->addFlash('notice','Evénement modifié');
        } 
        elseif($form->isSubmitted() && $form->isValid() && $form->get('delete_post')->isClicked()){
            $this->manager->remove($event);
            $this->manager->flush();
            $this->addFlash('notice','Evénement supprimé');
            return $this->redirectToRoute('adminPlanning');
        }
        return $this->render('admin/modifyEvent.html.twig',['form'=>$form->createView()]);
     }

     /**
      * @Route("/users", name="users")
      */

      public function users(){
        $users = $this->repo_u->findAll();
        $structures = [];
        foreach($users as $oneUser){                       
            $structures[$oneUser->getId()] = $oneUser->getStructure();       
        }
        return $this->render('admin/users.html.twig',['users'=>$users,'structures'=>$structures]);
      }

      /**
      * @Route("/user/{id}", name="user")
      */

      public function user(User $user, CreditRepository $repo_c, OorderRepository $repo_o, RefundRepository $repo_r,
      FreeCreditRepository $repo_fc, CreditManager $creditManager, Request $request){
        
        $oorder = new Oorder();
        $form_oorder = $this->createForm(OorderType::class,$oorder);
        $form_oorder->handleRequest($request); 
        if ($form_oorder->isSubmitted() && $form_oorder->isValid() && $form_oorder->get('submit_post')->isClicked()){
            $oorder->setCreatedAt(new \DateTime());
            $oorder->setUserId($user->getId());
            $oorder->setStatus("ok");
            $this->manager->persist($oorder);
            $this->manager->flush();
            $product = $this->repo_pr->findOneById($oorder->getProduct()->getId());
                $credit = $repo_c->findOneBy(['userId'=>$oorder->getUserId(),'productId'=>$product->getId()]);
                if (isset($credit)){
                    $creditManager->setAmount($credit,$product->getCredits());                                       
                }
                else{
                    $creditManager->add($product->getId(),$user->getId(),$product->getCredits(),
                    $user->getStructure()->getId());                    
                }
           
            $this->addFlash('notice','Commande enregistrée !');
            return $this->redirectToRoute('user',['id'=>$user->getId()]);
        }

        $freeCredit = new FreeCredit();
        $form_freeCredit = $this->createForm(FreeCreditType::class,$freeCredit);
        $form_freeCredit->handleRequest($request);
        if ($form_freeCredit->isSubmitted() && $form_freeCredit->isValid()){
            $freeCredit->setUser($user);
            $this->manager->persist($freeCredit);
            $this->manager->flush();

            $product = $this->repo_pr->findOneById($freeCredit->getProduct()->getId());
            $credit = $repo_c->findOneBy(['userId'=>$freeCredit->getUser()->getId(),'productId'=>$product->getId()]);
            if (isset($credit)){
                $creditManager->setAmount($credit,($product->getCredits()*$freeCredit->getAmount()));                                       
            }
            else{
                $creditManager->add($product->getId(),$user->getId(),($product->getCredits()*$freeCredit->getAmount()),
                $user->getStructure()->getId());                    
            }

            $this->addFlash('notice','Crédit ajouté !');
            return $this->redirectToRoute('user',['id'=>$user->getId()]);
        }

        $refund = new Refund();
        $form_refund = $this->createForm(RefundType::class,$refund);
        $form_refund->handleRequest($request);
        if ($form_refund->isSubmitted() && $form_refund->isValid() && $form_refund->get('submit_post')->isClicked()){
            $refund->setUserId($user->getId());
           
            $product = $this->repo_pr->findOneById($refund->getProduct()->getId());
                $credit = $repo_c->findOneBy(['userId'=>$refund->getUserId(),'productId'=>$product->getId()]);
                if (isset($credit)){
                    $creditManager->setAmount($credit,(-$refund->getCredits()));
                    $this->manager->persist($refund);
                    $this->manager->flush();                                     
                }
                else{
                   $this->addFlash('notice','Remboursement imossible, crédit insuffisant.');
                   return $this->redirectToRoute('user',['id'=>$user->getId()]);
                }
          
            $this->addFlash('notice','Remboursement enregistré !');
            return $this->redirectToRoute('user',['id'=>$user->getId()]);           
        }

        $credits = $repo_c->findByUserId($user->getId());
       
        $productId = 0;
        $amounts = [];        
        $productsName = [];
        foreach ($credits as $oneCredit){
            if ($oneCredit->getProductId()!=$productId){               
                $amounts[$oneCredit->getId()] = $oneCredit->getAmount();
                $productsName [$oneCredit->getId()] = $this->repo_pr->findOneById($oneCredit->getProductId())->getName();
                $productId = $oneCredit->getProductId();
            }            
        }

        $oorders = $repo_o->findBy(['userId'=>$user->getId(),'status'=>'ok'],['product'=>'ASC']);
        $products = [];
        foreach ($oorders as $oneOorder){
            $product = $this->repo_pr->findOneById($oneOorder->getProduct()); 
            if (!isset($products[$product->getName()])){
                $products[$product->getName()] = 1;
            }else{
                $products[$product->getName()] = $products[$product->getName()] + 1;
            }        
        }
        
        $refunds = $repo_r->findByUserId($user->getId());
        $nbRefunds = 0;
        foreach ($refunds as $oneRefund){
            $nbRefunds = $nbRefunds + $oneRefund->getCredits();
        }

        $freeCredits = $repo_fc->findByUser($user);
        $nbFreeCredits = 0;
        foreach ($freeCredits as $oneFreeCredits){
            $nbFreeCredits = $nbFreeCredits + $oneFreeCredits->getAmount();
        }

        $chargedParticipations = $this->repo_pa->participations($user->getId(),">","<");
        $nbChargedParticipations = count($chargedParticipations);
        $freeParticipations = $this->repo_pa->participations($user->getId(),"=","<");
        $nbFreeParticipations = count($freeParticipations);
        $chargedSubscriptions = $this->repo_pa->participations($user->getId(),">",">");
        $nbChargedSubscriptions = count($chargedSubscriptions);
        $freeSubscriptions = $this->repo_pa->participations($user->getId(),"=",">");
        $nbFreeSubscriptions = count($freeSubscriptions);
        $chargedInvitations = $this->repo_pa->participations($user->getId(),">","<",true);
        $nbChargedInvitations = count($chargedInvitations);
        $freeInvitations = $this->repo_pa->participations($user->getId(),"=",">",true);
        $nbFreeInvitations = count($freeInvitations);
        $chargedInvitationsFuture = $this->repo_pa->participations($user->getId(),">",">",true);
        $nbChargedInvitationsFuture = count($chargedInvitationsFuture);

        return $this->render('admin/user.html.twig',[
        'form_oorder'=>$form_oorder->createView(),
        'form_refund'=>$form_refund->createView(),
        'form_freeCredit'=>$form_freeCredit->createView(),
        'user'=>$user,
        'credits'=>$credits,
        'nbRefunds'=>$nbRefunds,
        'productsName'=>$productsName,
        'amounts'=>$amounts,        
        'products'=>$products,
        'nbChargedParticipations'=>$nbChargedParticipations,
        'nbFreeParticipations'=>$nbFreeParticipations,
        'nbChargedSubscriptions'=>$nbChargedSubscriptions,
        'nbFreeSubscriptions'=>$nbFreeSubscriptions,
        'nbChargedInvitations'=> $nbChargedInvitations,
        'nbFreeInvitations'=>$nbFreeInvitations,
        'nbChargedInvitationsFuture'=> $nbChargedInvitationsFuture,
        'nbFreeCredits'=>$nbFreeCredits
        ]);
      }

      /**
       * @Route("/structures", name="structures")
       */

       public function structures(StructureRepository $repo_s){
        $structures = $repo_s->findAll();
        return $this->render('admin/structures.html.twig',['structures'=>$structures]);
       }

        /**
       * @Route("/structure/{id}", name="structure")
       */

      public function structure(Structure $structure, LocationRepository $repo_l, CatalogRepository $repo_c, Request $request){
        
        $locations = $repo_l->findByStructure($structure);
        $catalogs = $repo_c->findByStructureId($structure->getId());
        $productsStructure = [];
        foreach ($catalogs as $oneCatalog){
            $productsStructure[] = $this->repo_pr->findOneById($oneCatalog->getProduct()->getId());
        }      

        
        $form = $this->createForm(StructureType::class,$structure);
        $form->handleRequest($request);
       
        if ($form->isSubmitted() && $form->isValid()){
            if (isset($_POST['publicSsessions'])){
                $structure->setpublicSsessions(1);
            }else{
                $structure->setpublicSsessions(0);
            }
            $this->manager->persist($structure);
            $this->manager->flush();

            for($i=5000;$i<5020;$i++){
               
                if(isset($_POST['location_name'.$i])){
                    $location2 = new Location();
                    $location2->setStructure($structure);
                    $location2->setName($_POST['location_name'.$i]);
                    if(isset($_POST['location_adress'.$i])){
                        $location2->setAdress($_POST['location_adress'.$i]);                            
                    }
                    $this->manager->persist($location2);
                    $this->manager->flush();
                }
            }

            foreach ($locations as $oneLocation){
                if(isset($_POST['location_name'.$oneLocation->getId()])){
                    $location = $repo_l->findOneById($oneLocation->getId());
                    $location->setName($_POST['location_name'.$oneLocation->getId()]);
                    if(isset($_POST['location_adress'.$oneLocation->getId()])){
                        $location->setAdress($_POST['location_adress'.$oneLocation->getId()]);                            
                    }
                    $this->manager->persist($location);
                    $this->manager->flush();
                }
            }

          
           
            $this->addFlash(
                'notice',
                'Structure Modifiée'
            );
            return $this->redirectToRoute('structure',['id'=>$structure->getId()]);
        }

        $catalog = new Catalog();
        $form_catalog = $this->createForm(CatalogType::class,$catalog);

        $form_catalog->handleRequest($request);

        if ($form_catalog->isSubmitted() && $form_catalog->isValid()){
            $catalog->setStructureId($structure->getId());
            $this->manager->persist($catalog);
            $this->manager->flush();
            $this->addFlash('notice', 'Produit ajouté !');
            return $this->redirectToRoute('structure',['id'=>$structure->getId()]);
        }

        return $this->render('admin/structure.html.twig',[
        'structure'=>$structure,
        'form'=>$form->createView(),
        'form_catalog'=>$form_catalog->createView(),
        'locations'=>$locations,
        'productsStructure'=>$productsStructure
   
        ]);
       }

       /**
        * @Route("/deleteLocation/{id}", name="deleteLocation")
        */
        
        public function deleteLocation(Location $location, StructureRepository $repo_s){
            $structure = $repo_s->findOneById($location->getStructure());
            $this->manager->remove($location);
            $this->manager->flush();
            $this->addFlash(
                'notice',
                'Lieu supprimé.'
            );
            return $this->redirectToRoute('structure',['id'=>$structure->getId()]);
        }

        /**
        * @Route("/deleteCatalog/{structure}/{product}", name="deleteCatalog")
        */
        
        public function deleteCatalog(Structure $structure, Product $product, CatalogRepository $repo_c){
            $catalog = $repo_c->findOneBy(['structureId'=>$structure->getId(),'product'=>$product]);
            $this->manager->remove($catalog);
            $this->manager->flush();
            $this->addFlash(
                'notice',
                'Produit supprimé.'
            );
            return $this->redirectToRoute('structure',['id'=>$structure->getId()]);
        }


      /**
       * @Route("/addStructure", name="addStructure")
       */

    public function addStructure(Request $request){
        $structure = new Structure();

        $form = $this->createForm(StructureType::class, $structure);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            if (isset($_POST['publicSsessions'])){
                $structure->setpublicSsessions(1);
            }
            else{
                $structure->setpublicSsessions(0);
            }
            $this->manager->persist($structure);
            $this->manager->flush();

            for ($i=0;$i<20;$i++){
                if(isset($_POST['location_name'.$i])){
                    $location = new Location();
                    $location->setStructure($structure);
                    $location->setName($_POST['location_name'.$i]);
                    if(isset($_POST['location_adress'.$i])){
                        $location->setAdress($_POST['location_adress'.$i]);                            
                    }
                    $this->manager->persist($location);
                    $this->manager->flush();           
                }
            }

            $this->addFlash(
                'notice',
                'Structure créée'
            );
            return $this->redirectToRoute('addStructure');
        }


        return $this->render('admin/addStructure.html.twig',['form'=>$form->createView()]);
    }

     /**
       * @Route("/addNotif", name="addNotif")
       */

      public function addNotif(Request $request){
        $notification = new Notification();

        $form = $this->createForm(NotificationType::class, $notification);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $notification->setUserId(0);
            $this->manager->persist($notification);
            $this->manager->flush();          
            

            $this->addFlash(
                'notice',
                'Notification créée'
            );
            return $this->redirectToRoute('addNotif');
        }

        return $this->render('admin/addNotification.html.twig',['form'=>$form->createView()]);
    }

     /**
       * @Route("/notifs", name="notifs_admin")
       */

      public function notifs(NotificationRepository $repo){          

        $notifications = $repo->findByStructureNotNull();
        return $this->render('admin/notifications.html.twig',['notifications'=>$notifications]);
    }

    /**
       * @Route("/notifModify/{id}", name="notifModify")
       */

      public function notifModify(Notification $notification, Request $request){          

        $form = $this->createForm(NotificationType::class, $notification);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $notification->setUserId(0);
            $this->manager->persist($notification);
            $this->manager->flush();          
            

            $this->addFlash(
                'notice',
                'Notification modifiée'
            );
            return $this->redirectToRoute('notifModify',['id'=>$notification->getId()]);
        }

        return $this->render('admin/modifyNotif.html.twig',['form'=>$form->createView(),'notification'=>$notification]);
    }

    /**
        * @Route("/deleteNotif/{id}", name="deleteNotif")
        */
        
        public function deleteNotif(Notification $notification, NotificationViewRepository $repo){
            
            $notificationViews = $repo->findByNotificationId($notification->getId());
            
            foreach ($notificationViews as $oneNotificationView){
                $this->manager->remove($oneNotificationView);
                $this->manager->flush();
            }

            $this->manager->remove($notification);
            $this->manager->flush();
            $this->addFlash(
                'notice',
                'Notification supprimé.'
            );
            return $this->redirectToRoute('notifs_admin');
        }

    /**
     * @Route("/addCoach", name="addCoach")
     */
    public function addCoach(UserManager $userManager, Request $request){
        
        $user = new User();
        $form = $this->createForm(CoachType::class, $user);

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()){ 

            $userManager->add($user->getLastname(),$user->getFirstname(),$user->getEmail(),
            $user->getPassword(),["ROLE_COACH"],null,"",new \Datetime(),1,0,0,1);
            
            $this->addFlash('notice', 'Coach créé !');
        }
        return $this->render('admin/addCoach.html.twig',['form'=>$form->createView()]);
    }

    /**
     * @Route("/addProduct", name="addProduct")
     */
    public function addProduct(Request $request){
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()){ 
                         
                $this->manager->persist($product);
                $this->manager->flush(); 
                $this->addFlash('notice', 'Produit créé !');
        }
        return $this->render('admin/addProduct.html.twig',['form'=>$form->createView()]);
    }


      /**
     * @Route("/profile", name="profileAdmin")
     */
    public function profile(NotificationManager $notificationManager, Request $request){
     
        $user = $this->getUser();
       
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()){
            $notificationManager->add(new \DateTime(),null,$user->getId(),"Profil modifié !");
         
            $this->manager->persist($user);
            $this->manager->flush(); 
            return $this->redirectToRoute('updateNotif',['returnRoute'=>'profileAdmin']);  
        }
        return $this->render('admin/profile.html.twig',['form'=>$form->createView()]);
    }

    /**
     * @Route("/modifyPasswordAdmin", name="modifyPasswordAdmin")
     */
    public function modifyPassword(UserPasswordEncoderInterface $encoder, Request $request){
           
        if (isset($_POST['password'])){
            $old_pwd = $request->get('formerPassword'); 
            $new_pwd = $request->get('password'); 
       
            $user = $this->getUser();
            $checkPass = $encoder->isPasswordValid($user, $old_pwd);
            if($checkPass === true) {
                $new_pwd_encoded = $encoder->encodePassword($user, $new_pwd);
                $user->setPassword( $new_pwd_encoded);
                $this->manager->persist($user);
                $this->manager->flush();
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
       
        return $this->render('admin/modifyPassword.html.twig');
    }
    
}
