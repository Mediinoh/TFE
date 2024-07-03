<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;

final class DateTimeZoneListener
{
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        
        // Définir le fuseau horaire sur Europe/Brussels
        date_default_timezone_set('Europe/Brussels');
    }
}
