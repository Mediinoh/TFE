<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LocaleController extends AbstractController
{

    #[Route('/changeLocale/{locale}', name: 'changeLocale', methods: ['GET'])]
    public function changeLocale(Request $request, string $locale)
    {
        // Stocke la langue dans la session
        $request->getSession()->set('_locale', $locale);

        // Redirige vers la page précédente
        $referer = $request->headers->get('referer');
        return new RedirectResponse($referer);
    }

}