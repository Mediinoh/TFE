<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;

class LocaleListener
{
    public function __construct(private string $defaultLocale = 'fr')
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
}
