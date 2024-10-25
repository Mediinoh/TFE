<?php

namespace App\EventListener;

// Importation des classes nécessaires pour la gestion des connexions et de la session
use App\Entity\HistoriqueConnexion;
use App\Event\UserLoggedInEvent;
use App\Repository\PanierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Classe 'UserLoggedInListener' écoute l'évenement de connexion utilisateur.
 * Elle crée une entrée de connexion l'historique de connexion dans l'historique et synchronise le panier utilisateur.
 */
class UserLoggedInListener
{
    // Constructeur injectant les dépendances requises pour l'écouteur d'événements
    public function __construct(private EntityManagerInterface $manager, private PanierRepository $panierRepository, private RequestStack $requestStack)
    {
        
    }

    /**
     * Méthode exécutée lors de la connexion utilisateur.
     * Elle ajoute un enregistrement dans 'HistoriqueConnexion' et synchronise le panier en session.
     */
    public function onUserLoggedIn(UserLoggedInEvent $event)
    {
        // Récupère l'utilisateur de l'événement
        $utilisateur = $event->getUtilisateur();

        // Vérifie que l'utilisateur est valide (non nul)
        if (!is_null($utilisateur)) {
            // Création d'une nouvelle instance 'HistoriqueConnexion' et synchronise le panier en session.
            $historiqueConnexion = new HistoriqueConnexion();
            $historiqueConnexion->setUtilisateur($utilisateur);

            // Persiste l'historique de connexion dans la base de données
            $this->manager->persist($historiqueConnexion);
            $this->manager->flush();

            // Accède à la session utilisateur
            $session = $this->requestStack->getSession();

            // Initialise la session 'panier' si elle n'existe pas
            if (!$session->has('panier')) {
                $session->set('panier', []);
            }
    
            $panier = $session->get('panier'); // Récupère le panier actuel de la session
            $userId = $utilisateur->getId(); // Récupère l'ID de l'utilisateur connecté
    
            // Si aucun panier n'est associé à cet utilisateur, initialise un panier vide
            if (!isset($panier[$userId])) {
                $panier[$userId] = [];
            }

            // Récupère les données du dernier panier non finalisé pour l'utilisateur
            $panierData = $this->panierRepository->findLastPanierNotInHistoriqueAchats($userId);

            // Met à jour la session avec le panier actuel
            $session->set('panier', $panier);
        }
    }
}
