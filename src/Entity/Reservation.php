<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Date;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer')]
    private $NombrePlace;

    #[ORM\Column(type: 'date')]
    private $DateArrivee;

    #[ORM\Column(type: 'date')]
    private $DateDepart;

    #[ORM\Column(type: 'date')]
    private $DateReservation;

    #[ORM\ManyToOne(targetEntity: Client::class, inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: true)]
    private $Client;

    #[ORM\ManyToMany(targetEntity: Date::class, mappedBy: 'relation')]
    private $dates;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $Telephone;

    #[ORM\ManyToOne(targetEntity: Code::class, inversedBy: 'reservations')]
    private $CodeAcces;

    public function __construct()
    {
        $this->dates = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombrePlace(): ?int
    {
        return $this->NombrePlace;
    }

    public function setNombrePlace(int $NombrePlace): self
    {
        $this->NombrePlace = $NombrePlace;

        return $this;
    }

    public function getDateArrivee(): ?\DateTimeInterface
    {
        return $this->DateArrivee;
    }

    public function setDateArrivee(\DateTimeInterface $DateArrivee): self
    {
        $this->DateArrivee = $DateArrivee;

        return $this;
    }

    public function getDateDepart(): ?\DateTimeInterface
    {
        return $this->DateDepart;
    }

    public function setDateDepart(\DateTimeInterface $DateDepart): self
    {
        $this->DateDepart = $DateDepart;

        return $this;
    }

    public function getDateReservation(): ?\DateTimeInterface
    {
        return $this->DateReservation;
    }

    public function setDateReservation(\DateTimeInterface $DateReservation): self
    {
        $this->DateReservation = $DateReservation;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->Client;
    }

    public function setClient(?Client $Client): self
    {
        $this->Client = $Client;

        return $this;
    }

    /**
     * @return Collection<int, Date>
     */
    public function getDates(): Collection
    {
        return $this->dates;
    }

    public function addDate(Date $date): self
    {
        if (!$this->dates->contains($date)) {
            $this->dates[] = $date;
            $date->addRelation($this);
        }

        return $this;
    }

    public function removeDate(Date $date): self
    {
        if ($this->dates->removeElement($date)) {
            $date->removeRelation($this);
        }

        return $this;
    }

    public function AjoutDates($entityManager){

        $date = new Date();
        $date->AjoutDates($this->DateArrivee,$this->DateDepart, $entityManager);
        $dateBoucle = new \DateTime($this->DateArrivee->format('Y-m-d'));
        $duree = $this->Duree();

        for($i = 0 ; $i < $duree; $i++){

           $date = $entityManager->getRepository(Date::class)->FindOneBy(array("Date"=>$dateBoucle));
           $this->addDate($date);

           $dateBoucle = new \DateTime(($dateBoucle->add(new \DateInterval("P1D"))->format('Y-m-d')));
        }

    }

    public function Duree() : int{
       return $this->DateArrivee->diff($this->DateDepart)->days+1;
    }

    public function VerificationDisponibilites($entityManager) : int{

        $dateBoucle = new \DateTime($this->DateArrivee->format('Y-m-d'));
        $duree = $this->Duree();
        $date = $entityManager->getRepository(Date::class)->FindOneBy(array("Date"=>$dateBoucle));
        $nombrePlaceDisponiblesMin = $date->NombrePlaceDisponibles($entityManager) - $this->NombrePlace;

        for($i = 0 ; $i < $duree; $i++){

            $date = $entityManager->getRepository(Date::class)->FindOneBy(array("Date"=>$dateBoucle));
            $nombrePlaceDisponibles = $date->NombrePlaceDisponibles($entityManager) - $this->NombrePlace;
            if( $nombrePlaceDisponibles < 0){
                return -1;
            }
            elseif($nombrePlaceDisponibles < $nombrePlaceDisponiblesMin){
                $nombrePlaceDisponiblesMin = $nombrePlaceDisponibles;
            }

            $dateBoucle = new \DateTime(($dateBoucle->add(new \DateInterval("P1D"))->format('Y-m-d')));
        }

        return $nombrePlaceDisponiblesMin;
    }

    public function Prix(): int{

        $duree = $this->Duree();

        if($duree < 5){
            $tarif = array(1 => 5 , 2 => 8, 3 => 10 , 4 => 10 );
            $prix = $tarif[$duree];
        }
        if($duree > 4 && $duree < 29){

            $prix = 10;
            $duree -= 4;
            $prix += round($duree/2,0,PHP_ROUND_HALF_UP) * 5;
        }
        if( $duree > 28){
            $prix = 70;
            $duree -= 29;
            $prix += round($duree/5,0,PHP_ROUND_HALF_UP) * 5;
        }

        return $prix * $this->NombrePlace;

    }

    public function getTelephone(): ?string
    {
        return $this->Telephone;
    }

    public function setTelephone(?string $Telephone): self
    {
        $this->Telephone = $Telephone;

        return $this;
    }

    public function getCodeAcces(): ?Code
    {
        return $this->CodeAcces;
    }

    public function setCodeAcces(?Code $CodeAcces): self
    {
        $this->CodeAcces = $CodeAcces;

        return $this;
    }

}
