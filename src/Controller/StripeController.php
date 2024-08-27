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

    #[Route('/create-checkout-session', name: 'stripe_checkout', methods: ['POST'])]
    public function checkout(Request $request, SessionInterface $session, ArticleRepository $articleRepository): Response
    {
        /** @var Utilisateur $utilisateur */
        $utilisateur = $this->getUser();
        $userId = $utilisateur->getId();
        $panier = $session->get('panier', []);

        $lineItems = [];
        $panierTotal = 0;

        if (isset($panier[$userId])) {
            $panierUtilisateur = $panier[$userId];
            
            foreach ($panierUtilisateur as $articleId => $quantite) {
                $article = $articleRepository->find($articleId);

                if ($article) {
                    $imageUrl = $this->assets->getUrl('images/articles/' . $article->getPhotoArticle());
                    $absoluteImageUrl = $this->getParameter('app_base_url') . $imageUrl;

                    $lineItems[] = [
                        'price_data' => [
                            'currency' => 'eur',
                            'product_data' => [
                                'name' => $article->getTitre(),
                                'images' => [],
                            ],
                            'unit_amount' => $article->getPrixUnitaire() * 100,
                        ],
                        'quantity' => $quantite,
                    ];
                    $panierTotal += $article->getPrixUnitaire() * $quantite;
                }
            }
        }

        Stripe::setApiKey($this->getParameter('stripe_secret'));

        $checkoutSession = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => $this->generateUrl('stripe_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->generateUrl('stripe_error', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        return $this->json(['id' => $checkoutSession->id]);
    }

    #[Route('/stripe_success', name: 'stripe_success')]
    public function success(SessionInterface $session, ArticleRepository $articleRepository): Response
    {
        /** @var Utilisateur $utilisateur */
        $utilisateur = $this->getUser();
        $userId = $utilisateur->getId();
        $panier = $session->get('panier', []);

        if (isset($panier[$userId])) {
            $panierUtilisateur = $panier[$userId];

            $panierEntity = new Panier();
            $panierEntity->setUtilisateur($utilisateur);
            $panierEntity->setMontantTotal(0);

            $this->entityManager->persist($panierEntity);
            $this->entityManager->flush();

            // Créer un nouvel enregistrement HistoriqueAchat
            $historiqueAchat = new HistoriqueAchat();
            $historiqueAchat->setUtilisateur($utilisateur);
            $historiqueAchat->setMontantTotal(0);  // Sera mis à jour plus tard
            $historiqueAchat->setPanier($panierEntity);

            $this->entityManager->persist($historiqueAchat);
            $this->entityManager->flush();

            $montantTotal = 0;

            foreach ($panierUtilisateur as $articleId => $quantite) {
                $article = $articleRepository->find($articleId);
                if ($article) {
                    $prixTotalArticle = $article->getPrixUnitaire() * $quantite;
                    $montantTotal += $prixTotalArticle;

                    // Créer une nouvelle LigneCommande
                    $ligneCommande = new LigneCommande();
                    $ligneCommande->setArticle($article);
                    $ligneCommande->setQuantite($quantite);
                    $ligneCommande->setPrix($article->getPrixUnitaire());
                    $ligneCommande->setPanier($panierEntity);

                    $this->entityManager->persist($ligneCommande);
                }
            }

            // Mise à jour des montants totaux
            $panierEntity->setMontantTotal($montantTotal);
            $historiqueAchat->setMontantTotal($montantTotal);

            $this->entityManager->flush();

            // Vider le panier pour cet utilisateur
            unset($panier[$userId]);
            $session->set('panier', $panier);
        }

        return $this->render('pages/stripe/success.html.twig');
    }

    #[Route('/stripe_error', name: 'stripe_error')]
    public function error(): Response
    {
        return $this->render('pages/stripe/error.html.twig');
    }
}
