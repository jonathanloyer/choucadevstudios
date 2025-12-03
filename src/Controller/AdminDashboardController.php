<?php

namespace App\Controller;

use App\Repository\BillingDocumentRepository;
use App\Repository\MaintenanceContractRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class AdminDashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'admin_dashboard')]
    public function index(
        BillingDocumentRepository $billingRepo,
        UserRepository $userRepo,
        MaintenanceContractRepository $contractRepo
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // 10 derniers documents
        $documents = $billingRepo->createQueryBuilder('b')
            ->leftJoin('b.client', 'c')->addSelect('c')
            ->orderBy('b.sentAt', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        // Tous les clients (ROLE_CLIENT)
        $clients = $userRepo->createQueryBuilder('u')
            ->where('u.roles LIKE :client')
            ->setParameter('client', '%"ROLE_CLIENT"%')
            ->orderBy('u.lastname', 'ASC')
            ->addOrderBy('u.firstname', 'ASC')
            ->getQuery()
            ->getResult();

        // Tous les sous-traitants (ROLE_SUBCONTRACTOR)
        $subcontractors = $userRepo->createQueryBuilder('u2')
            ->where('u2.roles LIKE :sub')
            ->setParameter('sub', '%"ROLE_SUBCONTRACTOR"%')
            ->orderBy('u2.lastname', 'ASC')
            ->addOrderBy('u2.firstname', 'ASC')
            ->getQuery()
            ->getResult();

        // Abonnements de maintenance actifs (10 derniers)
        $activeContracts = $contractRepo->createQueryBuilder('m')
            ->leftJoin('m.client', 'c')->addSelect('c')
            ->where('m.status = :status')
            ->setParameter('status', 'active')
            ->orderBy('m.startedAt', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        return $this->render('dashboard/admin.html.twig', [
            'page_title'      => 'Tableau de bord — Administration',
            'documents'       => $documents,
            'clients'         => $clients,
            'subcontractors'  => $subcontractors,
            'activeContracts' => $activeContracts,
        ]);
    }
}
