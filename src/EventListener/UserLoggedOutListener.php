<?php

namespace App\EventListener;

// Importation des classes nécessaires pour gérer les entités et l'évennement de déconnexion
use App\Entity\LigneCommande;
use App\Entity\Panier;
use App\Event\UserLoggedInEvent;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Classe 'UserLoggedOutListener' qui écoute les événements de déconnexion utilisateur.
 * Elle permet de sauvegarder le panier d'un utilisateur lorsqu'il se déconnecte.
 */
class UserLoggedOutListener
{
    // Constructeur de la classe pour injecter les dépendances nécessaires
    public function __construct(private EntityManagerInterface $manager, private ArticleRepository $articleRepository, private RequestStack $requestStack)
    {
        
    }

    /**
     * Méthode déclenchée à la déconnexion de l'utilisateur.
     * Elle enregistre le contenu du panier en base de données et le supprime de la session.
     */
    public function onUserLoggedOut(UserLoggedInEvent $event)
    {
        // Récupération de l'utilisateur depuis l'événement
        $utilisateur = $event->getUtilisateur();
        $session = $this->requestStack->getSession();

        // Vérification que l'utilisateur n'est pas null
        if (!is_null($utilisateur)) {
            // Récupération de l'ID utilisateur et du panier en session
            $userId = $utilisateur->getId();
            $panierSession = $session->get('panier', []);
            $articlesPanier = []; // Tableau pour stocker les articles du panier

            // Variables pour calculer le total et la quantité totale du panier
            $total = 0;
            $quantiteTotale = 0;

            // Vérifie si le panier contient des articles pour cet utilisateur
            if (isset($panierSession[$userId])) {
                foreach($panierSession[$userId] as $articleId => $quantite) {
                    // Recherche de l'article en base de données et calcul du total pour cet article
                    $article = $this->articleRepository->find($articleId);
                    $prixTotalArticle = $article->getPrixUnitaire() * $quantite;
                    $total += $prixTotalArticle;
                    $quantiteTotale += $quantite;

                    // Ajoute l'article au tableau des articles du panier
                    $articlesPanier[] = [
                        'article' => $article,
                        'quantite' => $quantite,
                        'prix_total' => $prixTotalArticle,
                    ];
                }
            }

            // Création d'un nouvel objet Panier pour stocker les articles et totaliser le montant
            $panier = new Panier();
            $panier->setUtilisateur($utilisateur)->setMontantTotal($total);
            $this->manager->persist($panier); // Persistance de l'entité Panier
            
            // Ajoute chaque article comme ligne de commande associée au panier
            foreach($articlesPanier as $articlePanier) {
                $ligneCommande = new LigneCommande();
                $ligneCommande->setArticle($articlePanier['article'])
                                ->setPanier($panier)
                                ->setQuantite($articlePanier['quantite'])
                                ->setPrix($articlePanier['prix_total']);
                $this->manager->persist($ligneCommande); // Persistance de chaque LigneCommande
            }
            
            // Enregistrement des modifications en base de données
            $this->manager->flush();

            // Suppression du panier de la session après la sauvegarde en base de données
            $session->remove('panier');
        }
    }
}
