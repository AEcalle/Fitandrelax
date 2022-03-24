<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Entity\Ssession;
use App\Entity\Oorder;
use App\Entity\Participation;
use App\Repository\StructureRepository;
use App\Repository\SsessionRepository;
use App\Repository\UserRepository;
use App\Repository\TicketRepository;
use App\Repository\ProductRepository;
use App\Form\RegistrationType;
use App\Service\CreditManager;
use App\Service\FreeCreditManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class SecurityController extends AbstractController
{
     
    /**
     * @Route("/inscription", name="security_registration")
    */
    public function registration(Request $request, EntityManagerInterface $manager, 
    UserPasswordEncoderInterface $encoder, StructureRepository $repo, UserRepository $repo_u, 
    CreditManager $creditManager, FreeCreditManager $freeCreditManager, ProductRepository $repo_p,
    TokenGeneratorInterface $tokenGenerator){
        $user = new User();
  
        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()){ 
            if($_POST['code']=="")
                $structure = $repo->findOneByCode(null);
            else
                $structure = $repo->findOneByCode($_POST['code']);
            
            if (!isset($structure)){
                return $this->render('security/registration.html.twig',['form'=>$form->createView(),
                'erreurCode'=>'Ce code n\'existe pas']);
            }
            else{    
                $user_verif = $repo_u->findOneByEmail($user->getEmail());
          
                if (isset($user_verif)){          
                    if ($user_verif->getPassword()!=" " && $user_verif->getPassword()!=""){                        
                        return $this->render('security/registration.html.twig',['form'=>$form->createView(),
                        'erreurCode'=>'Cette adresse email est déjà utilisée.']);
                    }
                    else{
                        $user_verif->setStructure($structure);
                        $hash = $encoder->encodePassword($user_verif, $user->getPassword());
                        $user_verif->setPassword($hash);
                        $user_verif->setFirstName($user_verif->getFirstName()); 
                        $user_verif->setLastName($user_verif->getLastname());
                        $user_verif->setMobile("");
                        $user_verif->setCreatedAt(new \Datetime()); 
                        $user_verif->setRappel(0);
                        $user_verif->setPlanningHebdo(0);
                        $user_verif->setAadmin(0); 
                        $user_verif->setEmailVerify(0); 
                        $user_verif->setToken($tokenGenerator->generateToken());                     
                        $manager->persist($user_verif);
                        $manager->flush(); 
                           // AUtomatic login
                            $token = new UsernamePasswordToken(
                                $user_verif,
                                $hash,
                                'main',
                                $user->getRoles()
                            );
                            $creditManager->add(39,$user_verif->getId(),2,$user_verif->getStructure()->getId());
                            $product = $repo_p->findOneById(39);
                            $freeCreditManager->add($product,$user_verif,2);
                            $user = $user_verif;
                    }
                }
                else{           
                    //Registration
                    $user->setStructure($structure);
                    $hash = $encoder->encodePassword($user, $user->getPassword());
                    $user->setPassword($hash); 
                    $user->setLastname("");
                    $user->setFirstname("");
                    $user->setMobile("");
                    $user->setCreatedAt(new \Datetime()); 
                    $user->setRappel(0);
                    $user->setPlanningHebdo(0);
                    $user->setAadmin(0);
                    $user->setEmailVerify(0); 
                    $user->setToken($tokenGenerator->generateToken());                        
                    $manager->persist($user);
                    $manager->flush(); 
                       // AUtomatic login
                    $token = new UsernamePasswordToken(
                    $user,
                    $hash,
                    'main',
                    $user->getRoles()
                 );
                 // $creditManager->add(39,$user->getId(),2,$user->getStructure()->getId());
                 // $product = $repo_p->findOneById(39);
                 //$freeCreditManager->add($product,$user,2);
                }
                
                             
                $this->get('security.token_storage')->setToken($token);
                $this->get('session')->set('_security_main', serialize($token));               

                return $this->redirectToRoute('newUserEmail',['userId'=>$user->getId(),'token'=>$user->getToken()]);
            }            
        }
       
        return $this->render('security/registration.html.twig',['form'=>$form->createView()]);
   }


   /**
     * @Route("/mot_de_passe_oublie", name="security_forgot")
    */
    public function forgot(){       

        $src = mt_rand(1,5);
        return $this->render('security/forgot.html.twig',['src'=> $src]);
    }

    /**
     * @Route("/resetting/{userId}/{token}", name="security_resetting")
    */
    public function resetting($userId, $token){
        
        return $this->render('security/resetting.html.twig',['userId'=>$userId,'token'=>$token]);
    }

    /**
     * @Route("/newPwd", name="security_newPwd")
    */
    public function newPwd(UserRepository $repo, UserPasswordEncoderInterface $encoder,EntityManagerInterface $manager){
        
        if (isset($_POST['userId'])){
            $user = $repo->findOneById(htmlspecialchars(trim($_POST['userId'])));
            if (htmlspecialchars(trim($_POST['token'])) == $user->getToken()){
                $limit = date("Y-m-d H:i:s",strtotime("-24 hours"));
                if ($user->getPasswordRequestedAt()->format("Y-m-d H:i:s")>$limit){
                    $new_pwd_encoded = $encoder->encodePassword($user, htmlspecialchars(trim($_POST['password'])));
                    $user->setPassword($new_pwd_encoded);
                    $manager->persist($user);
                    $manager->flush();
                    $this->addFlash(
                        'notice',
                        'Mot de passe modifié'
                     );                    
                }
            }
        }

        return $this->redirectToRoute('security_login');
    }

    /**
     * @Route("/verifyEmail/{userId}/{token}", name="security_verifyEmail")
    */
    public function verifyEmail($userId, $token, UserRepository $repo, EntityManagerInterface $manager){
        
        $user = $repo->findOneById($userId);
        if ($token == $user->getToken()){
            $user->setEmailVerify(1);
            $manager->persist($user);
            $manager->flush();
            $this->addFlash(
                'notice',
                'Votre adresse email a bien été vérifiée, merci.'
                ); 
        }else{
            $this->addFlash(
                'notice',
                'Une erreur est survenue et votre adresse mail n\'a pas pu être vérifiée.'
                ); 
        }
        
        return $this->redirectToRoute('home');
    }
 
    /**
     * @Route("/contactForm", name="security_contact")
    */
    public function contactForm(){
   
        
        return $this->render('security/contact.html.twig');
    }

     /**
     * @Route("/mentions", name="security_mentions")
     */
    public function mentions(){      
      
        return $this->render('security/mentions.html.twig');
    }

     /**
     * @Route("/cgv", name="security_cgv")
     */
    public function cgv(){      
      
        return $this->render('security/cgv.html.twig');
    }

    /**
     * @Route("/planningPublic", name="planningPublic")
     */
    public function planningPublic(StructureRepository $repo_st,SsessionRepository $repo_s){
        $structure = $repo_st->findByCode(null);
        $ssessions = $repo_s->findByStructureFuture($structure);
  
        return $this->render('security/planningPublic.html.twig',['ssessions'=>$ssessions]);
    }  

     /**
     * @Route("/bienvenue", name="bienvenue")
     */
    public function bienvenue(){
        
        return $this->render('security/bienvenue.html.twig');
    } 

    /**
     * @Route("/fit", name="fit")
     */
    public function fit(){
        
        return $this->render('security/bienvenue.html.twig');
    } 
        
    /**
     * @Route("/inscription/{id}", name="security_inscription")
     */
    public function inscription(Ssession $ssession, TicketRepository $repo_t, ProductRepository $repo_p,
    UserRepository $repo_u, EntityManagerInterface $manager){      
        if ($ssession->getOff()!=1){
            return $this->redirectToRoute('security_login');
        }
        else
        {
            if (isset($_POST['email'])){            
                $user = $repo_u->findOneByEmail(htmlspecialchars(trim($_POST['email'])));
                if (!isset($user)){
                    $user = new User();
                    $user->setEmail(htmlspecialchars(trim($_POST['email'])));
                    $user->setLastname(htmlspecialchars(trim($_POST['lastname'])));
                    $user->setFirstname(htmlspecialchars(trim($_POST['firstname'])));
                    $user->setPassword(" ");
                    $user->setMobile(" ");
                    $user->setCreatedAt(new \DateTime());
                    $user->setRappel(0);
                    $user->setAadmin(0);
                    $manager->persist($user);
                    $manager->flush();  
                }
                
                $product = $repo_p->findOneById(htmlspecialchars(trim($_POST['product'])));
                $oorder = new Oorder();
                $oorder->setUserId($user->getId());
                $oorder->setProduct($product);
                $oorder->setCreatedAt(new \DateTime());
                $oorder->setStatus("");
                $oorder->setMode("");

                $manager->persist($oorder);
                $manager->flush();

                require_once("payplug-php-3.0.0/lib/init.php");
                \Payplug\Payplug::setSecretKey('sk_live_3k5R6QsTLK63yWiqrwTxTO');
                $email = $user->getEmail();
                $amount = $product->getPrice();
                $customer_id = $user->getId();

                $payment = \Payplug\Payment::create(array(
                'amount'           => $amount * 100,
                'currency'         => 'EUR',
                'billing'  => array(
                'title'        => 'mr',
                'first_name'   => $user->getFirstname(),
                'last_name'    => $user->getLastname(),
                'email'        => $email,
                'address1'      => '221B Baker Street',
                'postcode'      => 'NW16XE',
                'city'          => 'London',
                'country'       => 'GB',
                'language'      => 'fr',
                ),
                'shipping'  => array(
                'title'         => 'mr',
                'first_name'    => $user->getFirstname(),
                'last_name'     => $user->getLastname(),
                'email'         => $email,
                'address1'      => '221B Baker Street',
                'postcode'      => 'NW16XE',
                'city'          => 'London',
                'country'       => 'GB',
                'language'      => 'fr',
                'delivery_type' => 'BILLING'
                ),
                'hosted_payment'   => array(
                'return_url'     => 'https://www.fitandrelax.fr/security_paymentReturn/'.$oorder->getId().'/'.$ssession->getId(),
                'cancel_url'     => 'https://www.relaxeo.net/inscription/'.$ssession->getId()
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
            $tickets = $repo_t->findBySsessionId($ssession->getId());
    
            $products = [];
            foreach ($tickets as $oneTicket){
                $product = $repo_p->findOneById($oneTicket->getProductId());
                if ($product->getCredits()==1 && $product->getTtype()!=3)
                    $products[] = $product;
            }
        
            return $this->render('security/inscription.html.twig',['ssession'=>$ssession,'products'=>$products]);
        }
    }

     /**
     * @Route("/paymentReturn/{oorder}/{ssession}", name="security_paymentReturn")
     */

    public function security_paymentReturn(Oorder $oorder, Ssession $ssession,
    ProductRepository $repo_p, EntityManagerInterface $manager){     
          
            if ($oorder->getStatus()==""){
                $product = $repo_p->findOneById($oorder->getProduct()->getId());             
                
                $oorder->setStatus("ok");

                $participation = new Participation();
                $participation->setUserId($oorder->getUserId());
                $participation->setSsessionId($ssession->getId());
                $participation->setCreatedAt(new \DateTime());
                $participation->setInvitedBy(0);
                $participation->setProductId($product->getId());
                $participation->setPresent(0);
                $manager->persist($participation);
                $manager->flush();
                
                return $this->redirectToRoute('inscriptionExt',['user'=>$oorder->getUserId(),'ssession'=>$ssession->getId()]);
            }
       
        return $this->redirectToRoute('security_inscription',['id'=>$ssession->getId()]);
     }

    

   /**    
     * @Route("/connexion", name="security_login")
    */
   public function login(AuthenticationUtils $authenticationUtils){
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $src = mt_rand(1,5);

        return $this->render('security/login.html.twig', [
        'last_username' => $lastUsername,
        'error'         => $error,
        'src'         => $src,
    ]);
   }

   /**
    * @Route("/deconnexion", name="security_logout")    
    */

    public function logout(){}
    
}
