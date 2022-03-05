<?php

namespace App\Entity;

use App\Repository\TransfertBddRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TransfertBddRepository::class)]
class TransfertBdd
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $idreservation;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $client;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $nom;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $contact;

    #[ORM\Column(type: 'date', nullable: true)]
    private $date;

    #[ORM\Column(type: 'date', nullable: true)]
    private $datef;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $place;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $code;

    #[ORM\Column(type: 'date', nullable: true)]
    private $date_reservation;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdreservation(): ?int
    {
        return $this->idreservation;
    }

    public function setIdreservation(?int $idreservation): self
    {
        $this->idreservation = $idreservation;

        return $this;
    }

    public function getClient(): ?int
    {
        return $this->client;
    }

    public function setClient(?int $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getContact(): ?string
    {
        return $this->contact;
    }

    public function setContact(?string $contact): self
    {
        $this->contact = $contact;

        return $this;
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

    public function getDatef(): ?\DateTimeInterface
    {
        return $this->datef;
    }

    public function setDatef(?\DateTimeInterface $datef): self
    {
        $this->datef = $datef;

        return $this;
    }

    public function getPlace(): ?int
    {
        return $this->place;
    }

    public function setPlace(?int $place): self
    {
        $this->place = $place;

        return $this;
    }

    public function getCode(): ?int
    {
        return $this->code;
    }

    public function setCode(?int $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getDateReservation(): ?\DateTimeInterface
    {
        return $this->date_reservation;
    }

    public function setDateReservation(?\DateTimeInterface $date_reservation): self
    {
        $this->date_reservation = $date_reservation;

        return $this;
    }
}
