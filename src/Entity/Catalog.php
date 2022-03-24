<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CatalogRepository")
 */
class Catalog
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $structureId;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Product", inversedBy="catalogs")
     */
    private $product;
     

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStructureId(): ?int
    {
        return $this->structureId;
    }

    public function setStructureId(int $structureId): self
    {
        $this->structureId = $structureId;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }
    
   
}
