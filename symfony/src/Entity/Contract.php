<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity]
class Contract implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'text')]
    private $ContentFi;

    /**
     * @Gedmo\Timestampable(on="update")
     */
    #[ORM\Column(type: 'datetime')]
    private $updatedAt;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private $purpose;

    /**
     * @Gedmo\Timestampable(on="create")
     */
    #[ORM\Column(type: 'datetime')]
    private $createdAt;

    #[ORM\Column(type: 'text', nullable: true)]
    private $ContentEn;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContentFi(): ?string
    {
        return $this->ContentFi;
    }

    public function setContentFi(string $ContentFi): self
    {
        $this->ContentFi = $ContentFi;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getPurpose(): ?string
    {
        return $this->purpose;
    }

    public function setPurpose(string $purpose): self
    {
        $this->purpose = $purpose;

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
    public function __toString(): string
    {
        return (string) ($this->purpose ?: 'purpose');
    }

    public function getContentEn(): ?string
    {
        return $this->ContentEn;
    }

    public function setContentEn(?string $ContentEn): self
    {
        $this->ContentEn = $ContentEn;

        return $this;
    }
}
