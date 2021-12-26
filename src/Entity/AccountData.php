<?php

namespace App\Entity;

use App\Repository\AccountDataRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AccountDataRepository::class)]
#[ORM\UniqueConstraint(name: "uidx_accdata1",columns: ["booking_date","description","amount","ref_user_id"]) ]
class AccountData
{
  const BANK_TARGO_BANK     = 0;
  const BANK_DEUTSCHE_BANK  = 1;
  
  public array $BANK_DESC = [
    self::BANK_TARGO_BANK     => 'Targo Bank',
    self::BANK_DEUTSCHE_BANK  => 'Deutsche Bank',
    ];
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'datetime_immutable')]
    private $BookingDate;

    #[ORM\Column(type: 'text')]
    private $Description;

    #[ORM\Column(type: 'decimal', precision: 12, scale: 2)]
    private $Amount;

    #[ORM\ManyToOne(targetEntity: AccountCategories::class)]
    private $RefCategory;

    #[ORM\Column(type: 'string', length: 34)]
    private $AccountingNumber;

    #[ORM\Column(type: 'string', length: 10)]
    private $Currency;

    #[ORM\Column(type: 'integer')]
    private $BankId;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false,onDelete: "cascade")]
    private $RefUser;

    #[ORM\Column(type: 'boolean')]
    private $IsIncome;

    public function __construct()
      {
      $this->setCurrency('EUR')->setBankId(self::BANK_DEUTSCHE_BANK);
      }
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBookingDate(): ?\DateTimeImmutable
    {
        return $this->BookingDate;
    }

    public function setBookingDate(\DateTimeImmutable $BookingDate): self
    {
        $this->BookingDate = $BookingDate;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->Description;
    }

    public function setDescription(string $Description): self
    {
        $this->Description = $Description;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->Amount;
    }

    public function setAmount(string $Amount): self
    {
        $this->Amount = $Amount;

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

    public function getAccountingNumber(): ?string
    {
        return $this->AccountingNumber;
    }

    public function setAccountingNumber(string $AccountingNumber): self
    {
        $this->AccountingNumber = $AccountingNumber;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->Currency;
    }

    public function setCurrency(string $Currency): self
    {
        $this->Currency = $Currency;

        return $this;
    }

    public function getBankId(): ?int
    {
        return $this->BankId;
    }

    public function setBankId(int $BankId): self
    {
        $this->BankId = $BankId;

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

    public function getIsIncome(): ?bool
    {
        return $this->IsIncome;
    }

    public function setIsIncome(bool $IsIncome): self
    {
        $this->IsIncome = $IsIncome;

        return $this;
    }
}
