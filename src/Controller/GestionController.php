<?php

namespace App\Controller;

use App\Entity\TransfertBdd;
use App\Entity\Reservation;
use App\Entity\Date;
use App\Entity\Code;
use App\Entity\Message;
use App\Entity\Statistique;
use App\Entity\Client;
use App\Form\TransfertBddType;
use App\Form\TransfertBddSqlType;
use App\Form\PlanningJourType;
use App\Form\PlanningType;
use App\Form\ReservationType;
use App\Form\ReservationModificationType;
use App\Form\MessageType;
use App\Form\MailType;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\Persistence\ManagerRegistry;

#[Route('/gestion')]
class GestionController extends AbstractController
{

    /**
     * @Route("/{nombre_jours}", name="app_gestion_planning")
     */
    public function planning(ManagerRegistry $doctrine, Request $request, int $nombre_jours = 0): Response
    {

        $form = $this->createForm(PlanningJourType::class, NULL);
        $form->handleRequest($request);

        // On initialise les variables afin de creer la boucle et stocker les resultats

        // planning
        $entityManager = $doctrine->getManager();
        $aujourdhui = new \DateTime();
        $dateBoucle = new \DateTime('first day of this month');
        $dates = array();

        // On test pour revenir en arriere sur le planning
        if ($nombre_jours < 0) {
            $dateRetour = new \DateTime();
            $dateRetour->sub(new \DateInterval("P" . -$nombre_jours . "D"));

            // par défaut on affiche a partir du debut du mois
            if ($dateBoucle > $dateRetour) {
                $dateBoucle = $dateRetour;
            }
        } else if ($nombre_jours > 0) {
            $dateRetour = new \DateTime();
            $dateRetour->add(new \DateInterval("P" . $nombre_jours + 1 . "D"));
            if ($dateBoucle < $dateRetour) {
                $dateBoucle = $dateRetour;
            }
        }

        // planning rapide
        $planningRapide = array();
        $planningRapideDateDebut = new \DateTime('first day of this month');
        $planningRapideDateFin = (clone $dateBoucle)->modify("first day of this month")->modify("+6 month");
        $nombrePlaceDisponibleMin = 40;

        if ($form->isSubmitted() && $form->isValid()) {

            $diff = $aujourdhui->diff($form->getData()['date']);
            $nombre_jours = $diff->days;
            if ($diff->invert) $nombre_jours = -$diff->days;
            return $this->redirectToRoute('app_gestion_planning', ['nombre_jours' => $nombre_jours]);
        }

        for ($i = 0; $i < 365; $i++) {
            $dateEntite = $entityManager->getRepository(Date::class)->SelectorCreate($dateBoucle);
            $nombrePlaceDisponible = $dateEntite->getNombrePlaceDisponibles();
            $dates[$dateBoucle->format('Y-m-d')]['nombrePlaceDisponibles'] = $nombrePlaceDisponible;
            $dates[$dateBoucle->format('Y-m-d')]['Depart'] = $dateEntite->getnombreDepart();
            $dates[$dateBoucle->format('Y-m-d')]['Arrivee'] = $dateEntite->getnombreArrivee();

            // planning rapide

            // On verifie si la date ne depasse pas 6 mois
            if ($dateBoucle < $planningRapideDateFin && $dateBoucle > $planningRapideDateDebut) {

                // On actualise si necessaire le nombre de place disponible min
                if ($nombrePlaceDisponibleMin > $nombrePlaceDisponible) $nombrePlaceDisponibleMin = $nombrePlaceDisponible;

                // On clone la date de boucle et rajoute un jour
                $dateTest = (clone $dateBoucle)->modify('+1 day');
                $dateTestSemaineCourte = (clone $dateBoucle)->modify('last day of this month');

                // A chaque debut de mois ou de debut de semaine (L-D), on enregistre
                if ($dateBoucle->format('W') != $dateTest->format('W') && $dateTestSemaineCourte->diff($dateBoucle)->days > 2
                    || $dateBoucle->format('m') != $dateTest->format('m')
                ) {
                    // On enregistre le nombre de place disponible minimun pour cette date
                    $planningRapide[$dateBoucle->format('M')][$dateBoucle->format('d')] = $nombrePlaceDisponibleMin;
                    $nombrePlaceDisponibleMin = 40;
                }
            }

            $dateBoucle->modify('+1 day');
        }


        return $this->renderForm('gestion/planning.html.twig', ['form' => $form, 'dates' => $dates, 'date' => $aujourdhui, 'planningRapide' => $planningRapide
        ]);
    }

    /**
     * @Route("/planning_jour/{date}", name="app_gestion_planning_jour")
     * @ParamConverter("date", options={"format": "Y-m-d"})
     */
    public function planningJour(Request $request, ManagerRegistry $doctrine, \DateTime $date, MailerInterface $mailer): Response
    {
        //On creer le formulaire pour aller à la date du planning voulu
        $form = $this->createForm(PlanningJourType::class, NULL, ['date' => $date]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // On redirige vers la date voulu
            return $this->redirectToRoute('app_gestion_planning_jour', ['date' => $form->getData()['date']->format('Y-m-d')]);
        }

        //On initialise les variables afin recuperer toutes les informations de cette journee sur le parking
        $entityManager = $doctrine->getManager();
        $arrivees = $entityManager->getRepository(Reservation::class)->FindBy(array("DateArrivee" => $date), array("DateDepart" => "ASC"));
        $departs = $entityManager->getRepository(Reservation::class)->FindBy(array("DateDepart" => $date), array("DateArrivee" => "ASC"));
        $dateEntite = $entityManager->getRepository(Date::class)->SelectorCreate($date);
        $voitures = $dateEntite->getRelation()->getValues();
        $numeroPlaceDispo = $dateEntite->getNumeroPlaceDispo();
        $nombrePlaceDisponibles = $dateEntite->getNombrePlaceDisponibles();
        $nbrArrivee = $dateEntite->getnombreArrivee();
        $nbrDepart = $dateEntite->getnombreDepart();
        $aujourdhui = new \DateTime();

        // On rajoute la possibilite donner le code
        $arriveesTemplate = [];

        foreach ($arrivees as $key => $reservation) {
            $nombreReservation = $reservation->getClient()->getNombreReservation();
            $messageEntite = new Message($reservation, $nombreReservation, $mailer, true);
            $messageEntite->messageCode();
            $code = $messageEntite->getMessageTelephone();
            $arriveesTemplate[$key] = [
                'entite' => $reservation,
                'code' => $code
            ];
        }

        return $this->renderForm('gestion/planningjour.html.twig', ['form' => $form, 'date' => $date, 'aujourdhui' => $aujourdhui, 'arrivees' => $arriveesTemplate, 'departs' => $departs,
            "nombrePlaceDisponibles" => $nombrePlaceDisponibles, 'voitures' => $voitures, 'nbrArrivee' => $nbrArrivee, 'nbrDepart' => $nbrDepart, 'numeroPlaceDispo' => $numeroPlaceDispo
        ]);
    }

}