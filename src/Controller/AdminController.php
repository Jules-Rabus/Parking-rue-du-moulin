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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\Persistence\ManagerRegistry;

#[Route('/admin_old')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin')]
    public function index(Request $request,ManagerRegistry $doctrine): Response
    {

        $entityManager = $doctrine->getManager();
        $reservation = new Reservation();
        $form = $this->createForm(ReservationType::class,$reservation);
        $form->handleRequest($request);
        $formError = null;
        $formPrix = null;


        if ($form->isSubmitted() && $form->isValid()) {

            $code = $entityManager->getRepository(Code::class)->SelectOrCreate($reservation->getDateArrivee(),$reservation->getDateDepart());

            $reservation->setDateReservation(new \DateTime());
            $reservation->AjoutDates($entityManager);
            $reservation->setCodeAcces($code);
            $formPrix = $reservation->Prix();

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
        }

        return $this->renderForm('admin/index.html.twig', ['form'=>$form,'formError'=>$formError,'formPrix'=>$formPrix
        ]);
    }

    #[Route('/planning', name: 'app_admin_planning')]
    public function planning(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        $dateBoucle = new \DateTime();
        $date = new \DateTime();
        $dateBoucle->sub(new \DateInterval("P2D"));
        $dates = array();

        for($i = 0 ; $i < 367 ; $i++){
            $dateEntite = $entityManager->getRepository(Date::class)->SelectorCreate($dateBoucle);
            $dates[$dateBoucle->format('Y-m-d')]['nombrePlaceDisponibles'] = $dateEntite->NombrePlaceDisponibles($entityManager);
            $dates[$dateBoucle->format('Y-m-d')]['Depart'] = $dateEntite->nombreDepart($entityManager);
            $dates[$dateBoucle->format('Y-m-d')]['Arrivee'] = $dateEntite->nombreArrivee($entityManager);
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
        $form = $this->createForm(PlanningJourType::class,NULL,['date'=>$date]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            return $this->redirectToRoute('app_admin_planning_jour',['date'=>$form->getData()['date']->format('Y-m-d')]);
        }

        $entityManager = $doctrine->getManager();
        $arrivees = $entityManager->getRepository(Reservation::class)->FindBy(array("DateArrivee"=>$date));
        $departs = $entityManager->getRepository(Reservation::class)->FindBy(array("DateDepart"=>$date));
        $dateEntite = $entityManager->getRepository(Date::class)->SelectorCreate($date);
        $voitures = $dateEntite->getRelation()->getValues();
        $nombrePlaceDisponibles = $dateEntite->NombrePlaceDisponibles($entityManager);

        return $this->renderForm('admin/planningjour.html.twig', ['form'=>$form,'date'=>$date,'arrivees'=>$arrivees,'departs'=>$departs,
            "nombrePlaceDisponibles"=>$nombrePlaceDisponibles,'voitures'=>$voitures
        ]);
    }

    #[Route('/transfertbdd', name: 'app_admin_transfertbdd')]
    public function transfertBdd(Request $request, SluggerInterface $slugger,ManagerRegistry $doctrine): Response
    {
        $transfertbdd = new TransfertBdd();
        $form = $this->createForm(TransfertBddType::class, $transfertbdd);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $jsonFile = $form->get('json')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($jsonFile) {
                $originalFilename = pathinfo($jsonFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$jsonFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $jsonFile->move(
                        $this->getParameter('json_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $transfertbdd->setJsonFilename($newFilename);
            }

            $transfertbdd->setDate(new \DateTime());
            $transfertbdd->setRelation($this->getUser());

            $entityManager = $doctrine->getManager();
            $entityManager->persist($transfertbdd);
            $transfertbdd->TransfertBdd($entityManager);
            $entityManager->flush();

            //return $this->redirectToRoute('app_admin_transfertbdd');
        }


        return $this->renderForm('admin/transfertbdd.html.twig', [
            'form' => $form,
            'transfertbdd' => $this->getDoctrine()->getRepository(TransfertBdd::class)->findAll()
        ]);
    }

    #[Route('/message/{contact}', name: 'app_admin_message')]
    public function message(ManagerRegistry $doctrine, string $contact): Response
    {
        $entityManager = $doctrine->getManager();
        $message = new Message();
        $message->heure();


        return $this->render('admin/message.html.twig');
    }

}
