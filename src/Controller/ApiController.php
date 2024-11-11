<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\InscriptionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController {

    public function __construct()
    {
        
    }

    #[Route('/api/inscription', 'api_inscription', methods: ['POST'])]
    public function apiInscription(Request $request, EntityManagerInterface $manager): JsonResponse
    {
        // Crée une nouvelle instance de l'entité
        $utilisateur = new Utilisateur();

        // Crée le formulaire et gère les données de requête
        $form = $this->createForm(InscriptionType::class, $utilisateur);
        $form->handleRequest($request);

        // Si le formulaire est valide, l'utilisateur est sauvée en DB
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($utilisateur);
            $manager->flush();

            return new JsonResponse([
                'message' => 'Utilisateur crée avec succès',
            ], JsonResponse::HTTP_CREATED);
        }

        // Si le formulaire n'est pas valide, retourne les erreurs
        $errors = [];
        foreach($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }

        return new JsonResponse([
            'errors' => $errors,
        ], JsonResponse::HTTP_BAD_REQUEST);
    }

    #[Route('/api/login', 'api_login', methods: ['POST'])]
    public function apiLogin(Request $request): JsonResponse
    {
        // Cette méthode sera traitée par le firewall de Symfony.
        return new JsonResponse([
            'message' => 'Authentification réussie',
        ], 200);
    }

    #[Route('/api/logout', 'api_logout', methods: ['POST'])]
    public function apiLogout(): void
    {
        // Symfony gère la déconnexion automatiquement
    }
}