<?php

namespace App\Controller\Admin;

use App\Entity\Reservation;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use Doctrine\ORM\EntityManagerInterface;

class ReservationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Reservation::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            DateField::new('DateArrivee','Date d\'arrivée')->setRequired(true),
            DateField::new('DateDepart','Date de départ')->setRequired(true),
            IntegerField::new('NombrePlace','Nombre de Place')->setRequired(true),
            AssociationField::new('Client'),
            TelephoneField::new('Telephone')->setRequired(false),
            AssociationField::new('CodeAcces',' Code d\'accès')->hideOnForm(),
            CollectionField::new('Dates')->hideOnForm(),
            DateField::new('DateReservation','Date de réservation')->hideOnForm(),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if(!$entityInstance instanceof Reservation) return;

        $entityInstance->setDateReservation(new \DateTime());
        $entityInstance->AjoutDates($entityManager);

        parent::persistEntity($entityManager,$entityInstance);
    }

}
