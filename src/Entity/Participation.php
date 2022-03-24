<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ParticipationRepository")
 */
class Participation
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
    private $userId;

    /**
     * @ORM\Column(type="integer")
     * @Groups("post:read")
     */
    private $ssessionId;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $invitedBy;

    /**
     * @ORM\Column(type="integer")
     */
    private $productId;

    /**
     * @ORM\Column(type="smallint")
     */
    private $present;  

    public function getId(): ?int
    {
        return $this->id;
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

    public function getSsessionId(): ?int
    {
        return $this->ssessionId;
    }

    public function setSsessionId(int $ssessionId): self
    {
        $this->ssessionId = $ssessionId;

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

    public function getInvitedBy(): ?int
    {
        return $this->invitedBy;
    }

    public function setInvitedBy(?int $invitedBy): self
    {
        $this->invitedBy= $invitedBy;

        return $this;
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

    public function getPresent(): ?int
    {
        return $this->present;
    }

    public function setPresent(int $present): self
    {
        $this->present = $present;

        return $this;
    }

   
}
