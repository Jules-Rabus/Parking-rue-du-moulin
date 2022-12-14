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

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin')]
    public function index(Request $request,ManagerRegistry $doctrine, MailerInterface $mailer ): Response
    {
        // On creer le formulaire de reservation
        $entityManager = $doctrine->getManager();
        $reservation = new Reservation();
        $form = $this->createForm(ReservationType::class,$reservation);
        $form->handleRequest($request);
        $formError = null;
        $formPrix = null;


        // On verifie que le formulaire est envoyee et valide
        if ($form->isSubmitted() && $form->isValid()) {

            // On recupere/genere le code pour ces dates
            $code = $entityManager->getRepository(Code::class)->SelectOrCreate($reservation->getDateArrivee(),$reservation->getDateDepart(),$mailer);


            // On cree la reservation
            $reservation->setDateReservation(new \DateTime());
            $reservation->AjoutDates($entityManager);
            $reservation->setCodeAcces($code);
            $formPrix = $reservation->getPrix();

            // On verifie que le parking n'est pas plein
            if($reservation->VerificationDisponibilites($entityManager) != -1){
                if($reservation->getDateDepart() >= $reservation->getDateArrivee()){
                    $entityManager->persist($reservation);
                    $entityManager->flush();
                }
                else{
                    $formError = "Les dates ne sont pas correctes";
                }
            }
            else{
                $formError = "Il n'y a pas de place pour ces dates";
            }
            return $this->redirectToRoute('app_admin_message',['reservation'=>$reservation->getId()]);
        }

        return $this->renderForm('admin/index.html.twig', ['form'=>$form,'formError'=>$formError,'formPrix'=>$formPrix
        ]);
    }

    /**
     * @Route("/planning/{nombre_jours}", name="app_admin_planning")
     */
    public function planning(ManagerRegistry $doctrine, Request $request, int $nombre_jours = 0): Response
    {

        $form = $this->createForm(PlanningJourType::class,NULL);
        $form->handleRequest($request);

        // On initialise les variables afin de creer la boucle et stocker les resultats

        // planning
        $entityManager = $doctrine->getManager();
        $aujourdhui = new \DateTime();
        $dateBoucle = new \DateTime('first day of this month');
        $dates = array();

        // On test pour revenir en arriere sur le planning
        if($nombre_jours < 0){
            $dateRetour = new \DateTime();
            $dateRetour->sub(new \DateInterval("P" . -$nombre_jours . "D"));

            // par d??faut on affiche a partir du debut du mois
            if($dateBoucle > $dateRetour){
                $dateBoucle = $dateRetour;
            }
        }
        else if ($nombre_jours > 0){
            $dateRetour = new \DateTime();
            $dateRetour->add(new \DateInterval("P" . $nombre_jours+1 . "D"));
            if($dateBoucle < $dateRetour){
                $dateBoucle = $dateRetour;
            }
        }

        // planning rapide
        $planningRapide = array();
        $planningRapideDateDebut = new \DateTime('first day of this month');
        $planningRapideDateFin = (clone $dateBoucle)->modify("first day of this month")->modify("+6 month");
        $nombrePlaceDisponibleMin = 40;

        // statistique
        $statistique = new Statistique($entityManager);

        if ($form->isSubmitted() && $form->isValid()) {

            $diff = $aujourdhui->diff($form->getData()['date']);
            $nombre_jours = $diff->days;
            if($diff->invert) $nombre_jours = -$diff->days;
            return $this->redirectToRoute('app_admin_planning',['nombre_jours'=>$nombre_jours]);
        }

        for($i = 0 ; $i < 365 ; $i++) {
            $dateEntite = $entityManager->getRepository(Date::class)->SelectorCreate($dateBoucle);
            $nombrePlaceDisponible = $dateEntite->getNombrePlaceDisponibles();
            $dates[$dateBoucle->format('Y-m-d')]['nombrePlaceDisponibles'] = $nombrePlaceDisponible;
            $dates[$dateBoucle->format('Y-m-d')]['Depart'] = $dateEntite->getnombreDepart();
            $dates[$dateBoucle->format('Y-m-d')]['Arrivee'] = $dateEntite->getnombreArrivee();

            // planning rapide

            // On verifie si la date ne depasse pas 6 mois
            if($dateBoucle < $planningRapideDateFin && $dateBoucle > $planningRapideDateDebut){

                // On actualise si necessaire le nombre de place disponible min
                if ($nombrePlaceDisponibleMin > $nombrePlaceDisponible)$nombrePlaceDisponibleMin = $nombrePlaceDisponible;

                // On clone la date de boucle et rajoute un jour
                $dateTest = (clone $dateBoucle)->modify('+1 day');
                $dateTestSemaineCourte = (clone $dateBoucle)->modify('last day of this month');

                // A chaque debut de mois ou de debut de semaine (L-D), on enregistre
                if( $dateBoucle->format('W') != $dateTest->format('W') && $dateTestSemaineCourte->diff($dateBoucle)->days > 2
                    || $dateBoucle->format('m') != $dateTest->format('m')
                ) {
                    // On enregistre le nombre de place disponible minimun pour cette date
                    $planningRapide[$dateBoucle->format('M')][$dateBoucle->format('d')] = $nombrePlaceDisponibleMin;
                    $nombrePlaceDisponibleMin = 40;
                }
            }

            $dateBoucle->modify('+1 day');
        }


        return $this->renderForm('admin/planning.html.twig', ['form'=>$form,'dates'=>$dates,'date'=>$aujourdhui,'statistique'=>$statistique,'planningRapide'=>$planningRapide
        ]);
    }

    /**
     * @Route("/planning_jour/{date}", name="app_admin_planning_jour")
     * @ParamConverter("date", options={"format": "Y-m-d"})
     */
    public function planningJour(Request $request,ManagerRegistry $doctrine,\DateTime $date, MailerInterface $mailer): Response
    {
        //On creer le formulaire pour aller ?? la date du planning voulu
        $form = $this->createForm(PlanningJourType::class,NULL,['date'=>$date]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // On redirige vers la date voulu
            return $this->redirectToRoute('app_admin_planning_jour',['date'=>$form->getData()['date']->format('Y-m-d')]);
        }

        //On initialise les variables afin recuperer toutes les informations de cette journee sur le parking
        $entityManager = $doctrine->getManager();
        $arrivees = $entityManager->getRepository(Reservation::class)->FindBy(array("DateArrivee"=>$date),array("DateDepart"=>"ASC"));
        $departs = $entityManager->getRepository(Reservation::class)->FindBy(array("DateDepart"=>$date),array("DateArrivee"=>"ASC"));
        $dateEntite = $entityManager->getRepository(Date::class)->SelectorCreate($date);
        $voitures = $dateEntite->getRelation()->getValues();
        $numeroPlaceDispo = $dateEntite->getNumeroPlaceDispo();
        $nombrePlaceDisponibles = $dateEntite->getNombrePlaceDisponibles();
        $nbrArrivee = $dateEntite->getnombreArrivee();
        $nbrDepart =  $dateEntite->getnombreDepart();
        $aujourdhui = new \DateTime();

        // On rajoute la possibilite donner le code
        $arriveesTemplate = [];

        foreach ($arrivees as $key => $reservation){
            $nombreReservation = $reservation->getClient()->getNombreReservation();
            $messageEntite = new Message($reservation,$nombreReservation,$mailer,true);
            $messageEntite->messageCode();
            $code = $messageEntite->getMessageTelephone();
            $arriveesTemplate[$key] = [
                'entite'=> $reservation,
                'code'=> $code
            ];
        }

        return $this->renderForm('admin/planningjour.html.twig', ['form'=>$form,'date'=>$date,'aujourdhui'=>$aujourdhui,'arrivees'=>$arriveesTemplate,'departs'=>$departs,
            "nombrePlaceDisponibles"=>$nombrePlaceDisponibles,'voitures'=>$voitures,'nbrArrivee'=>$nbrArrivee,'nbrDepart'=>$nbrDepart,'numeroPlaceDispo' =>$numeroPlaceDispo
        ]);
    }

    #[Route('/transfertbdd', name: 'app_admin_transfertbdd')]
    public function transfertBdd(Request $request, SluggerInterface $slugger,ManagerRegistry $doctrine, MailerInterface $mailer ): Response
    {

        // on creer le formulaire pour le transfert de BDD
        $transfertbdd = new TransfertBdd();
        $formJson = $this->createForm(TransfertBddType::class, $transfertbdd);
        $formJson->handleRequest($request);

        $formSql = $this->createForm(TransfertBddSqlType::class, $transfertbdd);
        $formSql->handleRequest($request);

        if ($formSql->isSubmitted() && $formSql->isValid()) {

            /*

            if( $formSql->get('check')->getData()){

                $jsonFile = $formJson->get('json')->getData();

                //Remise a zero de la bdd
                $sql = $doctrine->getConnection('default');

                $requete = $sql->prepare("DELETE FROM reservation");
                $requete->execute();

                $requete = $sql->prepare("DELETE FROM date");
                $requete->execute();

                $requete = $sql->prepare("DELETE FROM client WHERE roles != '[\"ROLE_ADMIN\"]'");
                $requete->execute();

                $requete = $sql->prepare("DELETE FROM code");
                $requete->execute();

                $entityManager = $doctrine->getManager();

                $transfertbdd->setJsonFilename("Sql");

                // On recupere la connexion a l'ancienne bdd et fait le traitement
                $sqlOld = $doctrine->getConnection('old');
                $transfertbdd->TransfertBddSql($entityManager,$sqlOld,$mailer);

                $transfertbdd->setDate(new \DateTime());
                $transfertbdd->setRelation($this->getUser());

                // On inscrit dans la bdd le transfert
                $entityManager->persist($transfertbdd);
                $entityManager->flush();
            }
            */
        }

        // On verifie que le formulaire est envoye et valide
        if ($formJson->isSubmitted() && $formJson->isValid()) {

            /*
            $jsonFile = $formJson->get('json')->getData();

            // On verifie qu'on a bien le fichier json est non null
            if ($jsonFile) {
                $originalFilename = pathinfo($jsonFile->getClientOriginalName(), PATHINFO_FILENAME);
                // on securise le chemin du fichier
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$jsonFile->guessExtension();

                // On deplace le fichier dans la partie des fichiers JSON
                try {
                    $jsonFile->move(
                        $this->getParameter('json_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {

                }

                // On donne le nom du fichier ?? l'entite
                $transfertbdd->setJsonFilename($newFilename);

                //Remise a zero de la bdd
                $sql = $doctrine->getConnection('default');

                $requete = $sql->prepare("DELETE FROM reservation");
                $requete->execute();

                $requete = $sql->prepare("DELETE FROM date");
                $requete->execute();

                $requete = $sql->prepare("DELETE FROM client WHERE roles != '[\"ROLE_ADMIN\"]'");
                $requete->execute();

                $requete = $sql->prepare("DELETE FROM code");
                $requete->execute();

                $entityManager = $doctrine->getManager();

                $transfertbdd->setDate(new \DateTime());
                $transfertbdd->setRelation($this->getUser());

                // On effectue le traitement du fichier Json
                $transfertbdd->TransfertBddJson($entityManager,$mailer);

                // On inscrit dans la bdd le transfert
                $entityManager->persist($transfertbdd);
                $entityManager->flush();

            }
            */

            // On recupere les clients en double
            $entityManager = $doctrine->getManager();
            $sql = $doctrine->getConnection('default');
            $requete = $sql->prepare("SELECT *,count(*) FROM client WHERE telephone IN (SELECT DISTINCT telephone FROM client WHERE telephone) GROUP BY telephone HAVING count(*) > 1");
            $requete = $requete->execute();
            $resultats = $requete->fetchAllAssociative();

            foreach ($resultats as $resultat){

                // On recupere les clients un par un
                $requete = $sql->prepare("SELECT * FROM client WHERE telephone = :tel");
                $requete->bindValue("tel", $resultat['telephone']);
                $requete = $requete->execute();
                $resultats2 = $requete->fetchAllAssociative();
                $clients = array();

                // On recupere l'entite de chaque client
                foreach ($resultats2 as $resultat2){
                    $clients[] = $entityManager->getRepository(Client::class)->Find($resultat2['id']);
                }
                $maxReservation = $clients[0]->getId();

                // On cherche quel client a le plus de reservation
                foreach ($clients as $client){
                    $nombreReservation = $client->getNombreReservation();
                    if($nombreReservation> $maxReservation){
                        $maxReservation = $client->getId();
                    }
                }

                // On recupere le client auquel on va rajouter les reservations en double
                $clientRajout = $entityManager->getRepository(Client::class)->Find($maxReservation);

                foreach($clients as $client){

                    // On traite le tout afin de supprimer les doublons
                    if($client->getId() != $maxReservation){
                        $reservations = $client->getReservations()->getValues();

                        // On supprime et rajoute chaque reservation
                        foreach ($reservations as $reservation){
                            $reservation->setTelephone(null);
                            $clientRajout->addReservation($reservation);
                            $client->removeReservation($reservation);
                        }

                        // on supprime le client et mets a jour dans la bdd
                        $entityManager->remove($client);
                        $entityManager->flush();

                    }
                }
            }

        }

        return $this->renderForm('admin/transfertbdd.html.twig', [
            'formJson' => $formJson,
            'formSql' => $formSql,
            'transfertbdd' => $this->getDoctrine()->getRepository(TransfertBdd::class)->findAll()
        ]);
    }

    #[Route('/client/{client}', name: 'app_admin_client')]
    public function client(Request $request, ManagerRegistry $doctrine, Client $client, MailerInterface $mailer): Response
    {
        $reservationsTemplates = [];
        $aujourdhui = new \DateTime();
        $entityManager = $doctrine->getManager();
        $form = $this->createForm(ReservationModificationType::class,NULL);
        $form->handleRequest($request);

        // On verifie que le formulaire est envoyee et valide
        if ($form->isSubmitted() && $form->isValid()) {

            // On verifie si le bouton annule est presse
            if($form->get('Annuler')->isClicked()){

                $reservation = $entityManager->getRepository(Reservation::class)->find($form->getData()['id']);
                $entityManager->remove($reservation);
                $entityManager->flush();
            }

            // On verifie si le bouton modifier est presse
            if($form->get('Modifier')->isClicked()) {

                $formData = $form->getData();

                // On traite les informations venant du formulaire
                $reservationId = $formData['id'];
                $reservation = $entityManager->getRepository(Reservation::class)->find($reservationId);
                $reservation->setDateArrivee($formData['DateArrivee']);
                $reservation->setDateDepart($formData['DateDepart']);
                $reservation->setNombrePlace($formData['NombrePlace']);
                $reservation->AjoutDates($entityManager,true);

                // On modifie la reservation si elle est possible et correcte
                if($reservation->VerificationDisponibilites($entityManager,true) != -1) {
                    if ($reservation->getDateDepart() >= $reservation->getDateArrivee()) {

                        // On remodifie le code avec les bonnes dates
                        $code = $entityManager->getRepository(Code::class)->SelectOrCreate($reservation->getDateArrivee(),$reservation->getDateDepart(),$mailer);
                        $reservation->setCodeAcces($code);
                        $reservation->setCodeDonne(false);

                        $entityManager->persist($reservation);
                        $entityManager->flush();
                    }
                }
                return $this->redirect($this->generateUrl('app_admin_client',['client' => $client->getId() ]) . "#" . $reservationId);

            }
        }

        // On recupere les reservations triees : passe, present, futur
        $reservationsTri = $client->getReservationsTri();
        $nombreReservation = $client->getNombreReservation();

        // On cree un formulaire pour chaque reservation
        foreach ($reservationsTri as $key => $reservations){
            if($key != "passe"){
                $reservationsTemplates[$key] = [];
                foreach ($reservations as $reservation){
                    $messageEntite = new Message($reservation,$nombreReservation,$mailer,true);
                    $message = $messageEntite->messageIntelligent();
                    $messageEntite->messageCode();
                    $code = $messageEntite->getMessageTelephone();
                    $reservationsTemplates[$key][] = [
                        'form'=>$this->createForm(ReservationModificationType::class, $reservation)->createView(),
                        'entite'=> $reservation,
                        'code'=> $code,
                        'message'=> $message] ;
                }
            }
            else{
                $reservationsTemplates[$key] = $reservations;
            }

        }

        return $this->renderForm('admin/client.html.twig',['client'=>$client,'reservations'=>$reservationsTemplates,
            'aujourdhui'=>$aujourdhui]);

    }

    #[Route('/message/{reservation}', name: 'app_admin_message')]
    public function message(Request $request, ManagerRegistry $doctrine, Reservation $reservation, MailerInterface $mailer): Response
    {

        // On cree le formulaire des messages
        $entityManager = $doctrine->getManager();
        $form = $this->createForm(MessageType::class);
        $form->handleRequest($request);

        // On cree le message et $reservation vient de l'url
        $message = new Message($reservation,$reservation->NombreReservation($entityManager),$mailer);

        if ($form->isSubmitted() && $form->isValid()) {

            return $this->renderForm('admin/message.html.twig',['form'=>$form,
                'message'=>$message,'messageRetour'=>$message->traitementFormulaire($form->getData(),$doctrine)]);
        }

        return $this->renderForm('admin/message.html.twig',['form'=>$form,
            'message'=>$message,'messageRetour'=>null]);
    }

    #[Route('/statistique', name: 'app_admin_statistique')]
    public function statistique(ManagerRegistry $doctrine) : Response
    {
        $entityManager = $doctrine->getManager();
        $statisque = new Statistique($entityManager);
        $date = (new \DateTime())->modify('last day of january');
        $statisquesDate = array();

        for($i = 0; $i <12; $i++){
            $key = $date->format('Y-m-d');
            $statisquesDate[$key]['moyen']['vehicule'] = round($statisque->getVehiculeMoisMoyenne($date),1);
            $statisquesDate[$key]['moyen']['duree'] = round($statisque->getDureeMoisMoyenne($date),1);
            $statisquesDate[$key]['moyen']['recette'] = round($statisque->getRecetteMoisMoyenne($date),1);

            $statisquesDate[$key]['present']['vehicule'] = round($statisque->getVehiculeMois($date),1);
            $statisquesDate[$key]['present']['duree'] = round($statisque->getDureeMois($date),1);
            $statisquesDate[$key]['present']['recette'] = round($statisque->getRecetteMois($date),1);

            $statisquesDate[$key]['meilleur']['vehicule'] = $statisque->getVehiculeMoisMeilleur($date);
            $statisquesDate[$key]['meilleur']['duree'] = $statisque->getDureeMoisMeilleur($date);
            $statisquesDate[$key]['meilleur']['recette'] = $statisque->getRecetteMoisMeilleur($date);

            $dateAvant = (clone $date)->modify('-1 year');

            $statisquesDate[$key]['precedent']['vehicule'] = round($statisque->getVehiculeMois($dateAvant),1);
            $statisquesDate[$key]['precedent']['duree'] = round($statisque->getDureeMois($dateAvant),1);
            $statisquesDate[$key]['precedent']['recette'] = round($statisque->getRecetteMois($dateAvant),1);

            $date->modify("first day of next month");

        }

        $statisques['nombreReservationMaxClient'] = $statisque->getNombreReservationClientMax();
        $statisques['nombreReservation'] = $statisque->getNombreReservation();
        $statisques['recetteTotal'] = $statisque->getRecetteTotal();
        $statisques['recetteAnneeMoyen'] = round($statisque->getRecetteAnneeMoyen(),1);
        $statisques['recetteMoisMoyen'] = round($statisque->getRecetteMoisMoyen(),1);

        return $this->render('admin/statistique.html.twig',['statistiquesDate'=>$statisquesDate,'statistiques'=>$statisques]);

    }

    #[Route('/code', name: 'app_admin_code')]
    public function code(ManagerRegistry $doctrine) : Response
    {
        $codes = $this->getDoctrine()->getRepository(Code::class)->findAll();

        // Tableau avec les codes triees
        $codesTri = array('futur'=>[],'passe'=>[],'present'=>[]);
        $aujourdhui = new \DateTime();

        // Tri des codes
        foreach ($codes as $code) {
            if ($code->getDateDebut() > $aujourdhui) {
                $codesTri['futur'][] = $code;
            } else if ($code->getDateFin() < $aujourdhui) {
                $codesTri['passe'][] = $code;
            } else {
                $codesTri['present'][] = $code;
            }
        }


        return $this->render('admin/code.html.twig',['codes'=>$codesTri]);
    }



}
