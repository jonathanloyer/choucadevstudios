<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ServiceController extends AbstractController
{
    #[Route('/services', name: 'services')]
    public function index(): Response
    {
        return $this->render('services/index.html.twig');
    }

    #[Route('/services/developpement-web', name: 'services_developpement_web')]
    public function devWeb(): Response
    {
        return $this->render('services/developpement_web.html.twig');
    }

    #[Route('/services/creation-contenu', name: 'services_creation_contenu')]
    public function creationContenu(): Response
    {
        return $this->render('services/creation_contenu.html.twig');
    }

    #[Route('/services/image-marque', name: 'services_image_marque')]
    public function imageMarque(): Response
    {
        return $this->render('services/image_marque.html.twig');
    }
}
