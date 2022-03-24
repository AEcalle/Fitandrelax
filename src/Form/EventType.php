<?php

namespace App\Form;

use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('subject')
            ->add('scheduledAt',DateTimeType::class, ['widget' => 'single_text'])
            ->add('finishedAt',DateTimeType::class, ['widget' => 'single_text'])        
            ->add('place')
            ->add('comment')
            ->add('private',CheckboxType::class, [               
                'required' => false,
            ])
            ->add('submit_post', SubmitType::class) 
            ->add('delete_post', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
