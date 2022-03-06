<?php

namespace App\Entity;

use App\Repository\TransfertBddRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Finder\Finder;
use App\Entity\Client;
use App\Entity\Reservation;


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

    public function TransfertBdd($entityManager)
    {

        $finder = new Finder();
        $finder->files()->in('/home/clients/f0d77ebce5440fda32259bef2b47eddc/sites/dev.parking-rue-du-moulin.fr/projetsymfony/public/uploads/json');
        $finder->files()->name($this->jsonFilename);

        $contents = array();

        foreach ($finder as $file) {

            $file->getContents();
            $contents += json_decode($file->getContents());
        }

        foreach ($contents as $content) {

            $client = new Client();
            $reservation = new Reservation();

            // On prends que les clients qui ont reservé avec leur mail
            if (filter_var($content->contact, FILTER_VALIDATE_EMAIL) && !empty($content->contact)) {

                if(!$entityManager->getRepository(Client::class)->countEmail($content->contact)){
                    $client->setEmail($content->contact);
                    $client->setNom($content->nom);
                    $password = str_replace(' ', '', $content->nom.$content->date);
                    $client->setPassword($password);
                    $entityManager->persist($client);
                    $entityManager->flush();
                }

                $client  = $entityManager->getRepository(Client::class)->FindBY(array("email"=>$content->contact));
                $reservation->setNombrePlace($content->place);
                $dateArrivee = new \DateTime($content->date);
                $reservation->setDateArrivee($dateArrivee);
                $dateDepart = new \DateTime($content->datef);
                $reservation->setDateDepart($dateDepart);
                $dateReservation = new \DateTime($content->date_reservation);
                $reservation->setDate($dateReservation);
                $reservation->setCodeAcces($content->code);
                $reservation->setClient(new Client ($client));

                $entityManager->persist($reservation);
            }
            $entityManager->flush();

        }

    }

}
