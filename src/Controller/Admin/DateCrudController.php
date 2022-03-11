<?php

namespace App\Controller\Admin;

use App\Entity\Date;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class DateCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Date::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
