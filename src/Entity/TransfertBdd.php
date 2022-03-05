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
}
