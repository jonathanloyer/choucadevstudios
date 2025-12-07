<?php

namespace App\Controller;

use App\Entity\BillingDocument;
use App\Form\BillingDocumentType;
use App\Entity\Contact;
use App\Form\ContactType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\String\Slugger\SluggerInterface;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact')]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        MailerInterface $mailer
    ): Response {
        $contact = new Contact();

        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        /**
         * 💡 Pré-remplissage du sujet si on arrive depuis un bouton
         * "Demander un devis" avec un paramètre ?offer=...
         * (uniquement si le formulaire n’a pas encore été soumis)
         */
        if (!$form->isSubmitted()) {
            $offer = $request->query->get('offer');

            if ($offer) {
                $subject = match ($offer) {
                    // 🌐 Sites vitrines
                    'site-vitrine-starter'   => 'Demande de devis — Site vitrine Chouca Starter',
                    'site-vitrine-pro'       => 'Demande de devis — Site vitrine Chouca Pro',
                    'site-vitrine-signature' => 'Demande de devis — Site vitrine Chouca Signature',

                    // 📹 Création de contenu (packs)
                    'contenu-starter'        => 'Demande d’accompagnement — Pack Starter (création de contenu)',
                    'contenu-production'     => 'Demande d’accompagnement — Pack Production (création de contenu)',
                    'contenu-strategie'      => 'Demande d’accompagnement — Pack Stratégie & croissance (création de contenu)',

                    // 🎨 Visuels / image de marque / 3D
                    'visuels-identite'       => 'Demande de devis — Identité visuelle & branding',
                    'visuels-marketing-video'=> 'Demande de devis — Vidéos & marketing digital',
                    'visuels-3d'             => 'Demande de devis — Conception & 3D personnalisée',

                    // 🛠 Maintenance
                    'maintenance-care'       => 'Demande d’abonnement — Maintenance Chouca Care',
                    'maintenance-guard'      => 'Demande d’abonnement — Maintenance Chouca Guard',
                    'maintenance-ops'        => 'Demande d’abonnement — Maintenance Chouca Ops',

                    // Valeur par défaut si on ne reconnaît pas l’offre
                    default                  => 'Demande de devis depuis le site ChoucaDev Studios',
                };

                // On met à jour l’entité + le champ du formulaire
                $contact->setSubject($subject);
                if ($form->has('subject')) {
                    $form->get('subject')->setData($subject);
                }
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {

            // 1️⃣ Sauvegarde en base
            $contact->setCreatedAt(new \DateTimeImmutable());
            $em->persist($contact);
            $em->flush();

            // 2️⃣ Envoi email
            $email = (new Email())
                ->from('contact@choucadev-studios.fr')
                ->to('contact@choucadev-studios.fr')
                ->subject('Nouvelle demande de contact')
                ->text(
                    "Nom : {$contact->getName()}\n" .
                    "Email : {$contact->getEmail()}\n" .
                    "Sujet : {$contact->getSubject()}\n\n" .
                    "Message :\n{$contact->getMessage()}"
                );

            $mailer->send($email);

            // 3️⃣ Message flash
            $this->addFlash('success', 'Votre message a bien été envoyé. Je vous répondrai rapidement !');

            return $this->redirectToRoute('contact');
        }

        return $this->render('contact/index.html.twig', [
            'contactForm' => $form->createView(),
        ]);
    }

    #[Route(path: '/admin/documents/nouveau', name: 'admin_billing_new')]
    public function uploadBillingDocument(
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $billingDocument = new BillingDocument();
        $form = $this->createForm(BillingDocumentType::class, $billingDocument);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile */
            $uploadedFile = $form->get('file')->getData();

            if ($uploadedFile) {
                $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $uploadedFile->guessExtension();

                // Déplacement du fichier dans le dossier configuré
                $uploadedFile->move(
                    $this->getParameter('billing_docs_dir'),
                    $newFilename
                );

                $billingDocument
                    ->setOriginalFilename($uploadedFile->getClientOriginalName())
                    ->setStoredFilename($newFilename);
            }

            $em->persist($billingDocument);
            $em->flush();

            $this->addFlash('success', 'Le document a bien été enregistré pour le client.');

            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('dashboard/admin_billing_new.html.twig', [
            'page_title' => 'Ajouter un devis / une facture',
            'form'       => $form->createView(),
        ]);
    }
}
