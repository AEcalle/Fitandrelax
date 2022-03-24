<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StructureRepository")
 */
class Structure
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $code;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\User", mappedBy="structure")
     */
    private $users;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Ssession", mappedBy="structure")
     */
    private $ssessions;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Location", mappedBy="structure")
     */
    private $locations;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Notification", mappedBy="structure")
     */
    private $notifications;

    /**
     * @ORM\Column(type="integer")
     */
    private $publicSsessions;    

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->ssessions = new ArrayCollection();
        $this->products = new ArrayCollection();
        $this->catalogs = new ArrayCollection();
        $this->locations = new ArrayCollection();
        $this->notifications = new ArrayCollection();
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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setStructure($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            // set the owning side to null (unless already changed)
            if ($user->getStructure() === $this) {
                $user->setStructure(null);
            }
        }

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
            $ssession->setStructure($this);
        }

        return $this;
    }

    public function removeSsession(Ssession $ssession): self
    {
        if ($this->ssessions->contains($ssession)) {
            $this->ssessions->removeElement($ssession);
            // set the owning side to null (unless already changed)
            if ($ssession->getStructure() === $this) {
                $ssession->setStructure(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Location[]
     */
    public function getLocations(): Collection
    {
        return $this->locations;
    }

    public function addLocation(Location $location): self
    {
        if (!$this->locations->contains($location)) {
            $this->locations[] = $location;
            $location->setStructure($this);
        }

        return $this;
    }

    public function removeLocation(Location $location): self
    {
        if ($this->locations->contains($location)) {
            $this->locations->removeElement($location);
            // set the owning side to null (unless already changed)
            if ($location->getStructure() === $this) {
                $location->setStructure(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Notification[]
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): self
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications[] = $notification;
            $notification->setStructure($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): self
    {
        if ($this->notifications->contains($notification)) {
            $this->notifications->removeElement($notification);
            // set the owning side to null (unless already changed)
            if ($notification->getStructure() === $this) {
                $notification->setStructure(null);
            }
        }

        return $this;
    }

    public function getPublicSsessions(): ?int
    {
        return $this->publicSsessions;
    }

    public function setPublicSsessions(int $publicSsessions): self
    {
        $this->publicSsessions = $publicSsessions;

        return $this;
    }
    
}
