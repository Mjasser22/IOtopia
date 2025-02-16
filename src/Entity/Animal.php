<?php

namespace App\Entity;

use App\Repository\AnimalRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AnimalRepository::class)]
class Animal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank] // Champ obligatoire
    #[Assert\Length(min: 2, max: 255)] // Longueur minimale et maximale
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank] 
    #[Assert\Length(min: 3, max: 255)] 
    private ?string $species = null;

    #[ORM\Column]
    #[Assert\NotNull] 
    #[Assert\Positive] // Doit être un nombre positif
    #[Assert\LessThan(100)] // L'âge doit être inférieur à 100
    private ?int $age = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['Healthy', 'Sick', 'Injured'], message: "Choose a valid health status: Healthy, Sick, Injured")] // Contrôle des valeurs possibles
    private ?string $health_status = null;

    #[ORM\OneToMany(mappedBy: "animal", targetEntity: SoinDesAnimaux::class, cascade: ["persist", "remove"])]
    private Collection $soins;

    public function __construct()
    {
        $this->soins = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getSpecies(): ?string
    {
        return $this->species;
    }

    public function setSpecies(string $species): static
    {
        $this->species = $species;
        return $this;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(int $age): static
    {
        $this->age = $age;
        return $this;
    }

    public function getHealthStatus(): ?string
    {
        return $this->health_status;
    }

    public function setHealthStatus(string $health_status): static
    {
        $this->health_status = $health_status;
        return $this;
    }

    /**
     * @return Collection<int, SoinDesAnimaux>
     */
    public function getSoins(): Collection
    {
        return $this->soins;
    }

    public function addSoin(SoinDesAnimaux $soin): static
    {
        if (!$this->soins->contains($soin)) {
            $this->soins->add($soin);
            $soin->setAnimal($this);
        }
        return $this;
    }

    public function removeSoin(SoinDesAnimaux $soin): static
    {
        if ($this->soins->removeElement($soin)) {
            if ($soin->getAnimal() === $this) {
                $soin->setAnimal(null);
            }
        }
        return $this;
    }
}
