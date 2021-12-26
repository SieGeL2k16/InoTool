<?php

namespace App\Entity;

use App\Repository\AccountCategoriesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AccountCategoriesRepository::class)]
#[ORM\UniqueConstraint(name: 'uidx_acccatname',columns: ["ref_user_id","Name"])]
class AccountCategories
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 100)]
    private $Name;

    #[ORM\Column(type: 'boolean')]
    private $IsFixed;

    #[ORM\Column(type: 'boolean')]
    private $IsActive;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false,onDelete: "cascade")]
    private $RefUser;

    public function __construct()
    {
      $this->IsFixed  = false;
      $this->IsActive = true;
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

    public function getIsFixed(): ?bool
    {
        return $this->IsFixed;
    }

    public function setIsFixed(bool $IsFixed): self
    {
        $this->IsFixed = $IsFixed;

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
