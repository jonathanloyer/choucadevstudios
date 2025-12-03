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
                ->text("Nom : {$contact->getName()}\nEmail : {$contact->getEmail()}\nSujet : {$contact->getSubject()}\n\nMessage :\n{$contact->getMessage()}");

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

            // plus tard : ici on pourra envoyer le mail au client
            // ...

            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('dashboard/admin_billing_new.html.twig', [
            'page_title' => 'Ajouter un devis / une facture',
            'form' => $form->createView(),
        ]);
    }
}
