<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProductRepository")
 */
class Product
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
     * @ORM\Column(type="decimal", precision=11, scale=2)
     */
    private $price;

    /**
     * @ORM\Column(type="integer")
     */
    private $credits;

    /**
     * @ORM\Column(type="text")
     */
    private $paypal;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Catalog", mappedBy="product")
     */
    private $catalogs;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Oorder", mappedBy="product")
     */
    private $oorders;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $ttype;

    /**
     * @ORM\OneToMany(targetEntity=FreeCredit::class, mappedBy="productId")
     */
    private $freeCredits;

    public function __construct()
    {
        $this->catalogs = new ArrayCollection();
        $this->oorders = new ArrayCollection();
        $this->freeCredits = new ArrayCollection();
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

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getCredits(): ?int
    {
        return $this->credits;
    }

    public function setCredits(int $credits): self
    {
        $this->credits = $credits;

        return $this;
    }

    public function getPaypal(): ?string
    {
        return $this->paypal;
    }

    public function setPaypal(string $paypal): self
    {
        $this->paypal = $paypal;

        return $this;
    }

    /**
     * @return Collection|Catalog[]
     */
    public function getCatalogs(): Collection
    {
        return $this->catalogs;
    }

    public function addCatalog(Catalog $catalog): self
    {
        if (!$this->catalogs->contains($catalog)) {
            $this->catalogs[] = $catalog;
            $catalog->setProduct($this);
        }

        return $this;
    }

    public function removeCatalog(Catalog $catalog): self
    {
        if ($this->catalogs->contains($catalog)) {
            $this->catalogs->removeElement($catalog);
            // set the owning side to null (unless already changed)
            if ($catalog->getProduct() === $this) {
                $catalog->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Oorder[]
     */
    public function getOorders(): Collection
    {
        return $this->oorders;
    }

    public function addOorder(Oorder $oorder): self
    {
        if (!$this->oorders->contains($oorder)) {
            $this->oorders[] = $oorder;
            $oorder->setProduct($this);
        }

        return $this;
    }

    public function removeOorder(Oorder $oorder): self
    {
        if ($this->oorders->contains($oorder)) {
            $this->oorders->removeElement($oorder);
            // set the owning side to null (unless already changed)
            if ($oorder->getProduct() === $this) {
                $oorder->setProduct(null);
            }
        }

        return $this;
    }

    public function getTtype(): ?int
    {
        return $this->ttype;
    }

    public function setTtype(?int $ttype): self
    {
        $this->ttype = $ttype;

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
            $freeCredit->setProductId($this);
        }

        return $this;
    }

    public function removeFreeCredit(FreeCredit $freeCredit): self
    {
        if ($this->freeCredits->contains($freeCredit)) {
            $this->freeCredits->removeElement($freeCredit);
            // set the owning side to null (unless already changed)
            if ($freeCredit->getProductId() === $this) {
                $freeCredit->setProductId(null);
            }
        }

        return $this;
    }  
    
}
