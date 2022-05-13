<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Reservation;
use App\Entity\Date;
use Doctrine\Persistence\ManagerRegistry;

#[Route('/api_reservation')]
class ApiController extends AbstractController
{
    #[Route('/pre_reservation', name: 'api_pre_reservation', methods:"GET")]
    public function preReservation(Request $request, ManagerRegistry $doctrine): JsonResponse
    {

        // On recupere les elements venant de la requete
        $dateArrivee = $request->query->get('dateArrivee');
        $dateDepart = $request->query->get('dateDepart');
        $nombrePlace =$request->query->get('nombrePlace');

        // On verifie la requete reçu
        if(!$dateDepart || !$dateArrivee || $nombrePlace < 1 || $nombrePlace > 10){
            // On retourne une erreur avec un code erreur http 400
            return new JsonResponse("Erreur dans les arguments",400);
        }

        // On cree une reservation et verifie sa disponibilite
        $reservation = new Reservation();
        $reservation->setDateArrivee(new \DateTime($dateArrivee));
        $reservation->setDateDepart(new \DateTime($dateDepart));
        $reservation->setNombrePlace($nombrePlace);
        $entityManager = $doctrine->getManager();
        $disponible = $reservation->VerificationDisponibilites($entityManager) > 5;

        // On retourne un tableau avec les reponses de l'api un code http 200, sous forme de json
        return new JsonResponse([
            "prix"=>$reservation->getPrix(),
            "dateDepart"=>$reservation->getDateDepart(),
            "dateArrivee"=>$reservation->getDateArrivee(),
            "nombrePlace"=>$reservation->getNombrePlace(),
            "disponible"=>$disponible
        ],200,['Access-Control-Allow-Origin: *']);
    }

    #[Route('/planning', name: 'api_planning', methods:"GET")]
    public function planning(Request $request, ManagerRegistry $doctrine): JsonResponse
    {
        // On recupere les elements venant de la requete
        $dateArrivee = $request->query->get('dateArrivee');
        $dateDepart = $request->query->get('dateDepart');

        // On verifie la requete reçu
        if(!$dateDepart || !$dateArrivee ){
            // On retourne une erreur avec un code erreur http 400
            return new JsonResponse("Erreur dans les arguments",400);
        }

        // On initialise les variables afin de creer la boucle et stocker les resultats
        $dateArrivee = new \DateTime($dateArrivee);
        $dateDepart = new \DateTime($dateDepart);
        $duree = $dateArrivee->diff($dateDepart)->days+1;
        $dates = array();
        $entityManager = $doctrine->getManager();
        $dateBoucle = new \DateTime($dateArrivee->format('Y-m-d'));

        // On fait tourner la boucle pour recuperer les donnees de la duree souhaite
        for($i = 0 ; $i < $duree; $i++){

            $date = $entityManager->getRepository(Date::class)->FindOneBy(array("Date"=>$dateBoucle));
            $dates[$i] = [ 'date' => $dateBoucle->format('Y-m-d'), 'categorie' =>  $date->getnombrePlaceCategorie()] ;
            $dateBoucle = new \DateTime(($dateBoucle->add(new \DateInterval("P1D"))->format('Y-m-d')));
        }

        // On retourne la reponse avec un code http 200
        return new JsonResponse($dates,200,['Access-Control-Allow-Origin: *']);
    }

}
