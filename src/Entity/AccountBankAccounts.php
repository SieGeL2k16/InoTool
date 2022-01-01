<?php

namespace App\Entity;

use App\Repository\AccountBankAccountsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AccountBankAccountsRepository::class)]
#[ORM\UniqueConstraint(name: 'uidx_accbankaccs1',columns: ['ref_user_id','iban'])]
class AccountBankAccounts
  {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 40)]
    private $Iban;

    #[ORM\Column(type: 'string', length: 80)]
    private $BankName;

    #[ORM\Column(type: 'string', length: 10)]
    private $BankShortcut;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'cascade')]
    private $RefUser;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIban(): ?string
    {
        return $this->Iban;
    }

    public function setIban(string $Iban): self
    {
        $this->Iban = $Iban;

        return $this;
    }

    public function getBankName(): ?string
    {
        return $this->BankName;
    }

    public function setBankName(string $BankName): self
    {
        $this->BankName = $BankName;

        return $this;
    }

    public function getBankShortcut(): ?string
    {
        return $this->BankShortcut;
    }

    public function setBankShortcut(string $BankShortcut): self
    {
        $this->BankShortcut = $BankShortcut;

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
