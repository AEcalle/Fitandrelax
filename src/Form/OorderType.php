<?php

namespace App\Form;

use App\Entity\Oorder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Product;

class OorderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder          
            ->add('product',EntityType::class,['class'=>Product::class,'choice_label'=>'name'])
            ->add('createdAt', DateTimeType::class, ['widget' => 'single_text'])          
            ->add('mode',ChoiceType::class,['choices'=>
            [
                'En Ligne'=>' ',
                'Chèque'=>'cheque',
                'Virement'=>'virement',            
                ]])
            ->add('submit_post', SubmitType::class) 
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Oorder::class,
        ]);
    }
}
