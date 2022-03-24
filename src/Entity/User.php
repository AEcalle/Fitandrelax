<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface
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
    private $lastname;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Email()
     */
    private $email;   

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min="8", minMessage="Votre mot de passe doit faire au minimum 8 caractères")      *
     */
    private $password;

    /**
     * @Assert\EqualTo(propertyPath="password", message="mots de passe différents")
     */
    private $confirm_password;

    /**
    * @ORM\Column(type="json")
    */
    private $roles = [];

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Structure", inversedBy="users")
     */
    private $structure;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $mobile;

    /**
     * @ORM\Column(type="datetime")
     */
    private $deletedAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $passwordRequestedAt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $token;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Ssession", mappedBy="coach")
     */
    private $ssessions;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $coach;

    /**
     * @ORM\Column(type="integer")
     */
    private $rappel;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $aadmin;

    /**
     * @ORM\OneToMany(targetEntity=FreeCredit::class, mappedBy="userId")
     */
    private $freeCredits;

    /**
     * @ORM\OneToMany(targetEntity=Sponsorship::class, mappedBy="userId")
     */
    private $sponsorships;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $planningHebdo;

    /**
     * @ORM\Column(type="integer")
     */
    private $emailVerify;

    public function __construct()
    {
        $this->ssessions = new ArrayCollection();
        $this->freeCredits = new ArrayCollection();
        $this->sponsorships = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->firstname.' '.$this->lastname;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getConfirmPassword(): ?string
    {
        return $this->confirm_password;
    }

    public function setConfirmPassword(string $confirm_password): self
    {
        $this->confirm_password = $confirm_password;

        return $this;
    }
    //Méthodes obligatoires pour utiliser la UserInterface
    public function eraseCredentials(){}
    public function getSalt(){}
    public function getUsername(){
        return $this->email;
    }
    public function getRoles(): array
    {
    $roles = $this->roles;
    // guarantee every user at least has ROLE_USER
    $roles[] = 'ROLE_USER';

    return array_unique($roles);
    }

    public function setRoles(array $roles)
    {
    $this->roles = $roles;

    // allows for chaining
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

    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    public function setMobile(?string $mobile): self
    {
        $this->mobile = $mobile;

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(\DateTimeInterface $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    public function getPasswordRequestedAt(): ?\DateTimeInterface
    {
        return $this->passwordRequestedAt;
    }

    public function setPasswordRequestedAt(\DateTimeInterface $passwordRequestedAt): self
    {
        $this->passwordRequestedAt = $passwordRequestedAt;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCoach(): ?int
    {
        return $this->coach;
    }

    public function setCoach(int $coach): self
    {
        $this->coach = $coach;

        return $this;
    }

    public function getRappel(): ?int
    {
        return $this->rappel;
    }

    public function setRappel(int $rappel): self
    {
        $this->rappel = $rappel;

        return $this;
    }

    public function getAadmin(): ?int
    {
        return $this->aadmin;
    }

    public function setAadmin(?int $aadmin): self
    {
        $this->aadmin = $aadmin;

        return $this;
    }

    /**
     * @return Collection|FreeCredit[]
     */
    public function getFreeCredits(): Collection
    {
        return $this->freeCredits;
    }

    public function addFreeCredit(FreeCredit $freeCredit): self
    {
        if (!$this->freeCredits->contains($freeCredit)) {
            $this->freeCredits[] = $freeCredit;
            $freeCredit->setUserId($this);
        }

        return $this;
    }

    public function removeFreeCredit(FreeCredit $freeCredit): self
    {
        if ($this->freeCredits->contains($freeCredit)) {
            $this->freeCredits->removeElement($freeCredit);
            // set the owning side to null (unless already changed)
            if ($freeCredit->getUserId() === $this) {
                $freeCredit->setUserId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Sponsorship[]
     */
    public function getSponsorships(): Collection
    {
        return $this->sponsorships;
    }

    public function addSponsorship(Sponsorship $sponsorship): self
    {
        if (!$this->sponsorships->contains($sponsorship)) {
            $this->sponsorships[] = $sponsorship;
            $sponsorship->setUserId($this);
        }

        return $this;
    }

    public function removeSponsorship(Sponsorship $sponsorship): self
    {
        if ($this->sponsorships->contains($sponsorship)) {
            $this->sponsorships->removeElement($sponsorship);
            // set the owning side to null (unless already changed)
            if ($sponsorship->getUserId() === $this) {
                $sponsorship->setUserId(null);
            }
        }

        return $this;
    }

    public function getPlanningHebdo(): ?int
    {
        return $this->planningHebdo;
    }

    public function setPlanningHebdo(?int $planningHebdo): self
    {
        $this->planningHebdo = $planningHebdo;

        return $this;
    }

    public function getEmailVerify(): ?int
    {
        return $this->emailVerify;
    }

    public function setEmailVerify(int $emailVerify): self
    {
        $this->emailVerify = $emailVerify;

        return $this;
    }
   
}
