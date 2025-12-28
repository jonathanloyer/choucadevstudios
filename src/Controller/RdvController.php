<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RdvController extends AbstractController
{
    #[Route('/prendrerdv', name: 'prendre_rdv')]
    public function index(): Response
    {
        return $this->render('rdv/index.html.twig', [
            'calendar_link' => 'https://calendar.app.google/N2xm4geNZ1ijuxNC7',
        ]);
    }
}
