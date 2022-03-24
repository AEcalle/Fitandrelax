<?php

namespace App\Entity;

use App\Repository\ActivityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=ActivityRepository::class)
 */
class Activity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("post:read")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("post:read")
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity=Ssession::class, mappedBy="activityId")
     */
    private $ssessions;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("post:read")
     */
    private $color;

    public function __construct()
    {
        $this->ssessions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

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
            $ssession->setActivityId($this);
        }

        return $this;
    }

    public function removeSsession(Ssession $ssession): self
    {
        if ($this->ssessions->contains($ssession)) {
            $this->ssessions->removeElement($ssession);
            // set the owning side to null (unless already changed)
            if ($ssession->getActivityId() === $this) {
                $ssession->setActivityId(null);
            }
        }

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): self
    {
        $this->color = $color;

        return $this;
    }
}
