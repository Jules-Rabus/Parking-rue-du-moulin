<?php

namespace App\Form;

use App\Entity\Reservation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TelType;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('NombrePlace',IntegerType::class,[
                'attr'=>['min'=>0, 'max'=>10]
            ])
            ->add('DateArrivee', DateType::class,[
                'widget' => 'single_text',
                'data' => $options['date'],
                'attr'=>['min'=>$options['date']->format('Y-m-d')]
            ])
            ->add('DateDepart', DateType::class,[
                'widget' => 'single_text',
                'data' => $options['date'],
                'attr'=>['min'=>$options['date']->format('Y-m-d')]
            ])
            ->add('Telephone',TelType::class)
            ->add('Client')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
            'date' => new \DateTime(),
        ]);
    }
}
