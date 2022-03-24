<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Entity\Participation;
use App\Repository\SsessionRepository;
use App\Repository\StructureRepository;
use App\Repository\CreditRepository;
use App\Repository\UserRepository;
use App\Repository\ParticipationRepository;
use App\Repository\TicketRepository;
use App\Service\UserManager;
use App\Service\ParticipationManager;
use App\Service\CreditManager;
use App\Service\OptoutManager;

class ApiController extends AbstractController
{

     /**
     * @Route("/api/login", name="api_login", methods={"POST"})
     */
    public function login(Request $resquest)
    {    
        $user = $this->getUser();

        if($user != null){
            return $this->json([
                'id' => $user->getId(),
                'lastname' => $user->getLastname(),
                'firstname' => $user->getFirstname(),                
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
            ],201);
        }
        return $this->json(['Error'=>'no user found'],201);
    }

    /**
     * @Route("/api/registration", name="api_registration", methods={"POST"})
     */
    public function registration(Request $resquest, StructureRepository $repo_s, 
    UserRepository $repo_u, UserManager $userManager, SerializerInterface $serializer)
    {    
        $jsonRecu = $resquest->getContent();

        try{
            $user = $serializer->deserialize($jsonRecu, User::class, 'json');   
            $structure = $repo_s->findOneByCode(null);

            $user_verif = $repo_u->findOneByEmail($user->getEmail());
            if(isset($user_verif)){
                if ($user_verif->getPassword()!=" " && $user_verif->getPassword()!=""){
                    return $this->json(['Error'=> 'Cette adresse email est déjà utilisée.']);    
                }else{
                        $user = $userManager->update($user_verif,'','',$user_verif->getEmail(),
                        $user->getPassword(),["ROLE_USER"], $structure,'',new \Datetime(),0,0,0,0);                  
                }                       
            }else{
                    
                    $user = $userManager->add('','',$user->getEmail(),$user->getPassword(),["ROLE_USER"],
                    $structure,'',new \Datetime(),0,0,0);                
            }   
            
            return $this->json([
                'id' => $user->getId(),
                'lastname' => $user->getLastname(),
                'firstname' => $user->getFirstname(),                
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
            ],201);            

        }catch(NotEncodableValueException $e){
            return $this->json(['status'=>400,'messsage'=>$e->getMessage()],400);
        }      
        
    }    

    /**
     * @Route("/api/ssession", name="api_ssession", methods={"GET"})
     */
    public function ssession(SsessionRepository $repo)
    {    
        return $this->json($repo->findBy([],['scheduledAt'=>'DESC'],2,0),200,[],['groups'=>'post:read']);               
    }

    /**
     * @Route("/api/planning", name="api_planning", methods={"POST"})
     */
    public function planning(SsessionRepository $repo_s, UserRepository $repo_u, CreditRepository $repo_c,
    ParticipationRepository $repo_p, Request $resquest, SerializerInterface $serializer)
    {  
        $jsonRecu = $resquest->getContent(); 
        $user = $serializer->deserialize($jsonRecu, User::class, 'json');       
        $user = $repo_u->findOneByEmail($user->getEmail());
        $ssessions = $repo_s->findByStructureFuture($user->getStructure());
        $credits = $repo_c->findByUserId($user->getId());
        $participations = [];
        foreach ($ssessions as $oneSsession){
            $participation = $repo_p->findOneBy(['userId'=>$user->getId(),'ssessionId'=>$oneSsession->getId()]);
            if (isset($participation)){
                $participations[] = $participation;
            }
        }

        return $this->json(['ssessions'=>$ssessions,'credits'=>$credits,'participations'=>$participations],200,[],['groups'=>'post:read']);               
    }

    /**
     * @Route("/api/inscription", name="api_inscription", methods={"POST"})      
     */

