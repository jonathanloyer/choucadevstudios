<?php

namespace App\Controller;

use App\Entity\MaintenanceContract;
use App\Entity\MaintenancePlan;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/maintenance')]
class AdminMaintenanceController extends AbstractController
{
    #[Route('/new', name: 'admin_maintenance_new')]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        UserRepository $userRepo
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $formBuilder = $this->createFormBuilder();

        $formBuilder
            ->add('client', EntityType::class, [
                'class' => User::class,
                'label' => 'Client',
                'choice_label' => function (User $user) {
                    return $user->getFullName() ?: $user->getEmail();
                },
                // On limite aux utilisateurs qui ont ROLE_CLIENT
                'query_builder' => function (UserRepository $ur) {
                    return $ur->createQueryBuilder('u')
                        ->where('u.roles LIKE :role')
                        ->setParameter('role', '%"ROLE_CLIENT"%')
                        ->orderBy('u.lastname', 'ASC')
                        ->addOrderBy('u.firstname', 'ASC');
                },
            ])

            ->add('plan', EntityType::class, [
                'class' => MaintenancePlan::class,
                'label' => 'Formule de maintenance',
                // 👉 on affiche "Nom — 49 € / mois HT"
                'choice_label' => function (MaintenancePlan $plan) {
                    // adapte si ton champ s’appelle autrement
                    $name   = $plan->getName();
                    $price  = $plan->getPricePerMonth(); // en centimes
                    $euros  = $price / 100;

                    // 49 ou 79,99 suivant ton besoin
                    $formatted = number_format($euros, 0, ',', ' ');

                    return sprintf('%s — %s € / mois HT', $name, $formatted);
                },
            ])

            ->add('startedAt', DateType::class, [
                'label'  => 'Date de début',
                'widget' => 'single_text',
                'data'   => new \DateTimeImmutable(),
            ])
        ;

        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            /** @var User $client */
            $client = $data['client'];

            /** @var MaintenancePlan $plan */
            $plan   = $data['plan'];

            /** @var \DateTimeInterface $date */
            $date   = $data['startedAt'];

            $startedAt = $date instanceof \DateTimeImmutable
                ? $date
                : \DateTimeImmutable::createFromMutable($date);

            $contract = new MaintenanceContract();
            $contract->setClient($client);
            $contract->setPlan($plan);

            // 👉 on copie le prix depuis le plan
            $contract->setPricePerMonth($plan->getPricePerMonth());

            $contract->setStartedAt($startedAt);
            $contract->setStatus('active'); // normalement déjà la valeur par défaut

            $em->persist($contract);
            $em->flush();

            $this->addFlash('success', 'L’abonnement de maintenance a été créé.');

            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('dashboard/admin_maintenance_new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
