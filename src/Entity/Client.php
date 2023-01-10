<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
#[ApiResource(
    attributes: ["security" => "is_granted('ROLE_ADMIN')"],
    normalizationContext: ['groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:write']],
    itemOperations : ['get']
)]
#[ApiFilter(SearchFilter::class, properties: ['email' => 'ipartial','nom' => 'ipartial','telephone'=>'partial'])]

#[UniqueEntity(fields: ['email'], message: 'Il y a déjà un compte existant pour ce mail')]
class Client implements UserInterface, PasswordAuthenticatedUserInterface
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', unique: true)]
    #[Groups(['user:read'])]
    private $id;

    #[ORM\Column(type: 'string', length: 180, unique: true, nullable: true)]
    #[Groups(['user:read', 'user:write'])]
    private $email;

    #[ORM\Column(type: 'json')]
    private $roles = [];

    #[ORM\Column(type: 'string')]
    #[Groups(['user:write'])]
    private $password;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['user:read', 'user:write'])]
    private $nom;

    #[ORM\OneToMany(mappedBy: 'Client', targetEntity: Reservation::class)]
    #[Groups(['user:read'])]
    private $reservations;

    #[ORM\Column(type: 'boolean')]
    private $isVerified = false;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['user:read', 'user:write'])]
    private $telephone;


    public function __construct()
    {
        $this->reservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }


    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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
            $reservation->setClient($this);
        }

        return $this;
    }


    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getClient() === $this) {
                $reservation->setClient(null);
            }
        }

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function __toString(): string
    {
        return "Client : " . $this->getNom();
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    // Les clients étant principalement des étrangers, je converti chaque numero afin que les clients reçoivent leur appel/sms même à l'international
    public function setTelephone(?string $telephone): self
    {
        // Conversion en +33 + et suppression des espaces

        if ($telephone != null && $telephone[0] == 0 && ($telephone[1] == 6 || $telephone[1] == 7) ){
            $this->telephone = substr_replace(str_replace(' ','',$telephone),"+33",0,1);
        }
        elseif($telephone != null && str_contains($telephone,"+33")){
            $this->telephone = str_replace(' ','',$telephone);
        }
        else{
            $this->telephone = $telephone;
        }

        return $this;
    }

    #[Groups(['user:read'])]
    public function getContact() : string{

        if( $email = $this->getEmail()){
            return $email;
        }
        return $this->getTelephone();

    }

    #[Groups(['user:read'])]
    public function getReservationsTri() : array{

        // Tableau avec les reservations triees
        $reservationsClient = array('futur'=>[],'passe'=>[],'present'=>[]);

        $aujourdhui = new \DateTime();

        // Tri des reservations
        foreach ($this->getReservations()->getValues() as $reservation) {
            if ($reservation->getDateArrivee() > $aujourdhui) {
                $reservationsClient['futur'][] = $reservation;
            } else if ($reservation->getDateDepart() < $aujourdhui) {
                $reservationsClient['passe'][] = $reservation;
            } else {
                $reservationsClient['present'][] = $reservation;
            }
        }

        return $reservationsClient;

    }

    #[Groups(['user:read'])]
    public function getNombreReservation() : int{
        return count($this->getReservations());
    }
    
}
