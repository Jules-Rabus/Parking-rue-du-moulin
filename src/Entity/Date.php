<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\DateRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: DateRepository::class)]
#[ApiResource(
    collectionOperations: ['get' => ['normalization_context' => ['groups' => 'date:read']]],
    itemOperations: ['get' => ['normalization_context' => ['groups' => 'date:read']]],
)]
class Date
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['date:read'])]
    private $id;

    #[ORM\Column(type: 'date', unique: false)]
    #[Groups(['date:read'])]
    private $Date;

    #[ORM\ManyToMany(targetEntity: Reservation::class, inversedBy: 'dates')]
    #[Groups(['date:read'])]
    private $relation;

    public function __construct()
    {
        $this->Reservations = new ArrayCollection();
        $this->relation = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->Date;
    }

    public function setDate(\DateTimeInterface $Date): self
    {
        $this->Date = $Date;

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getRelation(): Collection
    {
        return $this->relation;
    }

    public function addRelation(Reservation $relation): self
    {
        if (!$this->relation->contains($relation)) {
            $this->relation[] = $relation;
        }

        return $this;
    }

    public function removeRelation(Reservation $relation): self
    {
        $this->relation->removeElement($relation);

        return $this;
    }

    public function AjoutDates(  \Datetime $dateDebut, \DateTime $dateFin, $entityManager){

        // Nouveau datetime pour eviter la modification de l'ancien objet au cours de la boucle
        $dateBoucle = new \DateTime($dateDebut->format('Y-m-d'));
        $duree = $dateDebut->diff($dateFin)->days+1;

        for($i = 0 ; $i < $duree; $i++){

            $date = $entityManager->getRepository(Date::class)->selectIfExists($dateBoucle);

            if(!$date){
                $date = new Date();
                $date->setDate($dateBoucle);
                $entityManager->persist($date);
                $entityManager->flush();
            }

            $dateBoucle = new \DateTime(($dateBoucle->add(new \DateInterval("P1D"))->format('Y-m-d')));

        }

    }


    #[Groups(['date:read'])]
    public function getNombrePlaceDisponibles() : int{

        // on recupere toutes les reservations en lien avec cette date
        $reservations = $this->getRelation()->getValues();
        $nombrePlace = 40;

        // on enleve au nombre de place, le nombre de vehicule par reservation
        foreach ($reservations as $reservation){
            $nombrePlace -= $reservation->getNombrePlace();
        }

        return $nombrePlace;
    }

    #[Groups(['date:read'])]
    public function getNombreDepart() : int{

        // on recupere toutes les reservations en lien avec cette date
        $reservations = $this->getRelation()->getValues();
        $nombreDepart = 0;

        // On boucle afin de recuperer chaque vehicule qui part ce jour là
        foreach ($reservations as $reservation) {
            if ($reservation->getDateDepart() == $this->getDate()){
                $nombreDepart += $reservation->getNombrePlace();
            }
        }

        return $nombreDepart;
    }

    #[Groups(['date:read'])]
    public function getNombreArrivee() : int{

        // on recupere toutes les reservations en lien avec cette date
        $reservations = $this->getRelation()->getValues();
        $nombreArrivee = 0;


        // On boucle afin de recuperer chaque vehicule qui arrive ce jour là
        foreach ($reservations as $reservation) {
            if ($reservation->getDateArrivee() == $this->getDate()){
                $nombreArrivee += $reservation->getNombrePlace();
            }
        }

        return $nombreArrivee;
    }

    // Cette fonction me sert uniquement dans l'api afin de masquer le nombre de vehicule sur le parking
    public function getnombrePlaceCategorie() : int{

        $nombrePlace = $this->getNombrePlaceDisponibles();

        if($nombrePlace > 20){
            return 3;
        }
        if($nombrePlace > 10){
            return 2;
        }
        if($nombrePlace > 5){
            return 1;
        }
        return 0;

    }
    

}