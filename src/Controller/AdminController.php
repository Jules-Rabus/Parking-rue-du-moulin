<?php

namespace App\Controller;

use App\Entity\TransfertBdd;
use App\Entity\Reservation;
use App\Entity\Date;
use App\Entity\Code;
use App\Entity\Message;
use App\Form\TransfertBddType;
use App\Form\PlanningJourType;
use App\Form\ReservationType;
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
            if($reservation->VerificationDisponibilites($entityManager)){
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
    public function planning(ManagerRegistry $doctrine,int $nombre_jours = 2): Response
    {

        // On initialise les variables afin de creer la boucle et stocker les resultats
        $entityManager = $doctrine->getManager();
        $dateBoucle = new \DateTime();
        $date = new \DateTime();
        $dateInterval = "P" . $nombre_jours . "D";
        $dateBoucle->sub(new \DateInterval($dateInterval));
        $dates = array();

        // On fait qui demarre 2 jours avant la date actuelle et qui s'arrete un an apres
        for($i = -$nombre_jours ; $i < 367 ; $i++){
            $dateEntite = $entityManager->getRepository(Date::class)->SelectorCreate($dateBoucle);
            $dates[$dateBoucle->format('Y-m-d')]['nombrePlaceDisponibles'] = $dateEntite->getNombrePlaceDisponibles();
            $dates[$dateBoucle->format('Y-m-d')]['Depart'] = $dateEntite->getnombreDepart();
            $dates[$dateBoucle->format('Y-m-d')]['Arrivee'] = $dateEntite->getnombreArrivee();
            $dateBoucle->add(new \DateInterval("P1D"));
        }

        return $this->render('admin/planning.html.twig', ['dates'=>$dates,'date'=>$date
        ]);
    }

    /**
     * @Route("/planning_jour/{date}", name="app_admin_planning_jour")
     * @ParamConverter("date", options={"format": "Y-m-d"})
     */
    public function planningJour(Request $request,ManagerRegistry $doctrine,\DateTime $date): Response
    {

        //On creer le formulaire pour aller à la date du planning voulu
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
        $nombrePlaceDisponibles = $dateEntite->getNombrePlaceDisponibles();
        $nbrArrivee = $dateEntite->getnombreDepart();
        $nbrDepart =  $dateEntite->getnombreArrivee();
        $aujourdhui = new \DateTime();

        return $this->renderForm('admin/planningjour.html.twig', ['form'=>$form,'date'=>$date,'aujourdhui'=>$aujourdhui,'arrivees'=>$arrivees,'departs'=>$departs,
            "nombrePlaceDisponibles"=>$nombrePlaceDisponibles,'voitures'=>$voitures,'nbrArrivee'=>$nbrArrivee,'nbrDepart'=>$nbrDepart
        ]);
    }

    #[Route('/transfertbdd', name: 'app_admin_transfertbdd')]
    public function transfertBdd(Request $request, SluggerInterface $slugger,ManagerRegistry $doctrine): Response
    {

        // on creer le formulaire pour le transfert de BDD
        $transfertbdd = new TransfertBdd();
        $form = $this->createForm(TransfertBddType::class, $transfertbdd);
        $form->handleRequest($request);

        // On verifie que le formulaire est envoye et valide
        if ($form->isSubmitted() && $form->isValid()) {
            $jsonFile = $form->get('json')->getData();

            // On verifie qu'on a bien le fichier json non null
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

                // On donne le nom du fichier à l'entite
                $transfertbdd->setJsonFilename($newFilename);
            }


            $transfertbdd->setDate(new \DateTime());
            $transfertbdd->setRelation($this->getUser());

            $entityManager = $doctrine->getManager();
            $entityManager->persist($transfertbdd);

            // On effectue le traitement du fichier JSOn
            $transfertbdd->TransfertBdd($entityManager);

            $entityManager->flush();

            //return $this->redirectToRoute('app_admin_transfertbdd');
        }

        return $this->renderForm('admin/transfertbdd.html.twig', [
            'form' => $form,
            'transfertbdd' => $this->getDoctrine()->getRepository(TransfertBdd::class)->findAll()
        ]);
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
                'message'=>$message,'messageRetour'=>$message->TraitementFormulaire($form->getData(),$doctrine)]);
        }

        return $this->renderForm('admin/message.html.twig',['form'=>$form,
            'message'=>$message,'messageRetour'=>null]);
    }


}
