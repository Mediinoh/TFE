<?php

namespace App\Controller;

// Importation des classes nécessaires
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConditionController extends AbstractController
{
    // Route pour afficher la page d'accueil
    #[Route('/condition', name: 'condition.index')]
    public function index(): Response
    {
        // Rendu de la vue pour la page d'accueil
        return $this->render('pages/condition/condition.html.twig');
    }
}
