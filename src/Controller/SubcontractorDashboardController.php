<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\BillingDocument;
use App\Repository\BillingDocumentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/espace-sous-traitant')]
class SubcontractorDashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'subcontractor_dashboard')]
    public function index(BillingDocumentRepository $billingRepo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUBCONTRACTOR');

        /** @var User $user */
        $user = $this->getUser();

        // 👉 On ne récupère QUE les docs marqués pour sous-traitant
        $documents = $billingRepo->findBy(
            ['client' => $user, 'forSubcontractor' => true],
            ['sentAt' => 'DESC']
        );

        return $this->render('dashboard/subcontractor.html.twig', [
            'page_title' => 'Espace sous-traitant',
            'documents'  => $documents,
        ]);
    }

    #[Route('/document/{id}', name: 'subcontractor_billing_download')]
    public function download(BillingDocument $document): BinaryFileResponse
    {
        $this->denyAccessUnlessGranted('ROLE_SUBCONTRACTOR');

        /** @var User $user */
        $user = $this->getUser();

        // 🔐 Sécurité : le doc doit appartenir au sous-traitant
        // et être marqué comme document pour sous-traitant
        if ($document->getClient() !== $user || !$document->isForSubcontractor()) {
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
}
