<?php

namespace App\Controller;

use App\Entity\TransfertBdd;
use App\Entity\Reservation;
use App\Entity\Date;
use App\Form\TransfertBddType;
use App\Form\PlanningJourType;
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
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
        ]);
    }

    #[Route('/planning', name: 'app_admin_planning')]
    public function planning(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        $dateBoucle = new \DateTime();
        $dateBoucle->sub(new \DateInterval("P2D"));
        $dates = array();

        for($i = 0 ; $i < 367 ; $i++){
            $nombrePlaceDisponibles = $entityManager->getRepository(Date::class)->SelectorCreate($dateBoucle);
            $dates[$dateBoucle->format('Y-m-d')]['nombrePlaceDisponibles'] = $nombrePlaceDisponibles->NombrePlaceDisponibles($entityManager);
            $dates[$dateBoucle->format('Y-m-d')]['Depart'] = $entityManager->getRepository(Reservation::class)->CountDepart($dateBoucle);
            $dates[$dateBoucle->format('Y-m-d')]['Arrivee'] = $entityManager->getRepository(Reservation::class)->CountArrivee($dateBoucle);
            $dateBoucle->add(new \DateInterval("P1D"));
        }

        return $this->render('admin/planning.html.twig', ['dates'=>$dates
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
}
