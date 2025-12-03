<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\BillingDocument;
use App\Entity\MaintenanceContract;
use App\Repository\BillingDocumentRepository;
use App\Repository\MaintenanceContractRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/espace-client')]
class ClientDashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'client_dashboard')]
    public function index(
        BillingDocumentRepository $billingRepo,
        MaintenanceContractRepository $contractRepo
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_CLIENT');

        /** @var User $user */
        $user = $this->getUser();

        // Devis destinés au client (et pas aux sous-traitants)
        $quotes = $billingRepo->findBy(
            ['client' => $user, 'type' => 'quote', 'forSubcontractor' => false],
            ['sentAt' => 'DESC']
        );

        // Factures destinées au client
        $invoices = $billingRepo->findBy(
            ['client' => $user, 'type' => 'invoice', 'forSubcontractor' => false],
            ['sentAt' => 'DESC']
        );

        // Contrat de maintenance actif (status = 'active')
        $activeContract = $contractRepo->findOneBy([
            'client' => $user,
            'status' => 'active',
        ]);

        return $this->render('dashboard/client.html.twig', [
            'page_title'     => 'Espace client',
            'quotes'         => $quotes,
            'invoices'       => $invoices,
            'activeContract' => $activeContract,
        ]);
    }

    #[Route('/document/{id}', name: 'client_billing_download')]
    public function download(BillingDocument $document): BinaryFileResponse
    {
        $this->denyAccessUnlessGranted('ROLE_CLIENT');

        /** @var User $user */
        $user = $this->getUser();

        // Sécurité : le document doit appartenir au client connecté
        if ($document->getClient() !== $user) {
            throw $this->createAccessDeniedException('Ce document ne vous appartient pas.');
        }

        /** @var string $targetDir */
        $targetDir = $this->getParameter('billing_docs_dir');
        $filePath  = $targetDir . '/' . $document->getStoredFilename();

        if (!is_file($filePath)) {
            throw $this->createNotFoundException('Fichier introuvable.');
        }

        $response = new BinaryFileResponse($filePath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $document->getOriginalFilename()
        );

        return $response;
    }

    #[Route('/maintenance/cancel/{id}', name: 'client_maintenance_cancel', methods: ['POST'])]
    public function cancelMaintenance(
        MaintenanceContract $contract,
        Request $request,
        EntityManagerInterface $em
    ): RedirectResponse {
        $this->denyAccessUnlessGranted('ROLE_CLIENT');

        /** @var User $user */
        $user = $this->getUser();

        // CSRF
        if (!$this->isCsrfTokenValid(
            'cancel_maintenance_' . $contract->getId(),
            $request->request->get('_token')
        )) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('client_dashboard');
        }

        // Sécurité : le contrat doit appartenir au client connecté
        if ($contract->getClient() !== $user) {
            throw $this->createAccessDeniedException('Cet abonnement ne vous appartient pas.');
        }

        if (!$contract->isActive()) {
            $this->addFlash('info', 'Cet abonnement est déjà résilié.');
            return $this->redirectToRoute('client_dashboard');
        }

        $now = new \DateTimeImmutable();

        // On passe en "cancelled" et on fixe la date de fin
        $contract->setStatus('cancelled');
        $contract->setEndedAt($now);

        $em->flush();

        $this->addFlash('success', 'Votre abonnement a bien été résilié.');
        return $this->redirectToRoute('client_dashboard');
    }
}
