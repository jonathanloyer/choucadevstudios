<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardRedirectController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard_redirect')]
    public function dashboardRedirect(Security $security): Response
    {
        /** @var User|null $user */
        $user = $security->getUser();

        // Si l'utilisateur n'est pas connecté → on le renvoie vers la page de connexion
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $roles = $user->getRoles();

        // Admin
        if (in_array('ROLE_ADMIN', $roles, true)) {
            return $this->redirectToRoute('admin_dashboard');
        }

        // Sous-traitant
        if (in_array('ROLE_SUBCONTRACTOR', $roles, true)) {
            return $this->redirectToRoute('subcontractor_dashboard');
        }

        // Par défaut : client
        return $this->redirectToRoute('client_dashboard');
    }
}
