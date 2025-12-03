<?php

namespace App\Repository;

use App\Entity\MaintenanceContract;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MaintenanceContractRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MaintenanceContract::class);
    }

    /**
     * Retourne le contrat actif d’un client (ou null).
     */
    public function findActiveForClient(User $user): ?MaintenanceContract
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.client = :client')
            ->andWhere('c.status = :status')
            ->setParameter('client', $user)
            ->setParameter('status', 'active')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
