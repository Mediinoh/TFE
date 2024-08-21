<?php

namespace App\EventListener;

use App\Entity\HistoriqueConnexion;
use App\Event\UserLoggedInEvent;
use App\Repository\PanierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class UserLoggedInListener
{
    public function __construct(private EntityManagerInterface $manager, private PanierRepository $panierRepository, private RequestStack $requestStack)
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

            $session = $this->requestStack->getSession();

            if (!$session->has('panier')) {
                $session->set('panier', []);
            }
    
            $panier = $session->get('panier');
            $userId = $utilisateur->getId();
    
            if (!isset($panier[$userId])) {
                $panier[$userId] = [];
            }

            $panierData = $this->panierRepository->findLastPanierNotInHistoriqueAchats($userId);

            dd($panierData);

            $session->set('panier', $panier);
        }
    }
}
