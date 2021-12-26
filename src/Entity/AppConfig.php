<?php

namespace App\Entity;

use App\Repository\AppConfigRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AppConfigRepository::class)]
#[ORM\UniqueConstraint(name: 'appcfg_uidx_1',columns: ["key_name","ref_user_id"])]
#[ORM\Table(name: 'app_config')]
class AppConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 100)]
    private $KeyName;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $Value;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private $RefUser;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getKeyName(): ?string
    {
        return $this->KeyName;
    }

    public function setKeyName(string $KeyName): self
    {
        $this->KeyName = $KeyName;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->Value;
    }

    public function setValue(?string $Value): self
    {
        $this->Value = $Value;

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
