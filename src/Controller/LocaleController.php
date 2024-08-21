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
        $request->getSession()->set('_locale', $locale);
        $referer = $request->headers->get('referer');
        return new RedirectResponse($referer);
    }

}