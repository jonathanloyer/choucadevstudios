<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact')]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        MailerInterface $mailer
    ): Response
    {
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
}
