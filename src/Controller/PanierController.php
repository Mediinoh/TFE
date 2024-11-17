<?php

namespace App\Controller;

// Importation des classes nécessaires
use App\Entity\Utilisateur;
use App\Form\PayerType;
use App\Form\SuppressionArticlePanierType;
use App\Repository\ArticleRepository;
use App\Repository\HistoriqueAchatRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class PanierController extends AbstractController
{
    // Frais de livraison fixes pour chaque commande
    public const FRAIS_LIVRAISON = 2.99;

    public function __construct()
    {
        
    }

    // Route pour afficher la liste des articles dans le panier
    #[Route('/panier', name: 'panier.list', methods: ['GET', 'POST'])]
    public function list(SessionInterface $session, ArticleRepository $articleRepository): Response
    {
        // Récupération des informations du panier pour l'utilisateur actuel
        $panier = $this->getPanier($session, $articleRepository);

        // Calcul du total avec les frais de livraison
        $totalAvecFrais = $panier['total'] + self::FRAIS_LIVRAISON;

        $form = $this->createForm(PayerType::class);

        // Rendu de la vue avec les articles du panier
        return $this->render('pages/panier/index.html.twig', [
            'articlesPanier' => $panier['articlesPanier'],
            'total' => $panier['total'],
            'quantiteTotale' => $panier['quantiteTotale'],
            'totalAvecFrais' => $totalAvecFrais,
            'fraisLivraison' => self::FRAIS_LIVRAISON,
            'stripe_public' => $this->getParameter('stripe_public'),
            'form' => $form->createView(),
            'imagesArticlesPath' => $this->getParameter('images_articles_path'),
        ]);
    }

    // Méthode privée pour obtenir les détails du panier
    private function getPanier(SessionInterface $session, ArticleRepository $articleRepository): array
    {
        /** @var Utilisateur $user */
        $user = $this->getUser(); // Récupération de l'utilisateur connecté
        // Redirection vers la page de connexion si l'utilisateur n'est pas connecté
        if (!$user) {
            return $this->redirectToRoute('security.login');
        }

        $userId = $user->getId(); // ID utilisateur
        $panier = $session->get('panier', []); // Récupération du panier de la session
        $articlesPanier = [];
        $total = 0;
        $quantiteTotale = 0;

        // Vérification si le panier de l'utilisateur existe
        if (isset($panier[$userId])) {
            // Parcours des articles du panier
            foreach ($panier[$userId] as $articleId => $quantite) {
                // Récupère l'article par ID
                $article = $articleRepository->find($articleId);

                if ($article) {
                    // Calcul du prix total pour l'article
                    $prixTotalArticle = $article->getPrixUnitaire() * $quantite; // Mise à jour du total
                    $total += $prixTotalArticle;
                    $quantiteTotale += $quantite; // Mise à jour de la quantité totale

                    // Création du formulaire de suppression pour chaque article
                    $suppressionArticlePanierForm = $this->createForm(SuppressionArticlePanierType::class, null, [
                        'action' => $this->generateUrl('panier.supprimer', ['id' => $articleId]),
                        'method' => 'POST',
                        'quantiteMax' => $quantite,
                    ]);

                    // Ajout de l'article et de ses détails dans le tableau articlesPanier
                    $articlesPanier[] = [
                        'article' => $article,
                        'quantite' => $quantite,
                        'prix_total' => $prixTotalArticle,
                        'suppressionArticlePanierForm' => $suppressionArticlePanierForm->createView(),
                    ];
                }
            }
        }

        return [
            'articlesPanier' => $articlesPanier,
            'total' => $total,
            'quantiteTotale' => $quantiteTotale,
            'user' => $user,
        ];
    }

    // Route pour la validation du panier
    #[Route('/panier/validation', name: 'panier.validation', methods: ['GET', 'POST'])]
    public function validationPanier(SessionInterface $session, ArticleRepository $articleRepository, Request $request): Response
    {
        // Récupération des informations du panier
        $panierData = $this->getPanier($session, $articleRepository);
        $user = $panierData['user'];

        // Si requête en POST, redirection vers le checkout Stripe
        if ($request->isMethod('POST')) {
            return $this->redirectToRoute('stripe_checkout');
        }

        // Calcul du total avec les frais de livraison
        $totalAvecFrais = $panierData['total'] + self::FRAIS_LIVRAISON;

        // Rendu de la vue de validation du panier
        return $this->render('pages/panier/validation.html.twig', [
            'articlesPanier' => $panierData['articlesPanier'],
            'total' => $panierData['total'],
            'quantiteTotale' => $panierData['quantiteTotale'],
            'totalAvecFrais' => $totalAvecFrais,
            'fraisLivraison' => self::FRAIS_LIVRAISON,
            'imagesArticlesPath' => $this->getParameter('images_articles_path'),
        ]);
    }

    // Route pour la confirmation d'achat
    #[Route('/panier/confirmation/{id}', name: 'panier.confirmation', methods: ['GET'])]
    public function confirmationPanier(int $id, HistoriqueAchatRepository $historiqueAchatRepository): Response
    {
        // Récupération de l'utilisateur connecté
        $user = $this->getUser();
        // Redirection vers la page de connexion si l'utilisateur n'est pas connecté
        if (!$user) {
            return $this->redirectToRoute('security.login');
        }

        // Recherche de l'historique d'achat par ID
        $historiqueAchat = $historiqueAchatRepository->find($id);
        
        if (!$historiqueAchat) {
            throw $this->createNotFoundException('Achat non trouvé.');
        }

        // Rendu de la vue de confirmation d'achat
        return $this->render('pages/panier/confirmation.html.twig', [
            'historiqueAchat' => $historiqueAchat,
        ]);
    }

    // Route pour supprimer une quantité spécifique d'un article du panier
    #[Route('/panier/supprimer/{id}', name: 'panier.supprimer', methods: ['POST'])]
    public function suppressionPanier(int $id, ArticleRepository $articleRepository, SessionInterface $session, Request $request, TranslatorInterface $translator): Response
    {
        $locale = $request->getLocale(); // Langue de la requête

        /** @var Utilisateur $user */
        $user = $this->getUser(); // Récupération de l'utilisateur connecté
        // Redirection vers la page de connexion si l'utilisateur n'est pas connecté
        if (!$user) {
            return $this->redirectToRoute('security.login');
        }

        // Récupération de l'article par ID
        $article = $articleRepository->find($id);
        // Redirection vers le panier si l'article n'existe pas
        if (!$article) {
            $this->addFlash('danger', $translator->trans('article_not_found', ['%id%' => $id], 'messages', $locale));
            return $this->redirectToRoute('panier.list');
        }

        $userId = $user->getId();
        $panier = $session->get('panier', []); // Récupération du panier
        
        if (isset($panier[$userId][$id])) {
            // Récupération de la quantité à supprimer
            $quantite = intval($request->get('suppression_article_panier')['quantite']);
            $panier[$userId][$id] -= $quantite; // Mise à jour de la quantité

            // Suppression de l'article si la quantité atteint zéro
            if ($panier[$userId][$id] <= 0) {
                unset($panier[$userId][$id]);
            }
            
            $session->set('panier', $panier); // Mise à jour du panier

            // Message de succès pour la suppression
            $this->addFlash('success', $translator->trans('article_removed_quantity_from_cart', ['%quantity%' => $quantite], 'messages', $locale));
        }
        
        return $this->redirectToRoute('panier.list');
    }
}
