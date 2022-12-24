<?php

namespace App\Entity;

use App\Repository\FlProjectsRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FlProjectsRepository::class)]
class FlProjects
  {
  /** @var int Project Status values */
  const PROJ_STATUS_ACTIVE    = 0;
  const PROJ_STATUS_INACTIVE  = 1;
  
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false,onDelete: 'cascade')]
    private ?FlCustomer $RefCustomer = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false,onDelete: 'cascade')]
    private ?User $RefUser = null;

    #[ORM\Column(length: 255)]
    private ?string $ProjectName = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $Description = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $Status = null;

    #[ORM\Column]
    private ?int $WorkUnit = null;

    #[ORM\Column]
    private ?int $PayPerWorkUnit = null;

    #[ORM\Column(length: 10)]
    private ?string $Currency = null;

    #[ORM\Column]
    private ?int $TimeBudget = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $CreatedOn = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $LastModifiedOn = null;

    #[ORM\Column(nullable: true)]
    private ?bool $NoReporting = null;

    public function __construct() {
      $this->Currency       = 'EUR';
      $this->CreatedOn      = new DateTimeImmutable();
      $this->Status         = self::PROJ_STATUS_ACTIVE;       // Active
      $this->WorkUnit       = 60;                             // 60 Minutes
      $this->PayPerWorkUnit = 70;                             // 70 EUR / Hour
      $this->TimeBudget     = 0;
    }
    
    public function getId(): ?int
    {
        return $this->id;
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

    public function getRefUser(): ?User
    {
        return $this->RefUser;
    }

    public function setRefUser(?User $RefUser): self
    {
        $this->RefUser = $RefUser;

        return $this;
    }

    public function getProjectName(): ?string
    {
        return $this->ProjectName;
    }

    public function setProjectName(string $ProjectName): self
    {
        $this->ProjectName = $ProjectName;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->Description;
    }

    public function setDescription(?string $Description): self
    {
        $this->Description = $Description;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->Status;
    }

    public function setStatus(int $Status): self
    {
        $this->Status = $Status;

        return $this;
    }

    public function getWorkUnit(): ?int
    {
        return $this->WorkUnit;
    }

    public function setWorkUnit(int $WorkUnit): self
    {
        $this->WorkUnit = $WorkUnit;

        return $this;
    }

    public function getPayPerWorkUnit(): ?int
    {
        return $this->PayPerWorkUnit;
    }

    public function setPayPerWorkUnit(int $PayPerWorkUnit): self
    {
        $this->PayPerWorkUnit = $PayPerWorkUnit;

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

    public function getTimeBudget(): ?int
    {
        return $this->TimeBudget;
    }

    public function setTimeBudget(int $TimeBudget): self
    {
        $this->TimeBudget = $TimeBudget;

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

    public function isNoReporting(): ?bool
    {
        return $this->NoReporting;
    }

    public function setNoReporting(?bool $NoReporting): self
    {
        $this->NoReporting = $NoReporting;

        return $this;
    }
}
