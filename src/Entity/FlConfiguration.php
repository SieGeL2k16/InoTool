<?php

namespace App\Entity;

use App\Repository\FlConfigurationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: FlConfigurationRepository::class)]
class FlConfiguration
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $CompanyName = null;

    #[ORM\Column(length: 255)]
    private ?string $CompanyOwner = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $CompanyStreet = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $CompanyCity = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $CompanyPostal = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $CompanyCountry = null;

    #[ORM\Column(length: 30)]
    private ?string $CompanyTaxNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $CompanyEmail = null;

    #[ORM\Column(length: 40, nullable: true)]
    private ?string $CompanyPhone = null;

    #[ORM\Column(length: 40, nullable: true)]
    private ?string $CompanyFax = null;

    #[ORM\Column(type: Types::BLOB, nullable: true)]
    private $CompanyLogo = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $BankName = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $BankAccount = null;

    #[ORM\Column(length: 40, nullable: true)]
    private ?string $BankIban = null;

    #[ORM\Column(length: 12, nullable: true)]
    private ?string $BankBic = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false,onDelete: 'cascade')]
    private ?User $RefUser = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $CompanyLogoName = null;

    #[ORM\Column(nullable: true)]
    private ?int $CompanyLogoSize = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $CompanyLogoMimeType = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompanyName(): ?string
    {
        return $this->CompanyName;
    }

    public function setCompanyName(string $CompanyName): self
    {
        $this->CompanyName = $CompanyName;

        return $this;
    }

    public function getCompanyOwner(): ?string
    {
        return $this->CompanyOwner;
    }

    public function setCompanyOwner(string $CompanyOwner): self
    {
        $this->CompanyOwner = $CompanyOwner;

        return $this;
    }

    public function getCompanyStreet(): ?string
    {
        return $this->CompanyStreet;
    }

    public function setCompanyStreet(?string $CompanyStreet): self
    {
        $this->CompanyStreet = $CompanyStreet;

        return $this;
    }

    public function getCompanyCity(): ?string
    {
        return $this->CompanyCity;
    }

    public function setCompanyCity(?string $CompanyCity): self
    {
        $this->CompanyCity = $CompanyCity;

        return $this;
    }

    public function getCompanyPostal(): ?string
    {
        return $this->CompanyPostal;
    }

    public function setCompanyPostal(?string $CompanyPostal): self
    {
        $this->CompanyPostal = $CompanyPostal;

        return $this;
    }

    public function getCompanyCountry(): ?string
    {
        return $this->CompanyCountry;
    }

    public function setCompanyCountry(?string $CompanyCountry): self
    {
        $this->CompanyCountry = $CompanyCountry;

        return $this;
    }

    public function getCompanyTaxNumber(): ?string
    {
        return $this->CompanyTaxNumber;
    }

    public function setCompanyTaxNumber(string $CompanyTaxNumber): self
    {
        $this->CompanyTaxNumber = $CompanyTaxNumber;

        return $this;
    }

    public function getCompanyEmail(): ?string
    {
        return $this->CompanyEmail;
    }

    public function setCompanyEmail(?string $CompanyEmail): self
    {
        $this->CompanyEmail = $CompanyEmail;

        return $this;
    }

    public function getCompanyPhone(): ?string
    {
        return $this->CompanyPhone;
    }

    public function setCompanyPhone(?string $CompanyPhone): self
    {
        $this->CompanyPhone = $CompanyPhone;

        return $this;
    }

    public function getCompanyFax(): ?string
    {
        return $this->CompanyFax;
    }

    public function setCompanyFax(?string $CompanyFax): self
    {
        $this->CompanyFax = $CompanyFax;

        return $this;
    }

    public function getCompanyLogo()
    {
        return $this->CompanyLogo;
    }

    public function setCompanyLogo($CompanyLogo): self
    {
        $this->CompanyLogo = $CompanyLogo;

        return $this;
    }

    public function getBankName(): ?string
    {
        return $this->BankName;
    }

    public function setBankName(?string $BankName): self
    {
        $this->BankName = $BankName;

        return $this;
    }

    public function getBankAccount(): ?string
    {
        return $this->BankAccount;
    }

    public function setBankAccount(string $BankAccount): self
    {
        $this->BankAccount = $BankAccount;

        return $this;
    }

    public function getBankIban(): ?string
    {
        return $this->BankIban;
    }

    public function setBankIban(?string $BankIban): self
    {
        $this->BankIban = $BankIban;

        return $this;
    }

    public function getBankBic(): ?string
    {
        return $this->BankBic;
    }

    public function setBankBic(string $BankBic): self
    {
        $this->BankBic = $BankBic;

        return $this;
    }

    public function getRefUser(): ?User
    {
        return $this->RefUser;
    }

    public function setRefUser(UserInterface|User $RefUser): self
    {
        $this->RefUser = $RefUser;

        return $this;
    }

    public function getCompanyLogoName(): ?string
    {
        return $this->CompanyLogoName;
    }

    public function setCompanyLogoName(?string $CompanyLogoName): self
    {
        $this->CompanyLogoName = $CompanyLogoName;

        return $this;
    }

    public function getCompanyLogoSize(): ?int
    {
        return $this->CompanyLogoSize;
    }

    public function setCompanyLogoSize(?int $CompanyLogoSize): self
    {
        $this->CompanyLogoSize = $CompanyLogoSize;

        return $this;
    }

    public function getCompanyLogoMimeType(): ?string
    {
        return $this->CompanyLogoMimeType;
    }

    public function setCompanyLogoMimeType(?string $CompanyLogoMimeType): self
    {
        $this->CompanyLogoMimeType = $CompanyLogoMimeType;

        return $this;
    }
}
