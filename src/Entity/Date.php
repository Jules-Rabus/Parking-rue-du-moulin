<?php

namespace App\Entity;

use App\Repository\DateRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DateRepository::class)]
class Date
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer')]
    private $NombrePlace;

    #[ORM\Column(type: 'date')]
    private $Date;

    #[ORM\OneToMany(mappedBy: 'date', targetEntity: Reservation::class)]
    private $Reservations;

    public function __construct()
    {
        $this->Reservations = new ArrayCollection();
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
    public function getReservations(): Collection
    {
        return $this->Reservations;
    }

    public function addReservation(Reservation $reservation): self
    {
        if (!$this->Reservations->contains($reservation)) {
            $this->Reservations[] = $reservation;
            $reservation->setDate($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->Reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getDate() === $this) {
                $reservation->setDate(null);
            }
        }

        return $this;
    }
}
