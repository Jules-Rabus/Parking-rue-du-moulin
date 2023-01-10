<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use App\Form\ClientModificationType;
use App\Form\ClientSuppressionType;
use App\Entity\Client;

#[Route('/client')]
class ClientController extends AbstractController
{
    #[Route('/', name: 'app_client')]
    public function index(): Response
    {
        return $this->render('client/index.html.twig', [
            'controller_name' => 'ClientController',
        ]);
    }

    #[Route('/reservation', name: 'app_client_reservation')]
    public function reservation(): Response
    {
        return $this->render('client/index.html.twig', [
            'controller_name' => 'ClientController',
        ]);
    }

    #[Route('/compte', name: 'app_client_compte')]
    public function compte(Request $request, ManagerRegistry $doctrine, MailerInterface $mailer): Response
    {
        $entityManager = $doctrine->getManager();
        $client = $this->getUser();
        $formModification = $this->createForm(ClientModificationType::class,$client);
        $formModification->handleRequest($request);

        if ($formModification->isSubmitted() && $formModification->isValid()) {

            $email = new Email();
            $email->from(new Address('reservation@parking-rue-du-moulin.fr','Gestion Parking'))
                ->to(new Address('reservation@parking-rue-du-moulin.fr','Copie Mail'))
                ->bcc(new Address('jules200204@gmail.com','Copie Mail'))
                ->subject('Duplication Téléphone '. $client->getTelephone())
                ->text("Duplication Téléphone : " . $client->getTelephone() . " , ID : " . $client->getId());
            $mailer->send($email);

            $entityManager->persist($client);
            $entityManager->flush();
        }


        $formSuppression = $this->createForm(ClientSuppressionType::class);
        $formSuppression->handleRequest($request);

        if ($formSuppression->isSubmitted() && $formSuppression->isValid()) {

            $email = new Email();
            $email->from(new Address('reservation@parking-rue-du-moulin.fr','Gestion Parking'))
                ->to(new Address('reservation@parking-rue-du-moulin.fr','Copie Mail'))
                ->bcc(new Address('jules200204@gmail.com','Copie Mail'))
                ->subject('Suppresion compte '. $client->getId())
                ->text("Suppresion compte : ID" . $client->getId() . " , nom : " . $client->getId() . " , mail : " . $client->getEmail() . " , telephone : " . $client->getTelephone() );
            $mailer->send($email);

            $client->setTelephone(null);
            $client->setEmail(null);
            $client->setNom(null);

            $entityManager->persist($client);
            $entityManager->flush();

            return $this->redirectToRoute('app_logout');
        }


        return $this->renderForm('client/compte.html.twig',["formModification"=>$formModification,"formSuppression"=>$formSuppression]);
    }

}
