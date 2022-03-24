<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SsessionRepository")
 */
class Ssession
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups("post:read")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     * @Groups("post:read")
     */
    private $scheduledAt;

    /**
     * @ORM\Column(type="datetime")
     * @Groups("post:read")
     */
    private $finishedAt;   

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Structure", inversedBy="ssessions")      * 
     */
    private $structure;

    /**
     * @ORM\Column(type="integer")
     */
    private $participationMax;

    /**
     * @ORM\Column(type="datetime")
     */
    private $timeLimit;   

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="ssessions")
     * @ORM\JoinColumn(name="coach_id", referencedColumnName="id", nullable=true) 
     */
    private $coach;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Location", inversedBy="ssessions")
     * @ORM\JoinColumn(name="location_id", referencedColumnName="id", nullable=true) 
     */
    private $location;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $off;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $idZoom;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $passZoom;

    /**
     * @ORM\ManyToOne(targetEntity=Activity::class, inversedBy="ssessions")
     * @Groups("post:read")
     */
    private $activity;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)     *
     */
    private $subtitle;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $gratuite;   
   

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getScheduledAt(): ?\DateTimeInterface
    {
        return $this->scheduledAt;
    }

    public function setScheduledAt(\DateTimeInterface $scheduledAt): self
    {
        $this->scheduledAt = $scheduledAt;

        return $this;
    }

    public function getFinishedAt(): ?\DateTimeInterface
    {
        return $this->finishedAt;
    }

    public function setFinishedAt(\DateTimeInterface $finishedAt): self
    {
        $this->finishedAt = $finishedAt;

        return $this;
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

    public function getParticipationMax(): ?int
    {
        return $this->participationMax;
    }

    public function setParticipationMax(int $participationMax): self
    {
        $this->participationMax = $participationMax;

        return $this;
    }

    public function getTimeLimit(): ?\DateTimeInterface
    {
        return $this->timeLimit;
    }

    public function setTimeLimit(\DateTimeInterface $timeLimit): self
    {
        $this->timeLimit = $timeLimit;

        return $this;
    }   

    public function getCoach(): ?User
    {
        return $this->coach;
    }

    public function setCoach(?User $coach): self
    {
        $this->coach = $coach;

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getOff(): ?int
    {
        return $this->off;
    }

    public function setOff(?int $off): self
    {
        $this->off = $off;

        return $this;
    }

    public function getIdZoom(): ?string
    {
        return $this->idZoom;
    }

    public function setIdZoom(?string $idZoom): self
    {
        $this->idZoom = $idZoom;

        return $this;
    }

    public function getPassZoom(): ?string
    {
        return $this->passZoom;
    }

    public function setPassZoom(?string $passZoom): self
    {
        $this->passZoom = $passZoom;

        return $this;
    }

    public function getActivity(): ?Activity
    {
        return $this->activity;
    }

    public function setActivity(?Activity $activity): self
    {
        $this->activity = $activity;

        return $this;
    }

    public function getSubtitle(): ?string
    {
        return $this->subtitle;
    }

    public function setSubtitle(?string $subtitle): self
    {
        $this->subtitle = $subtitle;

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

    public function getGratuite(): ?int
    {
        return $this->gratuite;
    }

    public function setGratuite(?int $gratuite): self
    {
        $this->gratuite = $gratuite;

        return $this;
    }  
   
}
