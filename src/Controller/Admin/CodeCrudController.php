<?php

namespace App\Controller\Admin;

use App\Entity\Code;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;

class CodeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Code::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            IntegerField::new('Code'),
            DateField::new('DateDebut')->setRequired(true),
            DateField::new('DateFin')->setRequired(true),
            CollectionField::new('Reservations', 'Nombre de réservation')->hideOnForm()
        ];
    }

}
