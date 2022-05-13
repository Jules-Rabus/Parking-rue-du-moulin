<?php

namespace App\Entity;

use App\Repository\TransfertBddRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Finder\Finder;
use App\Entity\Client;
use App\Entity\Reservation;
use App\Entity\Date;
use App\Entity\Code;


#[ORM\Entity(repositoryClass: TransfertBddRepository::class)]
class TransfertBdd
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'date', nullable: false)]
    private $date;

    #[ORM\Column(type: 'string', nullable: false)]
    private $jsonFilename;

    #[ORM\ManyToOne(targetEntity: Client::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $relation;
    

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getJsonFilename()
    {
        return $this->jsonFilename;
    }

    public function setJsonFilename($jsonFilename)
    {
        $this->jsonFilename = $jsonFilename;

        return $this;
    }

    public function getRelation(): ?Client
    {
        return $this->relation;
    }

    public function setRelation(?Client $relation): self
    {
        $this->relation = $relation;

        return $this;
    }

    // traitement du fichier json
    public function TransfertBdd($entityManager)
    {

        // On recupere le fichier json
        $finder = new Finder();
        $finder->files()->in('/home/clients/f0d77ebce5440fda32259bef2b47eddc/sites/dev.parking-rue-du-moulin.fr/recup/public/uploads/json');
        $finder->files()->name($this->jsonFilename);

        $contents = array();


        // on decode chaque ligne du fichier JSON qui correspond a une reservation
        foreach ($finder as $file) {
            $file->getContents();
            $contents += json_decode($file->getContents());
        }

        // On effectue le traitement pour chaque reservation
        foreach ($contents as $content) {

            // On cree une entite client et reserfation
            $client = new Client();
            $reservation = new Reservation();

            //Traitement pour les réservations faites par mail ou téléphone

            if (filter_var($content->contact, FILTER_VALIDATE_EMAIL) && !empty($content->contact)) {

                // Si le contact est un mail

                // On verifie que le client n'a pas deja ete enregistrer dans la BDD
                if (! $entityManager->getRepository(Client::class)->countEmail($content->contact)) {

                    // On remplit les informations du client

                    $client->setEmail($content->contact);
                    $client->setNom($content->nom);

                    //Le client devra faire mdp oublié pour avoir son mdp
                    $password = str_replace(' ', '', $content->nom . $content->date);
                    $client->setPassword($password);

                    $entityManager->persist($client);
                    $entityManager->flush();
                }
                else{
                    // On recupere le client car il est deja dans la BDD
                    $client = $entityManager->getRepository(Client::class)->FindOneBy(array("email" => $content->contact));
                }
            }
            elseif( !empty($content->contact)){

                // Si le contact est un telephone

                // On verifie que le client n'a pas deja ete enregistrer dans la BDD
                if (! $entityManager->getRepository(Client::class)->countTelephone($content->contact)) {

                    // On remplit les informations du client

                    $client->setTelephone($content->contact);
                    $client->setNom($content->nom);

                    //Le client devra faire mdp oublié pour avoir son mdp
                    $password = str_replace(' ', '', $content->nom . $content->date);
                    $client->setPassword($password);

                    $entityManager->persist($client);
                    $entityManager->flush();
                }
                else{
                    // On recupere le client car il est deja dans la BDD
                    $client = $entityManager->getRepository(Client::class)->FindOneBy(array("telephone" => $content->contact));
                }
            }
            $reservation->setClient($client);

            // Traitement pour les codes

            // On regarde si le code a deja ete creer
            if( !$entityManager->getRepository(Code::class)->countCode($content->code)){
                // On creer un nouveau code dans la bdd
                $code = New Code();
                $code->setCode($content->code);
                $entityManager->persist($code);
            }
            else{
                // On recupere le code deja existant dans la BDD
                $code = $entityManager->getRepository(Code::class)->FindOneBy(array("Code" => $content->code));
            }
            $reservation->setCodeAcces($code);

            $reservation->setTelephone($content->contact);
            $reservation->setNombrePlace($content->place);

            //Traitement des dates de reservations
            $dateArrivee = new \DateTime($content->date);
            $reservation->setDateArrivee($dateArrivee);
            $dateDepart = new \DateTime($content->datef);
            $reservation->setDateDepart($dateDepart);
            $reservation->AjoutDates($entityManager);
            $dateReservation = new \DateTime($content->date_reservation);
            $reservation->setDateReservation($dateReservation);

            // Si le client est deja arrivee sur le parking alors il a forcement eu son code
            if($dateArrivee < new \DateTime()){
                $reservation->setCodeDonne(true);
            }

            $entityManager->persist($reservation);
            $entityManager->flush();
        }

    }

}
