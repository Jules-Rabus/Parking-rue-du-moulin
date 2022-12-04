<?php

namespace App\Form;

use App\Entity\Reservation;
use App\Entity\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ReservationClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('NombrePlace',IntegerType::class,[
                'label' => 'Nombre de Place',
                'required' => true,
                'attr' =>['min'=>1, 'max'=>5, 'value' => 1]
            ])
            ->add('DateArrivee', DateType::class,[
                'label' => 'Date d\'arrivée',
                'widget' => 'single_text',
                'data' => $options['date'],
                'required' => true,
                'attr'=>['min'=>$options['date']->format('Y-m-d')]
            ])
            ->add('DateDepart', DateType::class,[
                'label' => 'Date de départ',
                'widget' => 'single_text',
                'data' => $options['date'],
                'required' => true,
                'attr'=>['min'=>$options['date']->format('Y-m-d')]
            ])
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
