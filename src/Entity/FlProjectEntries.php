<?php

namespace App\Entity;

use App\Repository\FlProjectEntriesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FlProjectEntriesRepository::class)]
class FlProjectEntries
  {
  const RT_PROJECT_ENTRY      = 0;    // Standard record holding work items
  const RT_TRAVEL_ALLOWANCE   = 1;    // "Fahrkostenpauschale"

  const STATUS_ACTIVE         = 0;
  const STATUS_INACTIVE       = 1;
  const STATUS_ARCHIVED       = 2;
  
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $EntryDate = null;

    #[ORM\Column(nullable: true)]
    private ?int $WorkTimeInSecs = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false,onDelete: 'cascade')]
    private ?FlProjects $RefProject = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $RefUser = null;

    /** @var int|null '0 = active, 1 = inactive, 2 = archive' */
    #[ORM\Column]
    private ?int $Status = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $WorkDescription = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $CreatedOn = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $LastModifiedOn = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true,options: ['default' => 0])]
    private ?int $RecordType = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $Costs = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntryDate(): ?\DateTimeInterface
    {
        return $this->EntryDate;
    }

    public function setEntryDate(\DateTimeInterface $EntryDate): self
    {
        $this->EntryDate = $EntryDate;

        return $this;
    }

    public function getWorkTimeInSecs(): ?int
    {
        return $this->WorkTimeInSecs;
    }

    public function setWorkTimeInSecs(?int $WorkTimeInSecs): self
    {
        $this->WorkTimeInSecs = $WorkTimeInSecs;

        return $this;
    }

    public function getRefProject(): ?FlProjects
    {
        return $this->RefProject;
    }

    public function setRefProject(?FlProjects $RefProject): self
    {
        $this->RefProject = $RefProject;

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

    public function getStatus(): ?int
    {
        return $this->Status;
    }

    public function setStatus(int $Status): self
    {
        $this->Status = $Status;

        return $this;
    }

    public function getWorkDescription(): ?string
    {
        return $this->WorkDescription;
    }

    public function setWorkDescription(?string $WorkDescription): self
    {
        $this->WorkDescription = $WorkDescription;

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

    public function getRecordType(): ?int
    {
        return $this->RecordType;
    }

    public function setRecordType(?int $RecordType): static
    {
        $this->RecordType = $RecordType;

        return $this;
    }

    public function getCosts(): ?string
    {
        return $this->Costs;
    }

    public function setCosts(?string $Costs): static
    {
        $this->Costs = $Costs;

        return $this;
    }
}
