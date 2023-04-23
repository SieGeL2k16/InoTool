<?php

namespace App\Entity;

use App\Repository\FlServiceContractEntriesRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FlServiceContractEntriesRepository::class)]
class FlServiceContractEntries
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
    private ?FlServiceContracts $RefContract = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $BillingDate = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    private ?string $BillingFee = null;
  
    #[ORM\Column]
    private ?DateTimeImmutable $CreatedOn = null;
  
  
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

    public function getRefContract(): ?FlServiceContracts
    {
        return $this->RefContract;
    }

    public function setRefContract(?FlServiceContracts $RefContract): self
    {
        $this->RefContract = $RefContract;

        return $this;
    }

    public function getBillingDate(): ?\DateTimeImmutable
    {
        return $this->BillingDate;
    }

    public function setBillingDate(\DateTimeImmutable $BillingDate): self
    {
        $this->BillingDate = $BillingDate;

        return $this;
    }

    public function getBillingFee(): ?string
    {
        return $this->BillingFee;
    }

    public function setBillingFee(string $BillingFee): self
    {
        $this->BillingFee = $BillingFee;

        return $this;
    }
  
  /**
   * @return DateTimeImmutable|null
   */
  public function getCreatedOn(): ?DateTimeImmutable
  {
    return $this->CreatedOn;
  }
  
  /**
   * @param DateTimeImmutable|null $CreatedOn
   * @return FlServiceContractEntries
   */
  public function setCreatedOn(?DateTimeImmutable $CreatedOn): self
  {
    $this->CreatedOn = $CreatedOn;
    return $this;
  }
}
