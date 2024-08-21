<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class LocaleListener
{
    public function __construct(private RequestStack $requestStack, private string $defaultLocale = 'fr')
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
