<?php

namespace App\Controller\Admin;

use App\Entity\Code;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CodeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Code::class;
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
