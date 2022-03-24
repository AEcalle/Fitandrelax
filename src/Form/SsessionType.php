<?php

namespace App\Form;

use App\Entity\Ssession;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Repository\UserRepository;
use App\Entity\Structure;
use App\Entity\User;
use App\Entity\Location;
use App\Entity\Activity;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


class SsessionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('scheduledAt', DateTimeType::class, ['widget' => 'single_text'])
            ->add('finishedAt', DateTimeType::class, ['widget' => 'single_text'])
            ->add('activity',EntityType::class,['class'=>Activity::class,'choice_label'=>'name']) 
            ->add('subtitle')
            ->add('description')
            ->add('participationMax')
            ->add('timeLimit', DateTimeType::class, ['widget' => 'single_text'])
            ->add('structure', EntityType::class,['class'=>Structure::class,'choice_label'=>'name']) 
            ->add('coach', EntityType::class,['class'=>User::class,
            'required'=>false,  
            'query_builder'=>function(UserRepository $repo_u){
                return $repo_u->createQueryBuilder('u')
                ->where('u.coach=1')->orderBy('u.firstname','ASC');
            },                     
            'choice_label'=>'FullName']) 
            ->add('location', EntityType::class,['class'=>Location::class,
            'required'=>false,                        
            'choice_label'=>'name'])   
            ->add('idZoom')
            ->add('passZoom')              
            ->add('submit_post', SubmitType::class) 
            ->add('delete_post', SubmitType::class)
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Ssession::class,             
        ]);
    }
}
