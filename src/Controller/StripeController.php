<?php

namespace App\Controller;

use App\Entity\HistoriqueAchat;
use App\Entity\LigneCommande;
use App\Entity\Panier;
use App\Entity\Utilisateur;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StripeController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager, private Packages $assets)
    {

    }

    // Route pour créer une session de paiement
    #[Route('/create-checkout-session', name: 'stripe_checkout', methods: ['POST'])]
    public function checkout(Request $request, SessionInterface $session, ArticleRepository $articleRepository): Response
    {
        /** @var Utilisateur $utilisateur */
        $utilisateur = $this->getUser(); // Récupère l'utilisateur connecté
        $userId = $utilisateur->getId(); // Récupère l'ID de l'utilisateur
        $panier = $session->get('panier', []); // Récupère le panier de la session

        $lineItems = []; // Initialisation des articles de la ligne pour Stripe
        $panierTotal = 0; // Initialisation du montant total du panier

        // Vérifie si le panier de l'utilisateur existe
        if (isset($panier[$userId])) {
            $panierUtilisateur = $panier[$userId];
            
            // Parcourt les articles du panier
            foreach ($panierUtilisateur as $articleId => $quantite) {
                $article = $articleRepository->find($articleId); // Récupère l'article par ID

                if ($article) {
                    // Récupère l'URL de l'image de l'article
                    $imageUrl = $this->assets->getUrl('images/articles/' . $article->getPhotoArticle());
                    $absoluteImageUrl = $this->getParameter('app_base_url') . $imageUrl; // Crée l'URL absolue

                    // AJoute les données de l'article à la ligne d'items pour Stripe
                    $lineItems[] = [
                        'price_data' => [
                            'currency' => 'eur', // Devise en euros
                            'product_data' => [
                                'name' => $article->getTitre(), // Titre de l'article
                                'images' => [], // Images de l'article
                            ],
                            'unit_amount' => $article->getPrixUnitaire() * 100, // Montant en centimes
                        ],
                        'quantity' => $quantite, // Quantité de l'article
                    ];
                    $panierTotal += $article->getPrixUnitaire() * $quantite; // Ajoute au total
                }
            }
        }

        // Configuration de l'API Stripe avec la clé secrète
        Stripe::setApiKey($this->getParameter('stripe_secret'));

        // Création d'une nouvelle session de paiement Stripe
        $checkoutSession = StripeSession::create([
            'payment_method_types' => ['card'], // Types de paiement
            'line_items' => $lineItems, // Articles de la ligne
            'mode' => 'payment', // Mode paiement
            'success_url' => $this->generateUrl('stripe_success', [], UrlGeneratorInterface::ABSOLUTE_URL), // URL de succès
            'cancel_url' => $this->generateUrl('stripe_error', [], UrlGeneratorInterface::ABSOLUTE_URL), // URL d'annulation
        ]);

        // Retourne l'ID de la session de paiement sous forme de JSON
        return $this->json(['id' => $checkoutSession->id]);
    }

    // Route pour traiter les succès de paiement
    #[Route('/stripe_success', name: 'stripe_success')]
    public function success(SessionInterface $session, ArticleRepository $articleRepository): Response
    {
        /** @var Utilisateur $utilisateur */
        $utilisateur = $this->getUser(); // Récupère l'utilisateur connecté
        $userId = $utilisateur->getId(); // Récupère l'ID de l'utilisateur
        $panier = $session->get('panier', []); // Récupère le panier de la session

        // Vérifie si le panier de l'utilisateur existe
        if (isset($panier[$userId])) {
            $panierUtilisateur = $panier[$userId];

            // Crée une nouvelle entité Panier
            $panierEntity = new Panier();
            $panierEntity->setUtilisateur($utilisateur); // Associe l'utilisateur au panier
            $panierEntity->setMontantTotal(0); // Initialisation du montant total

            // Persiste le panier en base de données
            $this->entityManager->persist($panierEntity);
            // Enregistre les changements en base de données
            $this->entityManager->flush();

            // Créer un nouvel enregistrement HistoriqueAchat
            $historiqueAchat = new HistoriqueAchat();
            $historiqueAchat->setUtilisateur($utilisateur); // Associe l'utilisateur à l'historique d'achat
            $historiqueAchat->setMontantTotal(0);  // Montant total sera mis à jour plus tard
            $historiqueAchat->setPanier($panierEntity); // Associe le panier à l'historique

            // Persiste l'historique d'achat en base de données
            $this->entityManager->persist($historiqueAchat);
            // Enregistre les changements en base de données
            $this->entityManager->flush();

            $montantTotal = 0; // Initialisation du montant total à zéro

            // Parcourt les articles du panier
            foreach ($panierUtilisateur as $articleId => $quantite) {
                $article = $articleRepository->find($articleId); // Récupère l'article par ID

                if ($article) {
                    $prixTotalArticle = $article->getPrixUnitaire() * $quantite; // Calcul le prix total de l'article
                    $montantTotal += $prixTotalArticle; // Ajoute au montant total

                    // Créer une nouvelle LigneCommande pour chaque article
                    $ligneCommande = new LigneCommande();
                    $ligneCommande->setArticle($article); // Associe l'article à la ligne de commande
                    $ligneCommande->setQuantite($quantite); // Définit la quantité
                    $ligneCommande->setPrix($article->getPrixUnitaire()); // Définit le prix unitaire
                    $ligneCommande->setPanier($panierEntity); // Associe le panier à la ligne de commande

                    // Persiste la ligne de commande en base de données
                    $this->entityManager->persist($ligneCommande);
                }
            }

            // Mise à jour des montants totaux dans le panier et l'historique d'achats
            $panierEntity->setMontantTotal($montantTotal);
            $historiqueAchat->setMontantTotal($montantTotal);

            // Enregistre les changements en base de données
            $this->entityManager->flush();

            // Vider le panier pour cet utilisateur
            unset($panier[$userId]);
            // Met à jour la session
            $session->set('panier', $panier);
        }

        // Rendu de la vue de succès
        return $this->render('pages/stripe/success.html.twig');
    }

    // Route pour gérer les erreurs de paiement
    #[Route('/stripe_error', name: 'stripe_error')]
    public function error(): Response
    {
        // Rendu de la vue d'erreur
        return $this->render('pages/stripe/error.html.twig');
    }
}
