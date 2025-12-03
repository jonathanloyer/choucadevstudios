<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Entity\BillingDocument;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

#[Route('/admin/billing')]
class AdminBillingController extends AbstractController
{
    #[Route('/new', name: 'admin_billing_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        // Sécurité : uniquement admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Formulaire d’upload d’un document de facturation
        $form = $this->createFormBuilder()
            ->add('user', EntityType::class, [
                'class' => User::class,
                'label' => 'Destinataire',

                // Regroupe dans 2 (ou 3) sections : Clients / Sous-traitants / Admins
                'group_by' => function (User $user) {
                    if ($user->isSubcontractor()) {
                        return 'Sous-traitants';
                    }

                    if ($user->isAdmin()) {
                        return 'Administrateurs';
                    }

                    return 'Clients';
                },

                // Affiche un préfixe clair dans chaque option
                'choice_label' => function (User $user) {
                    if ($user->isSubcontractor()) {
                        $type = 'Sous-traitant';
                    } elseif ($user->isAdmin()) {
                        $type = 'Admin';
                    } else {
                        $type = 'Client';
                    }

                    $name = $user->getFullName() ?: $user->getEmail();

                    return sprintf('[%s] %s', $type, $name);
                },
            ])
            ->add('type', ChoiceType::class, [
                'label'   => 'Type de document',
                'choices' => [
                    'Devis'           => 'quote',
                    'Facture'         => 'invoice',
                    'Autre document'  => 'other',
                ],
            ])
            ->add('file', FileType::class, [
                'label'    => 'Fichier PDF',
                'mapped'   => false,
                'required' => true,
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form->get('file')->getData();
            
            /** @var User $client */
            // 👉 Correction : le champ s'appelle "user", pas "client"
            $client = $form->get('user')->getData();

            $type = $form->get('type')->getData();

            // Dossier défini dans services.yaml
            /** @var string $targetDir */
            $targetDir = $this->getParameter('billing_docs_dir');

            // Nom unique
            $newFilename = uniqid('doc_', true) . '.' . $uploadedFile->guessExtension();

            // Déplace le fichier
            $uploadedFile->move($targetDir, $newFilename);

            // Enregistre en base
            $doc = new BillingDocument();
            $doc->setClient($client);
            $doc->setType($type);
            $doc->setOriginalFilename($uploadedFile->getClientOriginalName());
            $doc->setStoredFilename($newFilename);
            $doc->setForSubcontractor($client->isSubcontractor());
            $doc->setSentAt(new \DateTimeImmutable());

            $em->persist($doc);
            $em->flush();

            $this->addFlash('success', 'Le document a été ajouté et associé à l’utilisateur.');

            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('dashboard/admin_billing_new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/delete/{id}', name: 'admin_billing_delete', methods: ['POST'])]
    public function delete(
        BillingDocument $billingDocument,
        Request $request,
        EntityManagerInterface $em
    ): RedirectResponse {
        
        // Sécurité : seulement admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Vérification CSRF
        if (!$this->isCsrfTokenValid('delete_billing_' . $billingDocument->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token de sécurité invalide, suppression annulée.');
            return $this->redirectToRoute('admin_dashboard');
        }

        // Chemin du fichier
        $targetDir = $this->getParameter('billing_docs_dir');
        $filePath  = $targetDir . '/' . $billingDocument->getStoredFilename();

        // Suppression du fichier physique
        if (is_file($filePath)) {
            @unlink($filePath);
        }

        // Suppression BDD
        $em->remove($billingDocument);
        $em->flush();

        $this->addFlash('success', 'Le document a bien été supprimé.');

        return $this->redirectToRoute('admin_dashboard');
    }
}
