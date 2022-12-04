<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Reservation;
use App\Entity\Message;
use App\Entity\Date;
use App\Entity\Client;
use App\Entity\Code;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Mailer\MailerInterface;

#[Route('/api')]
class ApiController extends AbstractController
{

    /**
     * @Route("/ajout_reservation", name="api_ajout_reservation", methods={"POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    #[Route('/ajout_reservation', name: 'api_ajout_reservation', methods:"POST")]
    public function ajoutReservation(Request $request, ManagerRegistry $doctrine, MailerInterface $mailer) : JsonResponse
    {
        // On recupere la reservation
        $requete = $request->toArray();


        if(  empty($requete['client']) || empty($requete['reservation']) ){
            // On retourne une erreur avec un code erreur http 400
            return new JsonResponse("Erreur dans le client",400);
        }


        $client = $requete['client'];
        $reservation = $requete['reservation'];

        $entityManager = $doctrine->getManager();

        $clientEntite = $entityManager->getRepository(Client::class)->find($client['id']);

        $reservationEntite = new Reservation();
        $reservationEntite->setClient($clientEntite);
        $reservationEntite->setNombrePlace($reservation['nombrePlace']);
        $reservationEntite->setDateArrivee(new \DateTime($reservation['dateArrivee']));
        $reservationEntite->setDateDepart(new \DateTime($reservation['dateDepart']));
        $reservationEntite->setDateReservation(new \DateTime());


        if($client['telephone'] != null) $reservationEntite->setTelephone($client['telephone']);

        if($reservationEntite->VerificationDisponibilites($entityManager) != -1){
            if($reservationEntite->getDateDepart() >= $reservationEntite->getDateArrivee()){
                $reservationEntite->AjoutDates($entityManager);

                $code = $entityManager->getRepository(Code::class)->SelectOrCreate($reservationEntite->getDateArrivee(),$reservationEntite->getDateDepart(),$mailer);
                $reservationEntite->setCodeAcces($code);

                $entityManager->persist($reservationEntite);
                $entityManager->flush();
                
                $messageEntite = new Message($reservationEntite,$reservationEntite->NombreReservation($entityManager),$mailer);

                $formulaire = ['debut'=> true, 'fin'=> true,'reservation'=>true,'code'=>false, 'explication' =>false ];
                $confirmation = $messageEntite->TraitementFormulaire($formulaire,$doctrine);
                $formulaire['explication'] = false;
                $explication = $messageEntite->TraitementFormulaire($formulaire,$doctrine);

            }
            else{
                // On retourne un message decrivant l'erreur et un code erreur : 1
                return new JsonResponse([
                    "message" => "Les dates sont invalides",
                    "erreur" => 1
                ],200);
            }
        }
        else{
            // On retourne un message decrivant l'erreur et un code erreur : 2
            return new JsonResponse([
                "message" => "Il n'y a pas de place pour ces dates",
                "erreur" => 2
            ],200);
        }

        // On retourne un tableau avec les reponses de l'api un code http 200, sous forme de json
        return new JsonResponse([
            $requete,
            "message" => "La réservation a bien été enregistré",
            "erreur" => 0,
            "confirmation" => $confirmation,
            "explication" => $explication
        ],200,['Access-Control-Allow-Origin: *']);
    }

    /**
     * @Route("/ajout_contact", name="api_ajout_contact", methods={"POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function ajoutContact(Request $request, ManagerRegistry $doctrine) : JsonResponse
    {
        // On recupere le client
        $client = $request->toArray();

        $entityManager = $doctrine->getManager();

        if(  empty($client['nom']) || empty($client['contact']) ){
            // On retourne une erreur avec un code erreur http 400
            return new JsonResponse("Erreur dans le client",400);
        }

        $clientEntite = new Client();

        if (filter_var($client['contact'], FILTER_VALIDATE_EMAIL)) {

            if ( !empty($entityManager->getRepository(Client::class)->countEmail($client['contact']))){
                // On retourne un message decrivant l'erreur et un code erreur : 1
                return new JsonResponse([
                    "message" => "Ce mail existe déjà",
                    "erreur" => 1
                ],200);
            }

            $clientEntite->setEmail($client['contact']);

        }
        elseif (  strlen($client['contact']) > 9 && strlen($client['contact']) < 13 && is_numeric($client['contact']) ){

            $telephone = $client['contact'];

            if ($telephone[0] == 0 && ($telephone[1] == 6 || $telephone[1] == 7)){
                $telephone = substr_replace(str_replace(' ','',$telephone),"+33",0,1);
            }
            elseif ($telephone[0] == 3 && $telephone[1] == 3 ){
                $telephone = str_replace('33','+33',$telephone);
            }
            elseif ( ! ($telephone[0] == '+' && $telephone[1] == 3 && $telephone[2] == 3)){
                return new JsonResponse([
                    "message" => "Le téléphone est incorrect",
                    "erreur" => 4
                ],200);
            }

            if ( !empty($entityManager->getRepository(Client::class)->countTelephone($telephone))){
                // On retourne un message decrivant l'erreur et un code erreur : 2
                return new JsonResponse([
                    "message" => "Ce téléphone existe déjà",
                    "erreur" => 2
                ],200);
            }

            $clientEntite->setTelephone($telephone);

        }
        else{
            // On retourne un message decrivant l'erreur et un code erreur : 3
            return new JsonResponse([
                "message" => "Le contact est ni un mail, ni un téléphone",
                "erreur" => 3
            ],200);
        }

        $clientEntite->setNom($client['nom']);
        $clientEntite->setPassword(random_bytes(10));

        $entityManager->persist($clientEntite);
        $entityManager->flush();



        // On retourne un tableau avec les reponses de l'api un code http 200, sous forme de json
        return new JsonResponse([
            "client"=>$client,
            "message"=> "Le contact a bien été ajouté",
            "erreur" => 0
        ],200,['Access-Control-Allow-Origin: *']);
    }

    /**
     * @Route("/contact", name="api_contact", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function contact(Request $request, ManagerRegistry $doctrine): JsonResponse
    {

        // On recupere les elements venant de la requete
        $contact = $request->query->get('contact');

        if( !$contact ){
            // On retourne une erreur avec un code erreur http 400
            return new JsonResponse("Erreur dans le contact",400);
        }

        $contacts = array();
        $entityManager = $doctrine->getManager();

        $contacts += $entityManager->getRepository(Client::class)->rechercheContactNom($contact);

        $contacts += $entityManager->getRepository(Client::class)->rechercheContactMail($contact);

        if ($contact[0] == 0 && ($contact[1] == 6 || $contact[1] == 7)){
            $contact = substr_replace(str_replace(' ','',$contact),"33",0,1);
        }
        $contacts += $entityManager->getRepository(Client::class)->rechercheContactTelephone($contact);


        // On retourne un tableau avec les reponses de l'api un code http 200, sous forme de json
        return new JsonResponse([
            "contacts"=>$contacts,
            "contact"=>$contact
        ],200,['Access-Control-Allow-Origin: *']);

    }

    /**
     * @Route("/pre_reservation_client", name="api_pre_reservation_client", methods={"GET"})
     */
    public function preReservationClient(Request $request, ManagerRegistry $doctrine): JsonResponse
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

