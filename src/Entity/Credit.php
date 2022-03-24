<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CreditRepository")
 */
class Credit
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups("post:read")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     * @Groups("post:read")
     */
    private $productId;

    /**
     * @ORM\Column(type="integer")
     * @Groups("post:read")
     */
    private $userId;

    /**
     * @ORM\Column(type="integer")
     * @Groups("post:read")
     */
    private $amount;

    /**
     * @ORM\Column(type="integer")
     */
    private $structureId;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProductId(): ?int
    {
        return $this->productId;
    }

    public function setProductId(int $productId): self
    {
        $this->productId = $productId;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
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
}
