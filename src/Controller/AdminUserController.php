<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/users')]
class AdminUserController extends AbstractController
{
    #[Route('/', name: 'admin_users_index')]
    public function index(UserRepository $userRepo): Response
    {
        // sécurité : seulement admin
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $users = $userRepo->findBy([], ['lastname' => 'ASC', 'firstname' => 'ASC']);

        return $this->render('dashboard/users_index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/{id}/role', name: 'admin_user_edit_role')]
    public function editRole(
        User $user,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // on détermine le rôle principal actuel
        if ($user->isAdmin()) {
            $current = 'ROLE_ADMIN';
        } elseif ($user->isSubcontractor()) {
            $current = 'ROLE_SUBCONTRACTOR';
        } else {
            $current = 'ROLE_CLIENT';
        }

        $form = $this->createFormBuilder()
            ->add('role', ChoiceType::class, [
                'label'   => 'Rôle principal',
                'choices' => [
                    'Client'         => 'ROLE_CLIENT',
                    'Sous-traitant'  => 'ROLE_SUBCONTRACTOR',
                    'Administrateur' => 'ROLE_ADMIN',
                ],
                'data' => $current,
            ])
            ->getForm()
        ;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $selected = $form->get('role')->getData();

            // on force les rôles à partir du rôle principal choisi
            $roles = ['ROLE_USER', $selected];
            $user->setRoles($roles);

            $em->flush();

            $this->addFlash(
                'success',
                'Rôle mis à jour pour '.$user->getFullName()
            );

            return $this->redirectToRoute('admin_users_index');
        }

        return $this->render('dashboard/user_edit_role.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