        // Permet de confirmer les param d'entree de la requete
        $retourReservation = [
            "nombrePlace"=>$nombrePlace,
            "dateArrivee"=>$dateArrivee,
            "dateDepart"=>$dateDepart
        ];
        $dateArrivee = new \DateTime($dateArrivee);
        $dateDepart = new \DateTime($dateDepart);


        if( $dateDepart < $dateArrivee || new \DateTime() > $dateArrivee ){
            // On retourne une erreur avec un code erreur http 400
            return new JsonResponse("Erreur dans les dates",400);
        }

        // On cree une reservation et verifie sa disponibilite
        $reservation = new Reservation();
        $reservation->setDateArrivee($dateArrivee);
        $reservation->setDateDepart($dateDepart);
        $reservation->setNombrePlace($nombrePlace);
        $entityManager = $doctrine->getManager();
        if($disponible = $reservation->VerificationDisponibilites($entityManager) >= 0);

        // On retourne un tableau avec les reponses de l'api un code http 200, sous forme de json
        return new JsonResponse([
            "prix"=>$reservation->getPrix(),
            "reservation" => $retourReservation,
            "disponible"=>$disponible,

        ],200,['Access-Control-Allow-Origin: *']);
    }

    /**
     * @Route("/pre_reservation", name="api_pre_reservation", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     */
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

        // Permet de confirmer les param d'entree de la requete
        $retourReservation = [
                "nombrePlace"=>$nombrePlace,
                "dateArrivee"=>$dateArrivee,
                "dateDepart"=>$dateDepart
            ];
        $dateArrivee = new \DateTime($dateArrivee);
        $dateDepart = new \DateTime($dateDepart);

        if($dateDepart < $dateArrivee || new \DateTime() >= $dateArrivee ){
            // On retourne une erreur avec un code erreur http 400
            return new JsonResponse("Erreur dans les dates",400);
        }

        // On cree une reservation et verifie sa disponibilite
        $reservation = new Reservation();
        $reservation->setDateArrivee($dateArrivee);
        $reservation->setDateDepart($dateDepart);
        $reservation->setNombrePlace($nombrePlace);
        $entityManager = $doctrine->getManager();
        $nombrePlaceDisponible = $reservation->VerificationDisponibilites($entityManager);

        // On retourne un tableau avec les reponses de l'api un code http 200, sous forme de json
        return new JsonResponse([
            "prix"=>$reservation->getPrix(),
            "reservation" => $retourReservation,
            "nombrePlaceDisponible"=>$nombrePlaceDisponible,

        ],200,['Access-Control-Allow-Origin: *']);
    }

    /**
     * @Route("/planning", name="api_planning", methods={"GET"})
     */
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
