<?php

namespace App\Entity;

use App\Repository\CodeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CodeRepository::class)]
#[ApiResource(
    attributes: ["security" => "is_granted('ROLE_ADMIN')"],
    collectionOperations: ['get' => ['normalization_context' => ['groups' => 'code:read']]],
    itemOperations: ['get' => ['normalization_context' => ['groups' => 'code:read']]],

)]
class Code
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer')]
    #[Groups(['code:read'])]
    private $Code;

    #[ORM\Column(type: 'date', nullable: true)]
    #[Groups(['code:read'])]
    private $DateDebut;

    #[ORM\Column(type: 'date', nullable: true)]
    #[Groups(['code:read'])]
    private $DateFin;

    #[ORM\OneToMany(mappedBy: 'CodeAcces', targetEntity: Reservation::class)]
    #[Groups(['code:read'])]
    private $reservations;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?int
    {
        return $this->Code;
    }

    public function setCode(int $Code): self
    {
        $this->Code = $Code;

        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->DateDebut;
    }

    public function setDateDebut(\DateTimeInterface $DateDebut): self
    {
        $this->DateDebut = $DateDebut;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->DateFin;
    }

    public function setDateFin(\DateTimeInterface $DateFin): self
    {
        $this->DateFin = $DateFin;

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): self
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations[] = $reservation;
            $reservation->setCodeAcces($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getCodeAcces() === $this) {
                $reservation->setCodeAcces(null);
            }
        }

        return $this;
    }
}
