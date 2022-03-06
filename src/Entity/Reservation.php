<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

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

    #[ORM\Column(type: 'integer', nullable: true)]
    private $CodeAcces;

    #[ORM\ManyToOne(targetEntity: Client::class, inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private $Client;

    #[ORM\ManyToMany(targetEntity: Date::class, mappedBy: 'relation')]
    private $dates;

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

    public function getCodeAcces(): ?int
    {
        return $this->CodeAcces;
    }

    public function setCodeAcces(?int $CodeAcces): self
    {
        $this->CodeAcces = $CodeAcces;

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

}
