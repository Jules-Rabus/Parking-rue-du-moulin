<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\ReservationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
#[ApiResource(
    attributes: ["security" => "is_granted('ROLE_ADMIN')"],
    collectionOperations: ['get' => ['normalization_context' => ['groups' => 'reservation:read']]],
    itemOperations: ['get' => ['normalization_context' => ['groups' => 'reservation:read']]],
)]
#[ApiFilter(DateFilter::class, properties: ['DateArrivee','DateDepart'])]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['reservation:read'])]
    private $id;

    #[ORM\Column(type: 'integer')]
    #[Groups(['reservation:read'])]
    private $NombrePlace;

    #[ORM\Column(type: 'date')]
    #[Groups(['reservation:read'])]
    private $DateArrivee;

    #[ORM\Column(type: 'date')]
    #[Groups(['reservation:read'])]
    private $DateDepart;

    #[ORM\Column(type: 'date')]
    #[Groups(['reservation:read'])]
    private $DateReservation;

    #[ORM\ManyToOne(targetEntity: Client::class, inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['reservation:read'])]
    private $Client;

    #[ORM\ManyToMany(targetEntity: Date::class, inversedBy: 'relation')]
    #[Groups(['reservation:read'])]
    private $dates;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['reservation:read'])]
    private $Telephone;

    #[ORM\ManyToOne(targetEntity: Code::class, inversedBy: 'reservations')]
    #[Groups(['reservation:read'])]
    private $CodeAcces;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups(['reservation:read'])]
    private $CodeDonne;

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

    public function AjoutDates($entityManager, bool $modification = false) : self{

        $date = new Date();
        $date->AjoutDates($this->DateArrivee,$this->DateDepart, $entityManager);
        $dateBoucle = new \DateTime($this->DateArrivee->format('Y-m-d'));
        $duree = $this->Duree();

        // On efface les dates actuelles en cas de modification de date
        if($modification){
            $this->dates->clear();
        }

        for($i = 0 ; $i < $duree; $i++){

            // FindOneBy car on a déja créer verifier que la date a été créer via AjoutDates()
            $date = $entityManager->getRepository(Date::class)->FindOneBy(array("Date"=>$dateBoucle));
            $this->addDate($date);

            $dateBoucle = new \DateTime(($dateBoucle->add(new \DateInterval("P1D"))->format('Y-m-d')));
        }

        return $this;

    }

    public function Duree() : int{
       return $this->DateArrivee->diff($this->DateDepart)->days+1;
    }

    public static function DureeReservation(\DateTime $dateArrivee, \DateTime $dateDepart) : int {
        return $dateArrivee->diff($dateDepart)->days+1;
    }

    public function VerificationDisponibilites($entityManager,bool $modification = false ) : int{

        $dateBoucle = new \DateTime($this->DateArrivee->format('Y-m-d'));
        $duree = $this->Duree();
        $date = $entityManager->getRepository(Date::class)->SelectorCreate($dateBoucle);

        // placePresent est utile en cas de modification de la reservation pour deduire le vehicule deja present
        if($modification){
            $nombrePlaceDisponiblesMin = $date->getNombrePlaceDisponibles($entityManager);
        }
        else{
            $nombrePlaceDisponiblesMin = $date->getNombrePlaceDisponibles($entityManager) - $this->NombrePlace;
        }


        for($i = 0 ; $i < $duree; $i++){

            $date = $entityManager->getRepository(Date::class)->SelectorCreate($dateBoucle);

            if($modification){
                $nombrePlaceDisponibles = $date->getNombrePlaceDisponibles($entityManager);
            }
            else{
                $nombrePlaceDisponibles = $date->getNombrePlaceDisponibles($entityManager) - $this->NombrePlace;
            }

            if($nombrePlaceDisponibles < 0){
                return -1;
            }
            elseif($nombrePlaceDisponibles < $nombrePlaceDisponiblesMin){
                $nombrePlaceDisponiblesMin = $nombrePlaceDisponibles;
            }

            $dateBoucle = new \DateTime(($dateBoucle->add(new \DateInterval("P1D"))->format('Y-m-d')));

        }

        return $nombrePlaceDisponiblesMin;
    }

    public function NombreReservation($entityManager) : int{

        if($this->Client){
            return $entityManager->getRepository(Reservation::class)->NombreReservationClient($this->getClient()->getId());
        }
        return $entityManager->getRepository(Reservation::class)->NombreReservationTelephone($this->Telephone);
    }

    #[Groups(['reservation:read'])]
    public function getPrix(): int{

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

    public static function Prix(\DateTime $dateArrivee, \DateTime $dateDepart, int $nombrePlace): int{

        $duree = $dateArrivee->diff($dateDepart)->days+1;

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

        return $prix * $nombrePlace;

    }

    public function getTelephone(): ?string
    {
        return $this->Telephone;
    }

    public function setTelephone(?string $Telephone): self
    {
        // Conversion en +33 + et suppresion des espaces

        if ($Telephone[0] == 0 && ($Telephone[1] == 6 || $Telephone[1] == 7)){
            $this->Telephone = substr_replace(str_replace(' ','',$Telephone),"+33",0,1);
        }
        elseif(strstr($Telephone,"+33")){
            $this->Telephone = str_replace(' ','',$Telephone);
        }

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

    public function getCodeDonne(): ?bool
    {
        return $this->CodeDonne;
    }

    public function setCodeDonne(?bool $CodeDonne): self
    {
        $this->CodeDonne = $CodeDonne;

        return $this;
    }
    

}
