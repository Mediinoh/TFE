<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PayementController extends AbstractController
{
    /**
     * Affiche la page de paiement.
     * 
     * @Route('/payement', name: 'app_payement')
     * @return Response Retourne une réponse HTML avec la vue de la page de paiement.
     */
    public function index(): Response
    {
        // Rendre la vue de la page de paiement
        return $this->render('payement/index.html.twig', [
            'controller_name' => 'PayementController',
        ]);
    }
}
