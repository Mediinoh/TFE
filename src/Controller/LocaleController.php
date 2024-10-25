<?php

namespace App\Controller;

// Importation des classes nécessaires
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class LocaleController extends AbstractController
{
    // Route pour changer la langue (locale) de l'application
    #[Route('/changeLocale/{locale}', name: 'changeLocale', methods: ['GET'])]
    public function changeLocale(SessionInterface $session, Request $request, string $locale)
    {
        // Démarrer manuellement la session (si besoin)
        if (!$session->isStarted()) {
            $session->start();
        }

        // Stocke la langue dans la session sous la clé '_locale'
        $request->getSession()->set('_locale', $locale);

        // Redirige vers la page précédente pour appliquer la nouvelle langue
        $referer = $request->headers->get('referer'); // Récupère l'URL de la page précédente
        return new RedirectResponse($referer); // Redirige l'utilisateur vers cette URL
    }
}