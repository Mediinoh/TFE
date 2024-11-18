<?php

    namespace App\EventListener;

    use Symfony\Component\HttpKernel\Event\RequestEvent;
    use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
    use Symfony\Component\HttpKernel\KernelEvents;
    use Symfony\Component\EventDispatcher\EventSubscriberInterface;

    class FormCsrfListener implements EventSubscriberInterface
    {
        private $csrfTokenManager;

        public function __construct(CsrfTokenManagerInterface $csrfTokenManager)
        {
            $this->csrfTokenManager = $csrfTokenManager;
        }

        public static function getSubscribedEvents()
        {
            return [
                KernelEvents::REQUEST => 'onKernelRequest',
            ];
        }

        public function onKernelRequest(RequestEvent $event)
        {
            $request = $event->getRequest();

            // Vérifiez si l'URL commence par /api
            if (strpos($request->getPathInfo(), '/api') === 0) {
                // Désactivez la protection CSRF pour cette requête
                $request->attributes->set('disable_csrf', true);
            }
        }
    }
