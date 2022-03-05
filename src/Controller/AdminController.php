<?php

namespace App\Controller;

use App\Entity\TransfertBdd;
use App\Form\TransfertBddType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\Persistence\ManagerRegistry;

#[Route('/admin', name: 'admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/transfertbdd', name: 'app_admin_transfertbdd')]
    public function transfert_bdd(Request $request, SluggerInterface $slugger,ManagerRegistry $doctrine): Response
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
            $transfertbdd->setAdminId($this->security->getUser()->getId());

            $entityManager = $doctrine->getManager();
            $entityManager->persist($transfertbdd);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_transfertbdd');
        }

        return $this->renderForm('admin/transfertbdd.html.twig', [
            'form' => $form,
        ]);
    }
}