     public function inscription(SsessionRepository $repo_s, ParticipationRepository $repo_p,
     TicketRepository $repo_t, CreditRepository $repo_c, ParticipationManager $participationManager, 
     CreditManager $creditManager, Request $resquest, SerializerInterface $serializer){
        
        $jsonRecu = $resquest->getContent();
        $participation = $serializer->deserialize($jsonRecu, Participation::class, 'json');

        $ssession = $repo_s->findOneById($participation->getSsessionId());

        //Check if user is already registered
        $existingParticipation = $repo_p->findBy(['userId'=>$participation->getUserId(),'ssessionId'=>$participation->getSsessionId()]);
        if (!empty($existingParticipation))
            return $this->json('Vous êtes déjà inscrit',200,[]);
        
        // Check if there is still places
        $participationMax = $ssession->getParticipationMax();
        $nbParticipations = count($repo_p->findBySsessionId($participation->getSsessionId()));
        if ($nbParticipations>=$participationMax)
            return $this->json('Inscription impossible, nombre d\'inscrits maximum atteint.',200,[]);

        //Check if user has enough credit
        $tickets = $repo_t->findBySsessionId($participation->getSsessionId());
        // If ssession isnt free 
        if (!empty($tickets)){
            // Verify if user has credits and return credit object              
            $credit = $repo_c->verify($tickets,$participation->getUserId());
            if ($credit == false)
                return $this->json('Inscription impossible, vous n\'avez pas assez de crédit.',200,[]);
            
            //Update user's credit
            $creditManager->setAmount($credit,-1);
        }

            //Add Participation
            if (isset($credit)){
                $productId = $credit->getProductId();
            }
            else{
                $productId = 0;
            }

            $participationManager->add($participation->getUserId(), $participation->getSsessionId(),
        new \DateTime(), 0, $productId, 0);

        return $this->json('Inscription enregistrée !',200,[]);
     }

     /**
      * @Route("/api/desinscription", name="api_desinscription", methods={"POST"}) 
      */

    public function desinscription(ParticipationManager $participationManager, CreditManager $creditManager,
    OptoutManager $optoutManager, CreditRepository $repo_c, ParticipationRepository $repo_p, 
    Request $request, SerializerInterface $serializer)
    {
        $jsonRecu = $request->getContent();
        $participation = $serializer->deserialize($jsonRecu, Participation::class, 'json');
        
        $participation = $repo_p->findOneBy(['userId'=>$participation->getUserId(),'ssessionId'=>$participation->getSsessionId()]);

        $credit = $repo_c->findOneBy(['userId'=>$participation->getUserId(),'productId'=>$participation->getProductId()]);
        if(isset($credit))
        $creditManager->setAmount($credit,1);

        $optoutManager->add($participation->getUserId(),$participation->getSsessionId(), new \DateTime());

        $participationManager->delete($participation);

        return $this->json('Désinscription enregistrée !',200,[]);
    }

    /**
     * @Route("/api/profile", name="api_profile", methods={"POST"})
     */

     public function profile(EntityManagerInterface $manager, UserRepository $repo_u, 
     SerializerInterface $serializer, Request $request)
     {
        $jsonRecu = $request->getContent();

        try
        {
            $new_user = $serializer->deserialize($jsonRecu, User::class, 'json');
            $user = $repo_u->findOneByEmail($new_user->getEmail());
            $new_email = json_decode($jsonRecu)->{'new_email'};
            $new_lastname = json_decode($jsonRecu)->{'new_lastname'};
            $new_firstname = json_decode($jsonRecu)->{'new_firstname'};
            
            $user->setEmail($new_email);
            $user->setLastname($new_lastname);
            $user->setFirstname($new_firstname);
            $manager->persist($user);
            $manager->flush();
            

            return $this->json([
                'id' => $user->getId(),
                'lastname' => $user->getLastname(),
                'firstname' => $user->getFirstname(),                
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
            ],201);     
        }
        catch(NotEncodableValueException $e){
            return $this->json(['status'=>400,'messsage'=>$e->getMessage()],400);
        }
     }

     /**
     * @Route("/api/nextSsession", name="api_nextSsession", methods={"GET"})
     */
    public function nextSsession(SsessionRepository $repo)
    {    
        return $this->json($repo->findByStructureFuture(23),200,[],['groups'=>'post:read']);               
    }


}
