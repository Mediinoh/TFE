<?php

namespace App\EventListener;

// Importation des classes nécessaires pour la gestion de la langue via les événements 'RequestEvent' et 'ResponseEvent'
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Twig\Environment;

/**
 * La classe 'LocaleListener' gère la langue de l'interface en fonction de l'interface en fonction des préférences de l'utilisateur stockées en session ou dans un cookie.
 */
class LocaleListener
{
    // Liste des langues disponibles avec leur nom et leur drapeau associés
    private array $locales = [
        'en' => ['name' => 'English', 'flag' => 'gb'],
        'fr' => ['name' => 'Français', 'flag' => 'fr'],
    ];

    // Constructeur de la classe injectant les dépendances
    public function __construct(private RequestStack $requestStack, private Environment $twig, private string $defaultLocale = 'fr')
    {

    }

    /**
     * Gère la requête entrante en définissant la langue de l'application
     */
    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();

        // Si la session n'existe pas encore, ne fait rien
        if (!$request->hasPreviousSession()) {
            return;
        }

        // Récupère la locale depuis la session ou utilise la locale par défaut
        $locale = $request->getSession()->get('_locale', $this->defaultLocale);
        $request->setLocale($locale);

        // Passe les langues disponibles et la locale actuelle à Twig pour rendre ces données accessibles globalement
        $this->twig->addGlobal('locales', $this->locales);
        $this->twig->addGlobal('current_locale', $locale);
    }

    /**
     * Gère la réponse en enregistrant la langue actuelle dans un cookie pour la persistance
     */
    public function onKernelResponse(ResponseEvent $event)
    {
        $response = $event->getResponse();
        $request = $this->requestStack->getCurrentRequest();
        $locale = $request->getLocale();

        // Crée un cookie 'locale' avec la langue actuelle, expirant dans un mois
        $cookie = new Cookie('locale', $locale, strtotime('+1 month'));
        $response->headers->setCookie($cookie);
    }
}
