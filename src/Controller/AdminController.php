<?php

namespace App\Controller;

use App\Entity\TransfertBdd;
use App\Entity\Reservation;
use App\Entity\Date;
use App\Form\TransfertBddType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
            $dates[$dateBoucle->format('Y-m-d')]['Depart'] = $entityManager->getRepository(Reservation::class)->CountDepart($dateBoucle);
            $dates[$dateBoucle->format('Y-m-d')]['Arrivee'] = $entityManager->getRepository(Reservation::class)->CountArrivee($dateBoucle);
            $dateBoucle->add(new \DateInterval("P1D"));
        }

        return $this->render('admin/planning.html.twig', ['dates'=>$dates
        ]);
    }

    #[Route('/planning_jour',requirements:['date'=> '\d*{2}-\d*{2}-\d*{4}'], name: 'app_admin_planning_jour')]
    public function planningJour(ManagerRegistry $doctrine,string $date = '2020-07-27'): Response
    {
        $entityManager = $doctrine->getManager();
        $reservations = $entityManager->getRepository(Reservation::class)->FindOneBy(array("DateArrivee"=>new \DateTime($date)));
        dump($reservations->getDates());
        exit();

        return $this->render('admin/planningjour.html.twig', [
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
