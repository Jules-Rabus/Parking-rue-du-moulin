<?php

namespace App\Entity;

use App\Entity\Reservation;
use App\Entity\Date;
use Doctrine\ORM\EntityManager;

class Statistique
{
    private $entityManager;
    private \DateTime $dateDebut;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->dateDebut = new \DateTime('2020-09-01');
    }

    // Recette

    // Calcul de la recette du mois
    public function getRecetteMois(\DateTime $date) : float {

        $reservations = $this->entityManager->getRepository(Reservation::class)->ReservationStatistiqueMois($date);
        $recette = 0;

        foreach ($reservations as $reservation){
            $recette += Reservation::Prix($reservation['DateArrivee'],$reservation['DateDepart'],$reservation['NombrePlace']);
        }

        return $recette;

    }

    // Calcul de la recette du jour
    public function getRecetteJour( \DateTime $date) : float {

        $date = new \DateTime($date);
        $reservations = $this->entityManager->getRepository(Reservation::class)->findBy(array('DateArrivee'=>$date));
        $recette = 0;

        foreach ($reservations as $reservation){
            $recette += $reservation->getPrix();
        }

        return $recette;
    }

    // Calcul de la recette de l'année avec un dateTime
    public function getRecetteAnnee(\DateTime $date) : float {

        $reservations = $this->entityManager->getRepository(Reservation::class)->ReservationStatistiqueAnnee($date);
        $recette = 0;

        foreach ($reservations as $reservation){
            $recette += Reservation::Prix($reservation['DateArrivee'],$reservation['DateDepart'],$reservation['NombrePlace']);
        }

        return $recette;

    }

    // Calcul de la recette du jour avec un string pour twig
    public function getRecetteJourTwig(string $date) : float {

        $reservations = $this->entityManager->getRepository(Reservation::class)->findBy(array('DateArrivee'=>$date));
        $recette = 0;

        foreach ($reservations as $reservation){
            $recette += $reservation->getPrix();
        }

        return $recette;
    }

    // Calcul de la recette du mois avec un string pour twig
    public function getRecetteMoisTwig(string $date) : float {

        $date = new \DateTime($date);
        $reservations = $this->entityManager->getRepository(Reservation::class)->ReservationStatistiqueMois($date);
        $recette = 0;

        foreach ($reservations as $reservation){
            $recette += Reservation::Prix($reservation['DateArrivee'],$reservation['DateDepart'],$reservation['NombrePlace']);
        }

        return $recette;

    }

    // Calcul de la recette de l'année avec un string pour twig
    public function getRecetteAnneeTwig(string $date) : float {

        $date = new \DateTime($date);
        $reservations = $this->entityManager->getRepository(Reservation::class)->ReservationStatistiqueAnnee($date);
        $recette = 0;

        foreach ($reservations as $reservation){
            $recette += Reservation::Prix($reservation['DateArrivee'],$reservation['DateDepart'],$reservation['NombrePlace']);
        }

        return $recette;

    }

    // Calcul de la recette moyenne du mois avec les dernieres annees
    public function getRecetteMoisMoyenne(\DateTime $date) : float {

        $recette = 0;
        $compteur = 0;
        $date = clone $date;

        while($this->dateDebut < $date){
            $recette += $this->getRecetteMois($date);
            $compteur++;
            $date->modify("-1 year");
        }

        if(!$compteur) return 0;

        return $recette/$compteur;

    }


    // Calcul de la recette moyenne d'une annee
    public function getRecetteAnneeMoyenne(\DateTime $date) : float {

        $recette = 0;
        $compteur = 0;
        $date = clone $date;

        while($this->dateDebut < $date){
            $recette += $this->getRecetteAnnee($date);
            $compteur++;
            $date->modify("-1 year");
        }

        if(!$compteur) return 0;

        return $recette/$compteur;

    }


    // Nombre de vehicule


    // Calcul de la moyenne du vehicule pour un mois
    public function getVehiculeMois(\DateTime $date) : float {

        $date = clone $date;
        $date->modify('first day of this month');
        $dateBoucle = clone $date;
        $date->modify('last day of this month');
        $compteur = 0;
        $vehicules = 0;

        while ( $date > $dateBoucle ){
            $dateEntite = $this->entityManager->getRepository(Date::class)->SelectOrCreate($dateBoucle);
            $vehicules += $dateEntite->getNombrePlaceDisponibles();
            $dateBoucle->modify("+1 day");
            $compteur++;
        }

        if(!$compteur) return 40;

        return 40 - $vehicules/$compteur;

    }

    // Calcul de la moyenne de vehicule du mois avec les dernieres annees
    public function getVehiculeMoisMoyenne(\DateTime $date) : float {

        $vehicules = 0;
        $compteur = 0;
        $date = clone $date;

        while($this->dateDebut < $date){
            $vehicules += $this->getVehiculeMois($date);
            $compteur++;
            $date->modify("-1 year");
        }

        if(!$compteur) return 0;

        return $vehicules/$compteur;

    }


    // Duree

    // Calcul de la duree moyenne du mois
    public function getDureeMois( \DateTime $date) : float {

        $reservations = $this->entityManager->getRepository(Reservation::class)->ReservationStatistiqueMois($date);
        $duree = 0;
        $compteur = 0;
        $date = clone $date;

        foreach ($reservations as $reservation){
            $duree += Reservation::DureeReservation($reservation['DateArrivee'],$reservation['DateDepart']);
            $compteur++;
        }

        if(!$compteur) return 0;

        return $duree/$compteur;
    }

    // Calcul de la duree moyenne du mois avec les dernieres annees
    public function getDureeMoisMoyenne( \DateTime $date) : float {

        $duree = 0;
        $compteur = 0;
        $date = clone $date;

        while($this->dateDebut < $date){
            $duree += $this->getDureeMois($date);
            $compteur++;
            $date->modify("-1 year");
        }

        if(!$compteur) return 0;

        return $duree/$compteur;
    }


}
