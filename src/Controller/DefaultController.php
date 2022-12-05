<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\ReservationClientType;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'default')]
    public function index(): Response
    {
        $form = $this->createForm(ReservationClientType::class,null);
        return $this->renderForm('default/index.html.twig',['formReservation' => $form]);
    }


}
