<?php

namespace App\Entity;

use App\Entity\Reservation;
use Doctrine\ORM\EntityManager;

class Statistique
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getRecetteMois(\DateTime $date) : int{

        $date = new \DateTime();
        $reservations = $this->entityManager->getRepository(Reservation::class)->ReservationMois($date);
        $recette = 0;

        foreach ($reservations as $reservation){
            $recette += Reservation::Prix($reservation['DateArrivee'],$reservation['DateDepart'],$reservation['NombrePlace']);
        }

        return $recette;

    }

    public function getRecetteMoisTwig(string $date) : int{

        $date = new \DateTime($date);
        $reservations = $this->entityManager->getRepository(Reservation::class)->ReservationMois($date);
        $recette = 0;

        foreach ($reservations as $reservation){
            $recette += Reservation::Prix($reservation['DateArrivee'],$reservation['DateDepart'],$reservation['NombrePlace']);
        }

        return $recette;

    }

    public function getRecetteJour( \DateTime $date) : int{

        $date = new \DateTime($date);
        $reservations = $this->entityManager->getRepository(Reservation::class)->findBy(array('DateArrivee'=>$date));
        $recette = 0;

        foreach ($reservations as $reservation){
            $recette += $reservation->getPrix();
        }

        return $recette;
    }

    public function getRecetteJourTwig(string $date) : int{

        $reservations = $this->entityManager->getRepository(Reservation::class)->findBy(array('DateArrivee'=>$date));
        $recette = 0;

        foreach ($reservations as $reservation){
            $recette += $reservation->getPrix();
        }

        return $recette;
    }

    public function getRecetteAnnee(\DateTime $date) : int{
        
        $reservations = $this->entityManager->getRepository(Reservation::class)->ReservationAnnee($date);
        $recette = 0;

        foreach ($reservations as $reservation){
            $recette += Reservation::Prix($reservation['DateArrivee'],$reservation['DateDepart'],$reservation['NombrePlace']);
        }

        return $recette;

    }

    public function getRecetteAnneeTwig(string $date) : int{

        $date = new \DateTime($date);
        $reservations = $this->entityManager->getRepository(Reservation::class)->ReservationAnnee($date);
        $recette = 0;

        foreach ($reservations as $reservation){
            $recette += Reservation::Prix($reservation['DateArrivee'],$reservation['DateDepart'],$reservation['NombrePlace']);
        }

        return $recette;

    }





}
