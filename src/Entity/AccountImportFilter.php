<?php

namespace App\Entity;

use App\Repository\AccountImportFilterRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AccountImportFilterRepository::class)]
#[ORM\UniqueConstraint(name: "uidx_accimpfilter1",columns: ["name","ref_user_id"])]
class AccountImportFilter
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 100)]
    private $Name;

    #[ORM\Column(type: 'string', length: 255)]
    private $Definition;

    #[ORM\ManyToOne(targetEntity: AccountCategories::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $RefCategory;

    #[ORM\Column(type: 'boolean')]
    private $IsActive;

    #[ORM\Column(type: 'datetime_immutable')]
    private $CreatedOn;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false,onDelete: "cascade")]
    private $RefUser;

    public function __construct()
    {
      $this->CreatedOn = new DateTimeImmutable();
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->Name;
    }

    public function setName(string $Name): self
    {
        $this->Name = $Name;

        return $this;
    }

    public function getDefinition(): ?string
    {
        return $this->Definition;
    }

    public function setDefinition(string $Definition): self
    {
        $this->Definition = $Definition;

        return $this;
    }

    public function getRefCategory(): ?AccountCategories
    {
        return $this->RefCategory;
    }

    public function setRefCategory(?AccountCategories $RefCategory): self
    {
        $this->RefCategory = $RefCategory;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->IsActive;
    }

    public function setIsActive(bool $IsActive): self
    {
        $this->IsActive = $IsActive;

        return $this;
    }

    public function getCreatedOn(): ?\DateTimeImmutable
    {
        return $this->CreatedOn;
    }

    public function setCreatedOn(\DateTimeImmutable $CreatedOn): self
    {
        $this->CreatedOn = $CreatedOn;

        return $this;
    }

    public function getRefUser(): ?User
    {
        return $this->RefUser;
    }

    public function setRefUser(?User $RefUser): self
    {
        $this->RefUser = $RefUser;

        return $this;
    }
}
