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

    // Calcul de la recette du jour
    public function getRecetteJour( \DateTime $date) : int {

        $date = new \DateTime($date);
        $reservations = $this->entityManager->getRepository(Reservation::class)->findBy(array('DateArrivee'=>$date));
        $recette = 0;

        foreach ($reservations as $reservation){
            $recette += $reservation->getPrix();
        }

        return $recette;
    }

    // Calcul de la recette du jour avec un string pour twig
    public function getRecetteJourTwig(string $date) : int {

        $reservations = $this->entityManager->getRepository(Reservation::class)->findBy(array('DateArrivee'=>$date));
        $recette = 0;

        foreach ($reservations as $reservation){
            $recette += $reservation->getPrix();
        }

        return $recette;
    }

    // Calcul de la recette du mois
    public function getRecetteMois(\DateTime $date) : int {

        $reservations = $this->entityManager->getRepository(Reservation::class)->ReservationStatistiqueMois($date);
        $recette = 0;

        foreach ($reservations as $reservation){
            $recette += Reservation::Prix($reservation['DateArrivee'],$reservation['DateDepart'],$reservation['NombrePlace']);
        }

        return $recette;

    }

    // Calcul de la recette du mois avec un string pour twig
    public function getRecetteMoisTwig(string $date) : int {

        $date = new \DateTime($date);
        $reservations = $this->entityManager->getRepository(Reservation::class)->ReservationStatistiqueMois($date);
        $recette = 0;

        foreach ($reservations as $reservation){
            $recette += Reservation::Prix($reservation['DateArrivee'],$reservation['DateDepart'],$reservation['NombrePlace']);
        }

        return $recette;

    }

    // Calcul de la recette moyen des mois
    public function getRecetteMoisMoyen() : float {

        $recette = 0;
        $compteur = 0;
        $date = new \DateTime();

        while($this->dateDebut < $date){
            $recette += $this->getRecetteMois($date);
            $compteur++;
            $date->modify("-1 month");
        }

        if(!$compteur) return 0;

        return $recette/$compteur;

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

    // Calcul du meilleur mois en recette moyen
    public function getRecetteMoisMeilleur(\DateTime $date) : array {

        $date = clone $date;
        $meilleur = array('meilleur'=>0,'date'=>$date);

        while($this->dateDebut < $date){
            if( ($recette = $this->getRecetteMois($date)) > $meilleur['meilleur']){
                $meilleur['meilleur'] = round($recette,1);
                $meilleur['date'] = clone $date;
            }
            $date->modify("-1 year");
        }

        return $meilleur;

    }

    // Calcul de la recette de l'année avec un dateTime
    public function getRecetteAnnee(\DateTime $date) : int {

        $reservations = $this->entityManager->getRepository(Reservation::class)->ReservationStatistiqueAnnee($date);
        $recette = 0;

        foreach ($reservations as $reservation){
            $recette += Reservation::Prix($reservation['DateArrivee'],$reservation['DateDepart'],$reservation['NombrePlace']);
        }

        return $recette;

    }

    // Calcul de la recette de l'année avec un string pour twig
    public function getRecetteAnneeTwig(string $date) : int {

        $date = new \DateTime($date);
        $reservations = $this->entityManager->getRepository(Reservation::class)->ReservationStatistiqueAnnee($date);
        $recette = 0;

        foreach ($reservations as $reservation){
            $recette += Reservation::Prix($reservation['DateArrivee'],$reservation['DateDepart'],$reservation['NombrePlace']);
        }

        return $recette;

    }

    // Calcul de la recette moyen des annees
    public function getRecetteAnneeMoyen() : float {

        $recette = 0;
        $compteur = 0;
        $date = new \DateTime();

        while($this->dateDebut < $date){
            $recette += $this->getRecetteAnnee($date);
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

    // Calcul de la recette total depuis le debut
    public function getRecetteTotal() : int {

        $reservations = $this->entityManager->getRepository(Reservation::class)->findAll();
        $recette = 0;

        foreach ($reservations as $reservation){
            $recette += Reservation::Prix($reservation->getDateArrivee(),$reservation->getDateDepart(),$reservation->getNombrePlace());
        }

        return $recette;

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

    // Calcul du meilleur mois en vehicule moyen
    public function getVehiculeMoisMeilleur(\DateTime $date) : array {

        $date = clone $date;
        $meilleur = array('meilleur'=>0,'date'=>$date);

        while($this->dateDebut < $date){
            if( ($vehicule = $this->getVehiculeMois($date)) > $meilleur['meilleur']){
                $meilleur['meilleur'] = round($vehicule,1);
                $meilleur['date'] = clone $date;
            }
            $date->modify("-1 year");
        }

        return $meilleur;

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

    // Calcul du meilleur mois en duree stationnement moyen
    public function getDureeMoisMeilleur(\DateTime $date) : array {

        $date = clone $date;
        $meilleur = array('meilleur'=>0,'date'=>$date);

        while($this->dateDebut < $date){
            if( ($duree = $this->getDureeMois($date)) > $meilleur['meilleur']){
                $meilleur['meilleur'] = round($duree,1);
                $meilleur['date'] = clone $date;
            }
            $date->modify("-1 year");
        }

        return $meilleur;

    }

    public function getNombreReservationClientMax() : array {
        $client = $this->entityManager->getRepository(Reservation::class)->ReservationStatistiqueNombreClientMax();
        $client['client'] = $this->entityManager->getRepository(Client::class)->find($client['client']);
        return $client;
    }

    public function getNombreReservation() {
        return $this->entityManager->getRepository(Reservation::class)->ReservationStatistiqueNombre();
    }


}
