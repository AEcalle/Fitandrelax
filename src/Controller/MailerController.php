<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\UserRepository;
use App\Repository\ParticipationRepository;
use App\Repository\SsessionRepository;
use App\Repository\EmailNotificationRepository;
use App\Repository\NotificationRepository;
use App\Repository\NotificationViewRepository;
use App\Repository\CreditRepository;
use App\Repository\StructureRepository;
use App\Entity\User;
use App\Entity\Ssession;
use App\Entity\Sponsorship;
use SendinBlue\Client\Configuration;
use SendinBlue\Client\Api\TransactionalEmailsApi;
use SendinBlue\Client\Model\SendSmtpEmail;
use GuzzleHttp\Client;

class MailerController extends AbstractController
{
    private $session;
    private $config;
    private $apiInstance;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
        $this->config = Configuration::getDefaultConfiguration()->setApiKey('api-key', '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');
        $this->apiInstance = new TransactionalEmailsApi(   
            new Client(),
            $this->config
        );
    }
    
    /**
     * @Route("/invitationMail/{user}/{guest}/{ssession}", name="invitationMail")
     */
    public function invitationMail(User $user,User $guest,Ssession $ssession){    
        
                $sendSmtpEmail = new SendSmtpEmail();
                $sendSmtpEmail['to'] = array(array('email'=>$guest->getEmail()));
                $sendSmtpEmail['sender'] = array("name"=> "Relaxeo", "email"=>"contact@fitandrelax.fr");
                $sendSmtpEmail['replyTo'] = array("email"=>'contact@fitandrelax.fr');
                $sendSmtpEmail['subject'] = 'Fit & Relax - Invitation'; 
                $sendSmtpEmail['textContent'] = 'Bonjour '.$guest->getFirstname().', '.$user->getFirstName().' '.$user->getLastname().' ('
                .$user->getEmail().') vous a inscrit à la séance de '.str_replace("_"," ",$ssession->getActivity()->getName()).' 
                prévue '.$ssession->getscheduledAt()->format('d/m H:i').'. Pour tout renseignement complémentaire, 
                n’hésitez pas à nous contacter via contact@fitandrelax.fr.
                Bonne séance !
                L’équipe Fit&Relax';      
                $sendSmtpEmail['htmlContent'] = '<p>Bonjour '.$guest->getFirstname().',</p><p>'.$user->getFirstName().' '.$user->getLastname().' ('
                .$user->getEmail().') vous a inscrit à la séance de '.str_replace("_"," ",$ssession->getActivity()->getName()).' 
                prévue '.$ssession->getscheduledAt()->format('d/m H:i').'.</p><p>Pour tout renseignement complémentaire, 
                n’hésitez pas à nous contacter via contact@fitandrelax.fr.</p><p>
                Bonne séance !</p> <p>L’équipe Fit&Relax</p>';
                
                try {
                    $result = $this->apiInstance->sendTransacEmail($sendSmtpEmail);
                    $this->addFlash(
                        'notice',
                        'Votre invitation est bien prise en compte.'
                        );          
                } catch (Exception $e) {
                    $this->addFlash(
                        'notice',
                        'Suite à un problème technique, le mail n\'a pas pu être envoyé.'
                        );                   
                } 
        
        return $this->redirectToRoute('updateNotif',['returnRoute'=>'planning']);
    }

     /**
     * @Route("/inscriptionExt/{user}/{ssession}", name="inscriptionExt")
     */
    public function inscriptionExt(User $user,Ssession $ssession){    
        
        $sendSmtpEmail = new SendSmtpEmail();
        $sendSmtpEmail['to'] = array(array('email'=>$user->getEmail()));
        $sendSmtpEmail['sender'] = array("name"=> "Relaxeo", "email"=>"contact@fitandrelax.fr");
        $sendSmtpEmail['replyTo'] = array("email"=>'contact@fitandrelax.fr');
        $sendSmtpEmail['subject'] = 'Fit & Relax - Confirmation inscription'; 
        $sendSmtpEmail['textContent'] = 'Bonjour '.$user->getFirstname().', Nous vous confirmons votre inscription à la séance de '.str_replace("_"," ",$ssession->getActivity()->getName()).' 
        prévue '.$ssession->getscheduledAt()->format('d/m H:i').'. Pour tout renseignement complémentaire, 
        n’hésitez pas à nous contacter via contact@fitandrelax.fr.
        Bonne séance !
        L’équipe Fit&Relax';      
        $sendSmtpEmail['htmlContent'] = '<p>Bonjour '.$user->getFirstname().',</p><p>Nous vous confirmons votre inscription à la séance de '.str_replace("_"," ",$ssession->getActivity()->getName()).' 
        prévue '.$ssession->getscheduledAt()->format('d/m H:i').'.</p><p>Pour tout renseignement complémentaire, 
        n’hésitez pas à nous contacter via contact@fitandrelax.fr.</p><p>
        Bonne séance !</p>
        <p>L’équipe Fit&Relax</p>';
        
        try {
            $result = $this->apiInstance->sendTransacEmail($sendSmtpEmail);
            $this->addFlash('notice','Merci pour votre paiement, votre inscription est bien enregistrée !');                      
        } catch (Exception $e) {
            $this->addFlash(
                'notice',
                'Suite à un problème technique, le mail de confirmation n\'a pas pu être envoyé.'
                );                   
        }           
        return $this->redirectToRoute('security_inscription',['id'=>$ssession->getId()]);
    }

         /**
     * @Route("/mailZoom/{user}/{ssession}", name="mailZoom")
     */
    public function mailZoom(User $user,Ssession $ssession, NotificationRepository $repo_n,
    NotificationViewRepository $repo_nv){    
        
                
        $notifications = $repo_n->findByUserOrStructure($user->getId(),$this->session->get('structure')->getId()); 
        $this->session->set('notifications', $notifications);
        $notificationViews = $repo_nv->findByUserId($user->getId());
        $newNotifications = count($notifications) - count($notificationViews);
        $this->session->set('newNotifications', $newNotifications);

        $sendSmtpEmail = new SendSmtpEmail();
        $sendSmtpEmail['to'] = array(array('email'=>$user->getEmail()));
        $sendSmtpEmail['sender'] = array("name"=> "Relaxeo", "email"=>"contact@fitandrelax.fr");
        $sendSmtpEmail['replyTo'] = array("email"=>'contact@fitandrelax.fr');
        $sendSmtpEmail['subject'] = 'Fit & Relax - Confirmation inscription - Codes Zoom'; 
        $sendSmtpEmail['textContent'] = 'Bonjour '.$user->getFirstname().', Nous vous confirmons votre inscription à la séance de '.str_replace("_"," ",$ssession->getActivity()->getName()).' 
        prévue '.$ssession->getscheduledAt()->format('d/m H:i').'. Nous vous invitons à vous connecter 5 minutes avant le début de la séance. Pour tout renseignement complémentaire, 
        n’hésitez pas à nous contacter via contact@fitandrelax.fr.
        Bonne séance !
        L’équipe Fit&Relax';      
        $sendSmtpEmail['htmlContent'] = '<p>Bonjour '.$user->getFirstname().',</p><p>Nous vous confirmons votre inscription à la séance de '.str_replace("_"," ",$ssession->getActivity()->getName()).' 
        prévue le '.$ssession->getscheduledAt()->format('d/m H:i').'.</p><p>Les codes pour accéder à votre séance sur Zoom
        sont les suivants. <br /><br /> ID réunion : '.$ssession->getIdZoom().' <br /> Mot de passe : '.$ssession->getPassZoom().' <br />Le lien pour rejoindre la séance : <a href="https://zoom.us/join">https://zoom.us/join</a></p>
        <p>Nous vous invitons à vous connecter 5 minutes avant le début de la séance.</p><p>Pour tout renseignement complémentaire, 
        n’hésitez pas à nous contacter via contact@fitandrelax.fr.</p><p>
        Bonne séance !</p>
        <p>L’équipe Fit&Relax</p>';

        try {
            $result = $this->apiInstance->sendTransacEmail($sendSmtpEmail);
            $this->addFlash('notice','Merci pour votre inscription ! Retrouvez les identifiants pour rejoindre votre séance sur Zoom dans la rubrique "vos inscriptions" ci-dessous.');                                    
        } catch (Exception $e) {
            $this->addFlash(
                'notice',
                'Suite à un problème technique, le mail de confirmation n\'a pas pu être envoyé.'
                );                   
        }    
            
      
        return $this->redirectToRoute('planning');
    }

    /**
     * @Route("/autoSubscriptionEmail/{user}/{ssession}",name="autoSubscriptionEmail")
     */

    public function autoSubscriptionEmail(User $user, Ssession $ssession){

        $sendSmtpEmail = new SendSmtpEmail();
        $sendSmtpEmail['to'] = array(array('email'=>$user->getEmail()));
        $sendSmtpEmail['sender'] = array("name"=> "Relaxeo", "email"=>"contact@fitandrelax.fr");
        $sendSmtpEmail['replyTo'] = array("email"=>'contact@fitandrelax.fr');
        $sendSmtpEmail['subject'] = 'Inscription à la séance de '.str_replace("_"," ",$ssession->getActivity()).' du '.$ssession->getscheduledAt()->format("d/m H:i"); 
        $sendSmtpEmail['textContent'] = 'Bonjour '.$user->getFirstname().',
		Vous étiez sur liste d\'attente pour la séance de '.str_replace('_',' ',$ssession->getActivity()->getName()).' du '.$ssession->getscheduledAt()->format('d/m H:i').'. 
		Une place s\'est libérée, vous avez donc été inscrit(e) automatiquement pour cette séance.
		Si finalement vous ne pouvez pas participer, vous pouvez vous désinscrire sur votre espace relaxeo.
		Pour tout renseignement complémentaire, n’hésitez pas à nous contacter via contact@fitandrelax.fr.
		Bonne séance !
		L’équipe Fit&Relax';      
        $sendSmtpEmail['htmlContent'] = '<p>Bonjour '.$user->getFirstname().',</p>
		<p>Vous étiez sur liste d\'attente pour la séance de '.str_replace('_',' ',$ssession->getActivity()->getName()).' du '.$ssession->getscheduledAt()->format('d/m H:i').'.</p> 
		<p>Une place s\'est libérée, vous avez donc été inscrit(e) automatiquement pour cette séance.</p>
		<p>Si finalement vous ne pouvez pas participer, vous pouvez vous désinscrire sur votre espace relaxeo.
		Pour tout renseignement complémentaire, n’hésitez pas à nous contacter via contact@fitandrelax.fr.</p>
		<p>Bonne séance !</p>
		<p>L’équipe Fit&Relax</p>';

        try {
            $result = $this->apiInstance->sendTransacEmail($sendSmtpEmail);                                              
        } catch (Exception $e) {
                         
        }        
        return $this->redirectToRoute('updateNotif',['returnRoute'=>'planning']);
    }

    /**
     * @Route("/coursPart", name="coursPart")
     */

    public function coursPart(Request $request){

        $email = $request->request->get('email');
   
        if (isset($email)){

            $sendSmtpEmail = new SendSmtpEmail();
            $sendSmtpEmail['to'] = array(array('email'=>'contact@fitandrelax.fr'));
            $sendSmtpEmail['sender'] = array("name"=> "Relaxeo", "email"=>"contact@fitandrelax.fr");
            $sendSmtpEmail['replyTo'] = array("email"=>$email);
            $sendSmtpEmail['subject'] = "Cours particuliers"; 
            $sendSmtpEmail['textContent'] = 
            'Activité : '.$request->request->get('activity').' '.$request->request->get('message');      
            $sendSmtpEmail['htmlContent'] = 
            'Activité : '.$request->request->get('activity').'<br /><br />
            '.$request->request->get('message');

            try {
                $result = $this->apiInstance->sendTransacEmail($sendSmtpEmail);
                $this->addFlash(
                    'notice',
                    'Votre message a bien été envoyé. Nous vous répondrons dans les plus brefs délais.'
                    );
            } catch (Exception $e) {
                $this->addFlash(
                    'notice',
                    'Suite à un problème technique, votre message n\'a pas pu être envoyé. Veuillez réessayer ou nous contacter à l\'adresse contact@fitandrelax.fr '
                    );                   
            } 

            return $this->redirectToRoute('fit');
        }
        
        return $this->redirectToRoute('fit');
    }

    /**
     * @Route("/contactExtMail", name="contactExtMail")
     */

     public function contactExtMail(Request $request){        
            
         
        if (isset($_POST['objet'])){           
     
        

            // Build POST request:
            $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
            $recaptcha_secret = '6Ld0gOIZAAAAAArAHOixQTUz4WIblNL6_QPvHOX3';
            $recaptcha_response = $_POST['recaptcha_response'];

            // Make and decode POST request:
            $recaptcha = file_get_contents($recaptcha_url . '?secret=' . $recaptcha_secret . '&response=' . $recaptcha_response);
            $recaptcha = json_decode($recaptcha);

            if ($recaptcha->score==null)
                $score = 1;
            else
                $score = $recaptcha->score;

            if ($score>0.5){
                $adress = "";
                if (isset($adress)){
                    $adress = htmlspecialchars(trim($_POST['adress']));
                } 
                if($adress!="Russia" && strpos($_POST['msg'], "Bitcoin") === false){
                    $sendSmtpEmail = new SendSmtpEmail();
                    $sendSmtpEmail['to'] = array(array('email'=>'contact@fitandrelax.fr'));
                    $sendSmtpEmail['sender'] = array("name"=> "Relaxeo", "email"=>"contact@fitandrelax.fr");
                    $sendSmtpEmail['replyTo'] = array("email"=>htmlspecialchars(trim($_POST['email'])));
                    $sendSmtpEmail['subject'] = htmlspecialchars(trim($_POST['objet'])); 
                    $sendSmtpEmail['textContent'] = 'Adresse : '.$adress.'<br />'.nl2br(htmlspecialchars(trim($_POST['msg'])));      
                    $sendSmtpEmail['htmlContent'] = 'Adresse : '.$adress.'<br />'.nl2br(htmlspecialchars(trim($_POST['msg'])));
                    
                    try {
                        $result = $this->apiInstance->sendTransacEmail($sendSmtpEmail);
                        $this->addFlash(
                            'notice',
                            'Votre message a bien été envoyé. Nous vous répondrons dans les plus brefs délais.'
                            );
                    } catch (Exception $e) {
                        $this->addFlash(
                            'notice',
                            'Suite à un problème technique, votre message n\'a pas pu être envoyé. Veuillez réessayer ou nous contacter à l\'adresse contact@fitandrelax.fr '
                            );                   
                    } 
                }             
            }else{
                $this->addFlash(
                    'notice',
                    'Suite à un problème technique, votre message n\'a pas pu être envoyé. Veuillez réessayer ou nous contacter à l\'adresse contact@fitandrelax.fr '
                    );
            }
        }
        
        return $this->redirectToRoute('security_contact');
     }

    /**
     * @Route("/contactIntMail", name="contactIntMail")
     */
     public function contactIntMail(Request $request){
        if (isset($_POST['objet'])){

                $sendSmtpEmail = new SendSmtpEmail();
                $sendSmtpEmail['to'] = array(array('email'=>'contact@fitandrelax.fr'));
                $sendSmtpEmail['sender'] = array("name"=> "Relaxeo", "email"=>"contact@fitandrelax.fr");
                $sendSmtpEmail['replyTo'] = array("email"=>$this->getUser()->getEmail());
                $sendSmtpEmail['subject'] = htmlspecialchars(trim($_POST['objet'])); 
                $sendSmtpEmail['textContent'] = nl2br(htmlspecialchars(trim($_POST['msg'])));      
                $sendSmtpEmail['htmlContent'] = 'Ce message a été envoyé par : '.$this->getUser()->getFirstname().' '
                .$this->getUser()->getLastname().'<br />'.nl2br(htmlspecialchars(trim($_POST['msg'])));
                
                try {
                    $result = $this->apiInstance->sendTransacEmail($sendSmtpEmail);
                    $this->addFlash(
                        'notice',
                        'Votre message a bien été envoyé. Nous vous répondrons dans les plus brefs délais.'
                        );          
                } catch (Exception $e) {
                    $this->addFlash(
                        'notice',
                        'Suite à un problème technique, votre message n\'a pas pu être envoyé. Veuillez réessayer ou nous contacter à l\'adresse contact@fitandrelax.fr '
                        );                   
                } 
            
        }
        return $this->redirectToRoute('contact');
    }
    

    /**
     * @Route("/contactParrainage", name="contactParrainage")
     */
    public function contactParrainage(Request $request, EntityManagerInterface $manager){
        if ($request->get('email')!==null){

                $sendSmtpEmail = new SendSmtpEmail();
                $sendSmtpEmail['to'] = array(array('email'=>$request->get('email')));                
                $sendSmtpEmail['replyTo'] = array("email"=>"contact@fitandrelax.fr");
                $sendSmtpEmail['templateId'] = 71; 
                $sendSmtpEmail['params'] = array('FNAME' => $this->getUser()->getFirstname(), 'LNAME' => $this->getUser()->getLastname(), 
                'MESSAGE' => $request->get('comment'));               
                
                try {
                    $result = $this->apiInstance->sendTransacEmail($sendSmtpEmail);
                    $sponsorShip = new Sponsorship();
                    $sponsorShip->setUser($this->getUser());
                    $sponsorShip->setGodson($request->get('email'));
                    $sponsorShip->setCreatedAt(new \DateTime());
                    $manager->persist($sponsorShip);
                    $manager->flush();
                    $this->addFlash(
                        'notice',
                        'Le mail de proposition de parrainage a été envoyé à votre filleul.'
                        );                  
                } catch (Exception $e) {
                    $this->addFlash(
                        'notice',
                        'Suite à un problème technique, votre message n\'a pas pu être envoyé. Veuillez réessayer ou nous contacter à l\'adresse contact@fitandrelax.fr '
                        );                   
                }      
             
        }
        return $this->redirectToRoute('parrainage');
    }

     /**
     * @Route("/newUserEmail/{userId}/{token}", name="newUserEmail")
     */
    public function newUserEmail($userId,$token){
      
                $sendSmtpEmail = new SendSmtpEmail();
                $sendSmtpEmail['to'] = array(array('email'=>$this->getUser()->getEmail()));                
                $sendSmtpEmail['replyTo'] = array("email"=>"contact@fitandrelax.fr");
                $sendSmtpEmail['templateId'] = 77; 

                $sendSmtpEmail2 = new SendSmtpEmail();
                $sendSmtpEmail2['to'] = array(array('email'=>$this->getUser()->getEmail()));                
                $sendSmtpEmail2['replyTo'] = array("email"=>"contact@fitandrelax.fr");
                $sendSmtpEmail2['templateId'] = 92; 
                $sendSmtpEmail2['params'] = array('LINK' => "https://relaxeo.net/verifyEmail/$userId/$token"); 
                              
                
                try {
                    $result = $this->apiInstance->sendTransacEmail($sendSmtpEmail); 
                    $result = $this->apiInstance->sendTransacEmail($sendSmtpEmail2);              
                    
                } catch (Exception $e) {
                              
                }          
       
        return $this->redirectToRoute('home');
    }

     /**
     * @Route("/resendEmailVerify/{userId}/{token}", name="resendEmailVerify")
     */
    public function resendEmailVerify($userId,$token){  
      

        $sendSmtpEmail = new SendSmtpEmail();
        $sendSmtpEmail['to'] = array(array('email'=>$this->getUser()->getEmail()));                
        $sendSmtpEmail['replyTo'] = array("email"=>"contact@fitandrelax.fr");
        $sendSmtpEmail['templateId'] = 92; 
        $sendSmtpEmail['params'] = array('LINK' => "https://relaxeo.net/verifyEmail/$userId/$token"); 
                      
        
        try {
            $result = $this->apiInstance->sendTransacEmail($sendSmtpEmail); 
            $this->addFlash(
                'notice',
                'Email envoyé.'
                );                     
            
        } catch (Exception $e) {
                      
        }          
      
        return $this->redirectToRoute('profile');
}

      /**
     * @Route("/reminder", name="reminder")
     */
    public function reminder(UserRepository $repo_u, CreditRepository $repo_c, SsessionRepository $repo_ss, 
    StructureRepository $repo_s){
        $users = $repo_u->reminder();        
       
        foreach ($users as $user){
      
            $credits = $repo_c->findByUserId($user->getId());
            $credit = 0;
            foreach ($credits as $oneCredit) {
                $credit = $credit + $oneCredit->getAmount();
            }
            $structure = $repo_s->findOneById(23);
            $ssessionsList = $repo_ss->findByStructureFuture($structure);
        
            $ssessions = [];
            $i = 0;
            $frenchDay = ["","Lundi","Mardi","Mercredi","Jeudi","Vendredi","Samedi","Dimanche"];
            foreach ($ssessionsList as $oneSsession){
                $activity = str_replace("_"," ",$oneSsession->getActivity()->getName()); 
                $ssessions[] = ['scheduledAt'=>$frenchDay[$oneSsession->getScheduledAt()->format("N")].' '.$oneSsession->getScheduledAt()->format("d/m à H:i"),
                'activity'=>$activity,
                'subtitle'=>$oneSsession->getSubtitle()];
        
                $i++;
                if($i>=4)
                    break;   
            }
        
            $sendSmtpEmail = new SendSmtpEmail();
            $sendSmtpEmail['to'] = array(array('email'=>$user->getEmail()));                
            $sendSmtpEmail['replyTo'] = array("email"=>"contact@fitandrelax.fr");
            $sendSmtpEmail['templateId'] = 80; 
            $sendSmtpEmail['params'] = array('CREDIT' => $credit,'SSESSIONS'=>$ssessions); 
                        
            
            try {
                $result = $this->apiInstance->sendTransacEmail($sendSmtpEmail);               
                
            } catch (Exception $e) {
                        
            }          
        }
       
        return $this->json('ok',200,[]);
    }

     /**
     * @Route("/planningHebdo", name="planningHebdo")
     */
    public function planningHebdo(UserRepository $repo_u,SsessionRepository $repo_ss, 
    StructureRepository $repo_s){
        $users = $repo_u->findByPlanningHebdo(1);        
       
        foreach ($users as $user){      
           
            $structure = $repo_s->findOneById(23);
            $ssessionsList = $repo_ss->findByStructureFuture($structure);
        
            $ssessions = [];
            $limitDate = date("Y-m-d H:i:s",strtotime("+7 days"));
            
            $frenchDay = ["","Lundi","Mardi","Mercredi","Jeudi","Vendredi","Samedi","Dimanche"];
            foreach ($ssessionsList as $oneSsession){
                
                if($oneSsession->getScheduledAt()->format("Y-m-d h:i:s")>$limitDate)
                    break;

                $activity = str_replace("_"," ",$oneSsession->getActivity()->getName()); 
                $ssessions[] = ['scheduledAt'=>$frenchDay[$oneSsession->getScheduledAt()->format("N")].' '.$oneSsession->getScheduledAt()->format("d/m à H:i"),
                'activity'=>$activity,
                'subtitle'=>$oneSsession->getSubtitle()];   
                  
            }
        
            $sendSmtpEmail = new SendSmtpEmail();
            $sendSmtpEmail['to'] = array(array('email'=>$user->getEmail()));                
            $sendSmtpEmail['replyTo'] = array("email"=>"contact@fitandrelax.fr");
            $sendSmtpEmail['templateId'] = 88; 
            $sendSmtpEmail['params'] = array('SSESSIONS'=>$ssessions); 
                        
            
            try {
                $result = $this->apiInstance->sendTransacEmail($sendSmtpEmail);               
                
            } catch (Exception $e) {
                        
            }          
        }
        
        return $this->json('ok',200,[]);
    }

      /**
     * @Route("/coachMail/{id}", name="coachMail")
     */
    public function coachMail(Ssession $ssession, Request $request){
        if (isset($_POST['coachComment'])){

            $sendSmtpEmail = new SendSmtpEmail();
            $sendSmtpEmail['to'] = array(array('email'=>'contact@fitandrelax.fr'));
            $sendSmtpEmail['sender'] = array("name"=> "Relaxeo", "email"=>"contact@fitandrelax.fr");
            $sendSmtpEmail['replyTo'] = array("email"=>$this->getUser()->getEmail());
            $sendSmtpEmail['subject'] = 'Remarque de '.$ssession->getCoach()->getFirstname().' - Séance du '.$ssession->getScheduledAt()->format('d/m/Y'); 
            $sendSmtpEmail['textContent'] = $request->request->get('coachComment');      
            $sendSmtpEmail['htmlContent'] = $request->request->get('coachComment');
            
            try {
                $result = $this->apiInstance->sendTransacEmail($sendSmtpEmail);
                $this->addFlash(
                    'notice',
                    'Votre message a bien été envoyé.'
                    );
            } catch (Exception $e) {
                $this->addFlash(
                    'notice',
                    'Suite à un problème technique, votre message n\'a pas pu être envoyé. Veuillez réessayer ou nous contacter à l\'adresse contact@fitandrelax.fr '
                    );                   
            }         
           
        }
        return $this->redirectToRoute('coachParticipations',['id'=>$ssession->getId()]);
    }

    /**
     * @Route("/resetPasswordMail", name="resetPasswordMail")
     */
    public function resetPasswordMail(Request $request, 
    EntityManagerInterface $manager, UserRepository $repo, TokenGeneratorInterface $tokenGenerator){        
        if (isset($_POST['email'])){
            $user = $repo->findOneByEmail(htmlspecialchars(trim($_POST['email'])));
         
            if (!$user){
                $this->addFlash(
                    'notice',
                    'Cet email ne correspond à aucun compte existant.'
                 );  
            }
            else{
                $user->setToken($tokenGenerator->generateToken());
                $user->setPasswordRequestedAt(new \Datetime());
                $manager->persist($user);
                $manager->flush();  
                
            
                $sendSmtpEmail = new SendSmtpEmail();
                $sendSmtpEmail['to'] = array(array('email'=>htmlspecialchars(trim($_POST['email']))));
                $sendSmtpEmail['sender'] = array("name"=> "Relaxeo", "email"=>"contact@fitandrelax.fr");
                $sendSmtpEmail['replyTo'] = array("email"=>'contact@fitandrelax.fr');
                $sendSmtpEmail['subject'] = "Relaxeo - Renouvellement du mot de passe"; 
                $sendSmtpEmail['textContent'] = 'Bonjour, Une erreur est survenue. Veuillez contactez le support';      
                $sendSmtpEmail['htmlContent'] = '<p>Bonjour, <br />Vous avez fait une demande de changement de mot de passe
                sur Relaxeo. <br>Cliquez sur ce lien pour changer votre mot de passe. 
                <a href="http://relaxeo.net/resetting/'.$user->getId().'/'.$user->getToken().'" target="_blank">
                Changer le mot de passe</a><br /><br />L\'équipe Fit & Relax.
                </p>';
                
                try {
                    $result = $this->apiInstance->sendTransacEmail($sendSmtpEmail);
                    $this->addFlash(
                        'notice',
                        'Un email vous a été envoyé afin que vous puissiez renouveller votre mot de passe.'
                        );     
                } catch (Exception $e) {
                    $this->addFlash(
                        'notice',
                        'Suite à un problème technique, le mail n\'a pas pu être envoyé. Veuillez réessayer ou nous contacter à l\'adresse contact@fitandrelax.fr '
                        );                   
                } 
               
            }
           
        }           
        return $this->redirectToRoute('security_forgot');  
    }

    /**
     * @Route("/rappel1", name="rappel1")
     */
    public function rappel1(UserRepository $repo_u, SsessionRepository $repo_s, 
    ParticipationRepository $repo_p, Request $request){
            $tommorow = date("Y-m-d H:i:s",strtotime("+1 day"));
            $ssessions = $repo_s->findSsessionsByDate($tommorow);
          
            foreach ($ssessions as $oneSsession){
            
                $participations = $repo_p->findBySsessionId($oneSsession->getId());
                foreach ($participations as $oneParticipation){
               
                    $users = $repo_u->findBy(['rappel'=>1,'id'=>$oneParticipation->getUserId()]);
                    foreach ($users as $oneUser){

                        $sendSmtpEmail = new SendSmtpEmail();
                        $sendSmtpEmail['to'] = array(array('email'=>$oneUser->getEmail()));
                        $sendSmtpEmail['sender'] = array("name"=> "Relaxeo", "email"=>"contact@fitandrelax.fr");
                        $sendSmtpEmail['replyTo'] = array("email"=>'contact@fitandrelax.fr');
                        $sendSmtpEmail['subject'] = 'Relaxeo - Rappel'; 
                        $sendSmtpEmail['textContent'] = 'Bonjour '.$oneUser->getFirstname().',
                        Vous êtes inscrit(e) à une séance demain. Si finalement vous ne pouvez pas participer, merci de vous désinscrire sur votre espace relaxeo.
                        A bientôt !   
                        L’équipe Fit&Relax
                        Pour ne plus recevoir cette notification, modifiez vos préférences sur votre espace relaxeo.';      
                        $sendSmtpEmail['htmlContent'] = '<p>Bonjour '.$oneUser->getFirstname().',</p>
                        <p>Vous êtes inscrit(e) à une séance demain. Si finalement vous ne pouvez pas participer, merci de vous désinscrire sur votre <a href="https://relaxeo.net/">espace relaxeo</a>.</p>
                        <p>A bientôt !</p>  
                        <p>L’équipe Fit&Relax</p>
                        <p>Pour ne plus recevoir cette notification, modifiez vos préférences sur votre <a href="https://relaxeo.net/">espace relaxeo.</a></p>';
                        
                        try {
                            $result = $this->apiInstance->sendTransacEmail($sendSmtpEmail);
                           
                        } catch (Exception $e) {                                        
                        }          
                    
                    }
                }
                
            }
            
            return $this->redirectToRoute('security_login');
    
    }

    /**
     * @Route("/rappel2", name="rappel2")
     */

     /* Mail de rappel abandonné
    public function rappel2(UserRepository $repo_u, SsessionRepository $repo_s, 
    ParticipationRepository $repo_p,EmailNotificationRepository $repo_e, 
    Request $request){
            
            $tommorow = date("Y-m-d H:i:s",strtotime("+1 day"));
            $ssessions = $repo_s->findSsessionsByDate($tommorow);
          
            foreach ($ssessions as $oneSsession){
                $emailNotifications = $repo_e->findByActivity($oneSsession->getActivity()->getName());
                foreach ($emailNotifications as $oneEmailNotification){
                    $users = $repo_u->findBy(['structure'=>$oneSsession->getStructure(),'id'=>$oneEmailNotification->getUserId()]);
                    foreach ($users as $oneUser){                        
                        $participation = $repo_p->findOneBy(['userId'=>$oneUser->getId(),'ssessionId'=>$oneSsession->getId()]);
                        if (!isset($participation)){    
                            
                            $sendSmtpEmail = new SendSmtpEmail();
                            $sendSmtpEmail['to'] = array(array('email'=>$oneUser->getEmail()));
                            $sendSmtpEmail['sender'] = array("name"=> "Relaxeo", "email"=>"contact@fitandrelax.fr");
                            $sendSmtpEmail['replyTo'] = array("email"=>'contact@fitandrelax.fr');
                            $sendSmtpEmail['subject'] = 'Relaxeo - Rappel'; 
                            $sendSmtpEmail['textContent'] = 'Bonjour '.$oneUser->getFirstname().',
                            Une séance de '.$oneSsession->getActivity()->getName().' a lieu demain. N\'oubliez-pas de vous inscrire !                        
                            A bientôt !                        
                            L’équipe Fit&Relax
                            Pour ne plus recevoir cette notification, modifiez vos préférences sur votre espace relaxeo.';      
                            $sendSmtpEmail['htmlContent'] = '<p>Bonjour '.$oneUser->getFirstname().',</p>
                            <p>Une séance de '.$oneSsession->getActivity()->getName().' a lieu demain. N\'oubliez-pas de vous inscrire !</p>                        
                            <p>A bientôt !</p>                      
                            <p>L’équipe Fit&Relax</p>
                            <p>Pour ne plus recevoir cette notification, modifiez vos préférences sur votre espace relaxeo.</p>';
                            
                            try {
                                $result = $this->apiInstance->sendTransacEmail($sendSmtpEmail);
                               
                            } catch (Exception $e) {                                        
                            }    
           
                         
                        }    
                    }
                }     
            }
            
            return $this->redirectToRoute('security_login');
    
    }
    */
}