<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\BillingDocumentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

        $documents = $billingRepo->findBy(
            ['client' => $user, 'forSubcontractor' => true],
            ['sentAt' => 'DESC']
        );

        return $this->render('dashboard/subcontractor.html.twig', [
            'page_title' => 'Espace sous-traitant',
            'documents'  => $documents,
        ]);
    }
}
