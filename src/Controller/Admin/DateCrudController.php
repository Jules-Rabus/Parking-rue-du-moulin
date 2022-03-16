<?php

namespace App\Controller\Admin;

use App\Entity\Date;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;

class DateCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Date::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            DateField::new('date')->setRequired(true),
            CollectionField::new('relation', 'Nombre de réservation')->hideOnForm()
        ];
    }

}
