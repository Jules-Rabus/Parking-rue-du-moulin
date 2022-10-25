<?php

namespace App\Form;

use App\Entity\Reservation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationModificationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id', HiddenType::class,)
            ->add('NombrePlace',IntegerType::class,[
                'label' => 'Nombre de Place',
                'required' => true,
                'attr' =>['min'=>1, 'max'=>10]
            ])
            ->add('DateArrivee', DateType::class,[
                'label' => 'Date d\'arrivée',
                'widget' => 'single_text',
                'required' => true,
                'attr'=>['min'=>$options['date']->format('Y-m-d')]
            ])
            ->add('DateDepart', DateType::class,[
                'label' => 'Date de départ',
                'widget' => 'single_text',
                'required' => true,
                'attr'=>['min'=>$options['date']->format('Y-m-d')]
            ])
            ->add('Annuler', SubmitType::class, [])
            ->add('Modifier', SubmitType::class, [])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'date' => new \DateTime(),
        ]);
    }
}
