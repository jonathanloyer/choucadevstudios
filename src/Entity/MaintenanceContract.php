<?php

namespace App\Entity;

use App\Repository\MaintenanceContractRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;

#[ORM\Entity(repositoryClass: MaintenanceContractRepository::class)]
class MaintenanceContract
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'maintenanceContracts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $client = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?MaintenancePlan $plan = null;

    // Copie du prix mensuel en centimes au moment de la souscription
    #[ORM\Column]
    private int $pricePerMonth;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $startedAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $endedAt = null;

    // 'active', 'cancel_requested', 'cancelled'
    #[ORM\Column(length: 20)]
    private string $status = 'active';

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function getClient(): ?User
    {
        return $this->client;
    }

    public function setClient(User $client): self
    {
        $this->client = $client;
        return $this;
    }

    public function getPlan(): ?MaintenancePlan
    {
        return $this->plan;
    }

    public function setPlan(MaintenancePlan $plan): self
    {
        $this->plan = $plan;
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

    public function getStartedAt(): \DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function setStartedAt(\DateTimeImmutable $startedAt): self
    {
        $this->startedAt = $startedAt;
        return $this;
    }

    public function getEndedAt(): ?\DateTimeImmutable
    {
        return $this->endedAt;
    }

    public function setEndedAt(?\DateTimeImmutable $endedAt): self
    {
        $this->endedAt = $endedAt;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isCancelRequested(): bool
    {
        return $this->status === 'cancel_requested';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
}
