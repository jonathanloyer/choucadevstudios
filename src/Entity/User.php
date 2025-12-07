<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\BillingDocument;
use App\Entity\MaintenanceContract;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'Cet email est déjà utilisé.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: 'L\'email est obligatoire.')]
    #[Assert\Email(message: 'L\'adresse email "{{ value }}" n\'est pas valide.')]
    private ?string $email = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $firstname = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $lastname = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Regex(
        pattern: '/^\+?[0-9 .-]{6,20}$/',
        message: 'Veuillez entrer un numéro de téléphone valide.'
    )]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $postalCode = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $city = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    /**
     * Mot de passe en clair utilisé uniquement pour les formulaires.
     * Non persisté en base de données.
     */
    #[Assert\Length(
        min: 6,
        minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.'
    )]
    private ?string $plainPassword = null;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: BillingDocument::class, orphanRemoval: true)]
    private Collection $billingDocuments;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: MaintenanceContract::class)]
    private Collection $maintenanceContracts;

    public function __construct()
    {
        $this->billingDocuments = new ArrayCollection();
        $this->maintenanceContracts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): static
    {
        $this->firstname = $firstname;
        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): static
    {
        $this->lastname = $lastname;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;
        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): static
    {
        $this->postalCode = $postalCode;
        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;
        return $this;
    }

    public function getFullName(): string
    {
        return trim(($this->firstname ?? '') . ' ' . ($this->lastname ?? ''));
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // garantit au moins ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function isAdmin(): bool
    {
        return in_array('ROLE_ADMIN', $this->getRoles(), true);
    }

    public function isClient(): bool
    {
        return in_array('ROLE_CLIENT', $this->getRoles(), true);
    }

    public function isSubcontractor(): bool
    {
        return in_array('ROLE_SUBCONTRACTOR', $this->getRoles(), true);
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Getter/Setter pour le mot de passe en clair (utilisé dans les formulaires).
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): static
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0" . self::class . "\0password"] = hash('crc32c', $this->password);
        return $data;
    }

    public function eraseCredentials(): void
    {
        // On efface le mot de passe en clair après utilisation
        $this->plainPassword = null;
    }

    public function getBillingDocuments(): Collection
    {
        return $this->billingDocuments;
    }

    public function addBillingDocument(BillingDocument $billingDocument): static
    {
        if (!$this->billingDocuments->contains($billingDocument)) {
            $this->billingDocuments->add($billingDocument);
            $billingDocument->setClient($this);
        }

        return $this;
    }

    public function removeBillingDocument(BillingDocument $billingDocument): static
    {
        if ($this->billingDocuments->removeElement($billingDocument)) {
            if ($billingDocument->getClient() === $this) {
                $billingDocument->setClient(null);
            }
        }

        return $this;
    }

    public function getMaintenanceContracts(): Collection
    {
        return $this->maintenanceContracts;
    }

    public function addMaintenanceContract(MaintenanceContract $contract): static
    {
        if (!$this->maintenanceContracts->contains($contract)) {
            $this->maintenanceContracts->add($contract);
            $contract->setClient($this);
        }

        return $this;
    }

    public function removeMaintenanceContract(MaintenanceContract $contract): static
    {
        $this->maintenanceContracts->removeElement($contract);
        return $this;
    }

    public function getActiveMaintenanceContract(): ?MaintenanceContract
    {
        foreach ($this->maintenanceContracts as $contract) {
            if ($contract->isActive()) {
                return $contract;
            }
        }

        return null;
    }
}
