<?php

namespace App\Entity;

use App\Repository\BillingDocumentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BillingDocumentRepository::class)]
class BillingDocument
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // "quote" = devis, "invoice" = facture, "other" = autre document
    #[ORM\Column(length: 20)]
    private ?string $type = null;

    // Destinataire (client ou sous-traitant)
    #[ORM\ManyToOne(inversedBy: 'billingDocuments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $client = null;

    #[ORM\Column(length: 255)]
    private ?string $originalFilename = null;

    #[ORM\Column(length: 255)]
    private ?string $storedFilename = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    // true = document destiné à un sous-traitant, false = à un client
    #[ORM\Column(options: ['default' => false])]
    private bool $forSubcontractor = false;

    // Date à laquelle l'admin l’a "envoyé" (mis à dispo)
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $sentAt = null;

    // Date à laquelle le destinataire l’a consulté
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $viewedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getClient(): ?User
    {
        return $this->client;
    }

    public function setClient(?User $client): static
    {
        $this->client = $client;

        return $this;
    }

    public function getOriginalFilename(): ?string
    {
        return $this->originalFilename;
    }

    public function setOriginalFilename(string $originalFilename): static
    {
        $this->originalFilename = $originalFilename;

        return $this;
    }

    public function getStoredFilename(): ?string
    {
        return $this->storedFilename;
    }

    public function setStoredFilename(string $storedFilename): static
    {
        $this->storedFilename = $storedFilename;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function isForSubcontractor(): bool
    {
        return $this->forSubcontractor;
    }

    public function setForSubcontractor(bool $forSubcontractor): static
    {
        $this->forSubcontractor = $forSubcontractor;

        return $this;
    }

    public function getSentAt(): ?\DateTimeImmutable
    {
        return $this->sentAt;
    }

    public function setSentAt(?\DateTimeImmutable $sentAt): static
    {
        $this->sentAt = $sentAt;

        return $this;
    }

    public function getViewedAt(): ?\DateTimeImmutable
    {
        return $this->viewedAt;
    }

    public function setViewedAt(?\DateTimeImmutable $viewedAt): static
    {
        $this->viewedAt = $viewedAt;

        return $this;
    }

    /** Petit helper pour l'affichage */
    public function getTypeLabel(): string
    {
        return match ($this->type) {
            'quote'   => 'Devis',
            'invoice' => 'Facture',
            default   => 'Document',
        };
    }

    public function isViewed(): bool
    {
        return $this->viewedAt !== null;
    }
}
