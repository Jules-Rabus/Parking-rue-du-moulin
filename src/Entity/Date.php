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

    #[ORM\Column(type: 'date', unique: false)]
    private $Date;

    #[ORM\ManyToMany(targetEntity: Reservation::class, inversedBy: 'dates')]
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
        $duree = $dateDebut->diff($dateFin)->days;

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


}