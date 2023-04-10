<?php

namespace App\Entity;

use App\Repository\FlCustomerRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FlCustomerRepository::class)]
class FlCustomer
  {
   const USER_STATUS_ACTIVE    = 1;
   const USER_STATUS_INACTIVE  = 0;
   const USER_STATUS_LOCKED    = 2;
  
  #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false,onDelete: 'cascade')]
    private ?User $RefUser = null;

    #[ORM\Column(length: 38)]
    private ?string $CustomerNumber = null;

    #[ORM\Column(length: 200)]
    private ?string $Name = null;

    #[ORM\Column(length: 200)]
    private ?string $ContactEmail = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $ContactName = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $ContactPosition = null;

    #[ORM\Column]
    private ?bool $Active = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $Street = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $City = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $PostalCode = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $Country = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $PhoneBusiness = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $PhonePrivate = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $PhoneMobile = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $PhoneFax = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $CustomerUrl = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $CreatedOn = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $LastModifiedOn = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCustomerNumber(): ?string
    {
        return $this->CustomerNumber;
    }

    public function setCustomerNumber(string $CustomerNumber): self
    {
        $this->CustomerNumber = $CustomerNumber;

        return $this;
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

    public function getContactEmail(): ?string
    {
        return $this->ContactEmail;
    }

    public function setContactEmail(string $ContactEmail): self
    {
        $this->ContactEmail = $ContactEmail;

        return $this;
    }

    public function getContactName(): ?string
    {
        return $this->ContactName;
    }

    public function setContactName(?string $ContactName): self
    {
        $this->ContactName = $ContactName;

        return $this;
    }

    public function getContactPosition(): ?string
    {
        return $this->ContactPosition;
    }

    public function setContactPosition(?string $ContactPosition): self
    {
        $this->ContactPosition = $ContactPosition;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->Active;
    }

    public function setActive(bool $Active): self
    {
        $this->Active = $Active;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->Street;
    }

    public function setStreet(?string $Street): self
    {
        $this->Street = $Street;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->City;
    }

    public function setCity(?string $City): self
    {
        $this->City = $City;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->PostalCode;
    }

    public function setPostalCode(?string $PostalCode): self
    {
        $this->PostalCode = $PostalCode;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->Country;
    }

    public function setCountry(?string $Country): self
    {
        $this->Country = $Country;

        return $this;
    }

    public function getPhoneBusiness(): ?string
    {
        return $this->PhoneBusiness;
    }

    public function setPhoneBusiness(?string $PhoneBusiness): self
    {
        $this->PhoneBusiness = $PhoneBusiness;

        return $this;
    }

    public function getPhonePrivate(): ?string
    {
        return $this->PhonePrivate;
    }

    public function setPhonePrivate(?string $PhonePrivate): self
    {
        $this->PhonePrivate = $PhonePrivate;

        return $this;
    }

    public function getPhoneMobile(): ?string
    {
        return $this->PhoneMobile;
    }

    public function setPhoneMobile(?string $PhoneMobile): self
    {
        $this->PhoneMobile = $PhoneMobile;

        return $this;
    }

    public function getPhoneFax(): ?string
    {
        return $this->PhoneFax;
    }

    public function setPhoneFax(?string $PhoneFax): self
    {
        $this->PhoneFax = $PhoneFax;

        return $this;
    }

    public function getCustomerUrl(): ?string
    {
        return $this->CustomerUrl;
    }

    public function setCustomerUrl(?string $CustomerUrl): self
    {
        $this->CustomerUrl = $CustomerUrl;

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

    public function getLastModifiedOn(): ?\DateTimeInterface
    {
        return $this->LastModifiedOn;
    }

    public function setLastModifiedOn(?\DateTimeInterface $LastModifiedOn): self
    {
        $this->LastModifiedOn = $LastModifiedOn;

        return $this;
    }
}
