<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class LocaleController extends AbstractController
{

    #[Route('/changeLocale/{locale}', name: 'changeLocale', methods: ['GET'])]
    public function changeLocale(SessionInterface $session, Request $request, string $locale)
    {
        // Démarrer manuellement la session (si besoin)
        if (!$session->isStarted()) {
            $session->start();
        }

        // Stocke la langue dans la session
        $request->getSession()->set('_locale', $locale);

        // Redirige vers la page précédente
        $referer = $request->headers->get('referer');
        return new RedirectResponse($referer);
    }

}