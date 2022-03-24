<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LocationRepository")
 */
class Location
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Structure", inversedBy="locations")
     */
    private $structure;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $adress;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Ssession", mappedBy="location")
     */
    private $ssessions;

    public function __construct()
    {
        $this->ssessions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStructure(): ?Structure
    {
        return $this->structure;
    }

    public function setStructure(?Structure $structure): self
    {
        $this->structure = $structure;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAdress(): ?string
    {
        return $this->adress;
    }

    public function setAdress(?string $adress): self
    {
        $this->adress = $adress;

        return $this;
    }

    /**
     * @return Collection|Ssession[]
     */
    public function getSsessions(): Collection
    {
        return $this->ssessions;
    }

    public function addSsession(Ssession $ssession): self
    {
        if (!$this->ssessions->contains($ssession)) {
            $this->ssessions[] = $ssession;
            $ssession->setLocation($this);
        }

        return $this;
    }

    public function removeSsession(Ssession $ssession): self
    {
        if ($this->ssessions->contains($ssession)) {
            $this->ssessions->removeElement($ssession);
            // set the owning side to null (unless already changed)
            if ($ssession->getLocation() === $this) {
                $ssession->setLocation(null);
            }
        }

        return $this;
    }
}
