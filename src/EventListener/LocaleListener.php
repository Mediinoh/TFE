<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Twig\Environment;

class LocaleListener
{
    private array $locales = [
        'en' => ['name' => 'English', 'flag' => 'gb'],
        'fr' => ['name' => 'Français', 'flag' => 'fr'],
    ];

    public function __construct(private RequestStack $requestStack, private string $defaultLocale = 'fr', private Environment $twig)
    {

    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->hasPreviousSession()) {
            return;
        }

        // Récupère la locale depuis la session ou utilise la locale par défaut
        $locale = $request->getSession()->get('_locale', $this->defaultLocale);
        $request->setLocale($locale);

        // dd($locale, $this->defaultLocale);

        // Passe les langues disponibles et la locale actuelle à twig
        $this->twig->addGlobal('locales', $this->locales);
        $this->twig->addGlobal('current_locale', $locale);
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        $response = $event->getResponse();
        $request = $this->requestStack->getCurrentRequest();
        $locale = $request->getLocale();

        $cookie = new Cookie('locale', $locale, strtotime('+1 month'));
        $response->headers->setCookie($cookie);
    }
}
