<?php

    namespace App\EventListener;

use App\Entity\HistoriqueConnexion;
use App\Event\UserLoggedInEvent;
use Doctrine\ORM\EntityManagerInterface;

    class UserLoggedInListener
    {
        public function __construct(private EntityManagerInterface $manager)
        {
            
        }

        public function onUserLoggedIn(UserLoggedInEvent $event)
        {
            $utilisateur = $event->getUtilisateur();

            if (!is_null($utilisateur)) {
                $historiqueConnexion = new HistoriqueConnexion();
                $historiqueConnexion->setUtilisateur($utilisateur);

                $this->manager->persist($historiqueConnexion);
                $this->manager->flush();
            }
        }
    }