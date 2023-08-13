<?php

namespace App\Entity;

use App\Repository\FlInvoicesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FlInvoicesRepository::class)]
class FlInvoices
  {
  const INVOICE_TYPE_INCOME   = 0;
  const INVOICE_TYPE_EXPENSE  = 1;

  public static array $_INVOICE_TYPE_LIST = [
    self::INVOICE_TYPE_INCOME   => 'Umsatz',
    self::INVOICE_TYPE_EXPENSE  => 'Ausgabe'
    ];
    
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

    #[ORM\Column]
    private ?int $InvoiceType = null;

    #[ORM\Column]
    private ?bool $IsPaid = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $InvoiceDate = null;

    #[ORM\Column(length: 30)]
    private ?string $InvoiceNumber = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $SumNetto = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $SumBrutto = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $TaxPct = null;

    #[ORM\Column]
    private ?bool $NoTax = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $Comment = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $PdfFilename = null;

    #[ORM\Column(nullable: true)]
    private ?int $PdfFilesize = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $PdfMimeType = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $CreatedOn = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $LastModifiedOn = null;

    #[ORM\Column(type: Types::BLOB, nullable: true)]
    private $PdfData = null;

    public function __construct()
      {
      $this->InvoiceType = self::INVOICE_TYPE_INCOME;
      $this->IsPaid = false;
      }
    
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

    public function getInvoiceType(): ?int
    {
        return $this->InvoiceType;
    }

    public function setInvoiceType(int $InvoiceType): self
    {
        $this->InvoiceType = $InvoiceType;

        return $this;
    }

    public function isIsPaid(): ?bool
    {
        return $this->IsPaid;
    }

    public function setIsPaid(bool $IsPaid): self
    {
        $this->IsPaid = $IsPaid;

        return $this;
    }

    public function getInvoiceDate(): ?\DateTimeInterface
    {
        return $this->InvoiceDate;
    }

    public function setInvoiceDate(\DateTimeInterface $InvoiceDate): self
    {
        $this->InvoiceDate = $InvoiceDate;

        return $this;
    }

    public function getInvoiceNumber(): ?string
    {
        return $this->InvoiceNumber;
    }

    public function setInvoiceNumber(string $InvoiceNumber): self
    {
        $this->InvoiceNumber = $InvoiceNumber;

        return $this;
    }

    public function getSumNetto(): ?string
    {
        return $this->SumNetto;
    }

    public function setSumNetto(?string $SumNetto): self
    {
        $this->SumNetto = $SumNetto;

        return $this;
    }

    public function getSumBrutto(): ?string
    {
        return $this->SumBrutto;
    }

    public function setSumBrutto(?string $SumBrutto): self
    {
        $this->SumBrutto = $SumBrutto;

        return $this;
    }

    public function getTaxPct(): ?string
    {
        return $this->TaxPct;
    }

    public function setTaxPct(?string $TaxPct): self
    {
        $this->TaxPct = $TaxPct;

        return $this;
    }

    public function isNoTax(): ?bool
    {
        return $this->NoTax;
    }

    public function setNoTax(bool $NoTax): self
    {
        $this->NoTax = $NoTax;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->Comment;
    }

    public function setComment(?string $Comment): self
    {
        $this->Comment = $Comment;

        return $this;
    }

    public function getPdfFilename(): ?string
    {
        return $this->PdfFilename;
    }

    public function setPdfFilename(?string $PdfFilename): self
    {
        $this->PdfFilename = $PdfFilename;

        return $this;
    }

    public function getPdfFilesize(): ?int
    {
        return $this->PdfFilesize;
    }

    public function setPdfFilesize(?int $PdfFilesize): self
    {
        $this->PdfFilesize = $PdfFilesize;

        return $this;
    }

    public function getPdfMimeType(): ?string
    {
        return $this->PdfMimeType;
    }

    public function setPdfMimeType(?string $PdfMimeType): self
    {
        $this->PdfMimeType = $PdfMimeType;

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

    public function getPdfData()
    {
        return $this->PdfData;
    }

    public function setPdfData($PdfData): self
    {
        $this->PdfData = $PdfData;

        return $this;
    }
}
