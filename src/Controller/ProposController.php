<?php

namespace App\Controller;

// Importation des classes nécessaires
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProposController extends AbstractController
{
    // Route pour afficher la page "À propos"
    #[Route('/propos', name: 'propos.index', methods: ['GET'])]
    public function index(): Response
    {
        // Rendu de la vue pour la page "À propos"
        return $this->render('pages/propos/index.html.twig');
    }
}
