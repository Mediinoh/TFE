<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Classe 'DateTimeZoneListener' pour définir le fuseau horaire de l'application lors de chaque requête
 */
final class DateTimeZoneListener
{
    // Méthode déclenchée à chaque requête pour fixer le fuseau horaire
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        
        // Définit le fuseau horaire sur 'Europe/Brussels' pour l'application
        date_default_timezone_set('Europe/Brussels');
    }
}
