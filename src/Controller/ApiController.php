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
        $dateArrivee = $request->query->get('dateArrivee');
        $dateDepart = $request->query->get('dateDepart');
        $nombrePlace =$request->query->get('nombrePlace');


        if(!$dateDepart || !$dateArrivee || $nombrePlace < 1 || $nombrePlace > 10){
            return new JsonResponse("Erreur dans les arguments",400);
        }

        $reservation = new Reservation();
        $reservation->setDateArrivee(new \DateTime($dateArrivee));
        $reservation->setDateDepart(new \DateTime($dateDepart));
        $reservation->setNombrePlace($nombrePlace);
        $entityManager = $doctrine->getManager();
        $disponible = $reservation->VerificationDisponibilites($entityManager) > 5;

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
        $dateArrivee = $request->query->get('dateArrivee');
        $dateDepart = $request->query->get('dateDepart');

        if(!$dateDepart || !$dateArrivee ){
            return new JsonResponse("Erreur dans les arguments",400);
        }

        $dateArrivee = new \DateTime($dateArrivee);
        $dateDepart = new \DateTime($dateDepart);
        $duree = $dateArrivee->diff($dateDepart)->days+1;
        $dates = array();
        $entityManager = $doctrine->getManager();
        $dateBoucle = new \DateTime($dateArrivee->format('Y-m-d'));

        for($i = 0 ; $i < $duree; $i++){

            $date = $entityManager->getRepository(Date::class)->FindOneBy(array("Date"=>$dateBoucle));
            $dates[$i] = [ 'date' => $dateBoucle->format('Y-m-d'), 'categorie' =>  $date->getnombrePlaceCategorie()] ;
            $dateBoucle = new \DateTime(($dateBoucle->add(new \DateInterval("P1D"))->format('Y-m-d')));
        }

        return new JsonResponse($dates,200,['Access-Control-Allow-Origin: *']);
    }

}
