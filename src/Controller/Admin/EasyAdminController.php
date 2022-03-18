<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Entity\Reservation;
use App\Entity\Date;
use App\Entity\Code;
use App\Entity\Client;

class EasyAdminController extends AbstractDashboardController
{
    #[Route('/easy_admin', name: 'easy_admin')]
    public function index(): Response
    {
        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        return $this->redirect($adminUrlGenerator->setController(ReservationCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirect('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        // return $this->render('some/path/my-dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Projetsymfony');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::Section('Reservation');
        yield MenuItem::SubMenu('Actions','fas fa-bars')->setSubItems([
            MenuItem::linkToCrud('Afficher Réservation', 'fas fa-eye', Reservation::class)
        ]);

        yield MenuItem::Section('Date');
        yield MenuItem::SubMenu('Actions','fas fa-bars')->setSubItems([
            MenuItem::linkToCrud('Afficher Date', 'fas fa-eye', Date::class)
        ]);

        yield MenuItem::Section('Code');
        yield MenuItem::SubMenu('Actions','fas fa-bars')->setSubItems([
            MenuItem::linkToCrud('Afficher Code', 'fas fa-eye', Code::class)
        ]);

        yield MenuItem::Section('Client');
        yield MenuItem::SubMenu('Actions','fas fa-bars')->setSubItems([
            MenuItem::linkToCrud('Afficher Client', 'fas fa-eye', Client::class)
        ]);
    }
}
