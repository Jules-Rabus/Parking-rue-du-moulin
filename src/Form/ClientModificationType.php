<?php

namespace App\Form;

use App\Entity\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Validator\Constraints\Length;

class ClientModificationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom',TextType::class,[
                'label' => 'Nom',
                'attr' => [
                    'placeholder' => 'Ex : Pierre Dupont',
                    'autocomplete' => 'name'
                ]
                ,'constraints' => [
                new Length([
                    'min' => 3,
                    'minMessage' => 'Votre nom doit être de minimum {{ limit }} caractères',
                    'maxMessage' => 'Votre nom doit être de maximun {{ limit }} caractères',
                    'max' => 48,
                ]),
                ]
            ])
            ->add('telephone',TelType::class, [
                'label' => 'Téléphone',
                'required' => false,
                'attr' => [
                    'placeholder'=> 'Ex : +33671731835',
                    'autocomplete' => 'tel'
                ],
                'constraints' => [
                    new Length([
                        'min' => 10,
                        'minMessage' => 'Votre téléphone doit être de minimum {{ limit }} caractères',
                        'maxMessage' => 'Votre téléphone doit être de maximun {{ limit }} caractères',
                        'max' => 20,
                    ]),
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Client::class,
        ]);
    }
}
