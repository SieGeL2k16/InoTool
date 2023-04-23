<?php

namespace App\Entity;

use App\Repository\FlServiceContractsRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FlServiceContractsRepository::class)]
class FlServiceContracts
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false,onDelete: 'cascade')]
    private ?User $RefUser = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false,onDelete: 'cascade')]
    private ?FlCustomer $RefCustomer = null;

    #[ORM\Column(length: 200)]
    private ?string $ContractName = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?DateTimeImmutable $ContractStartDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?DateTimeInterface $ContractEndDate = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    private ?string $ContractFee = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $ContractDescription = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ContractFilename = null;

    #[ORM\Column(nullable: true)]
    private ?int $ContractFilesize = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $ContractFiletype = null;

    #[ORM\Column(type: Types::BLOB, nullable: true)]
    private $ContractData = null;

    #[ORM\Column]
    private ?DateTimeImmutable $CreatedOn = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $LastModifiedOn = null;

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

    public function getRefCustomer(): ?FlCustomer
    {
        return $this->RefCustomer;
    }

    public function setRefCustomer(?FlCustomer $RefCustomer): self
    {
        $this->RefCustomer = $RefCustomer;

        return $this;
    }

    public function getContractName(): ?string
    {
        return $this->ContractName;
    }

    public function setContractName(string $ContractName): self
    {
        $this->ContractName = $ContractName;

        return $this;
    }

    public function getContractStartDate(): ?DateTimeImmutable
    {
        return $this->ContractStartDate;
    }

    public function setContractStartDate(DateTimeImmutable $ContractStartDate): self
    {
        $this->ContractStartDate = $ContractStartDate;

        return $this;
    }

    public function getContractEndDate(): ?DateTimeInterface
    {
        return $this->ContractEndDate;
    }

    public function setContractEndDate(?DateTimeInterface $ContractEndDate): self
    {
        $this->ContractEndDate = $ContractEndDate;

        return $this;
    }

    public function getContractFee(): ?string
    {
        return $this->ContractFee;
    }

    public function setContractFee(string $ContractFee): self
    {
        $this->ContractFee = $ContractFee;

        return $this;
    }

    public function getContractDescription(): ?string
    {
        return $this->ContractDescription;
    }

    public function setContractDescription(?string $ContractDescription): self
    {
        $this->ContractDescription = $ContractDescription;

        return $this;
    }

    public function getContractFilename(): ?string
    {
        return $this->ContractFilename;
    }

    public function setContractFilename(?string $ContractFilename): self
    {
        $this->ContractFilename = $ContractFilename;

        return $this;
    }

    public function getContractFilesize(): ?int
    {
        return $this->ContractFilesize;
    }

    public function setContractFilesize(?int $ContractFilesize): self
    {
        $this->ContractFilesize = $ContractFilesize;

        return $this;
    }

    public function getContractFiletype(): ?string
    {
        return $this->ContractFiletype;
    }

    public function setContractFiletype(?string $ContractFiletype): self
    {
        $this->ContractFiletype = $ContractFiletype;

        return $this;
    }

    public function getContractData()
    {
        return $this->ContractData;
    }

    public function setContractData($ContractData): self
    {
        $this->ContractData = $ContractData;

        return $this;
    }

    public function getCreatedOn(): ?DateTimeImmutable
    {
        return $this->CreatedOn;
    }

    public function setCreatedOn(DateTimeImmutable $CreatedOn): self
    {
        $this->CreatedOn = $CreatedOn;

        return $this;
    }

    public function getLastModifiedOn(): ?DateTimeInterface
    {
        return $this->LastModifiedOn;
    }

    public function setLastModifiedOn(?DateTimeInterface $LastModifiedOn): self
    {
        $this->LastModifiedOn = $LastModifiedOn;

        return $this;
    }
}
