<?php

namespace App\Controller\Admin;

use App\Entity\Client;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;


class ClientCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Client::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            EmailField::new('email'),
            TextField::new('password'),
            TextField::new('nom'),
            BooleanField::new('is_verified',"Est vérifié")->hideOnForm()
        ];
    }

}
