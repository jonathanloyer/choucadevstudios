<?php

namespace App\Entity;

use App\Repository\MaintenancePlanRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MaintenancePlanRepository::class)]
class MaintenancePlan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 80)]
    private string $name;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    // Prix mensuel en centimes (ex : 4900 = 49,00 €)
    #[ORM\Column]
    private int $pricePerMonth;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getPricePerMonth(): int
    {
        return $this->pricePerMonth;
    }

    public function setPricePerMonth(int $pricePerMonth): self
    {
        $this->pricePerMonth = $pricePerMonth;
        return $this;
    }
}
