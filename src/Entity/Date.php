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
    #[ORM\Column(type: 'date')]
    private $id;


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

        for($i = 0 ; $i < 5; $i++){

            $dateBoucle = new \DateTime(($dateBoucle->add(new \DateInterval("P1D"))->format('Y-m-d')));

            $date = $entityManager->getRepository(Date::class)->selectIfExists($dateBoucle);

            if(!$date){
                $date = new Date();
                $date->setDate($dateBoucle);
                dump($date);
            }

            $entityManager->persist($date);

        }
        $entityManager->flush();
        exit();

    }


}