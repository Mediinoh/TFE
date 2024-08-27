<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\SuppressionArticlePanierType;
use App\Repository\ArticleRepository;
use App\Repository\HistoriqueAchatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class PanierController extends AbstractController
{
    #[Route('/panier', name: 'panier.list', methods: ['GET', 'POST'])]
    public function list(SessionInterface $session, ArticleRepository $articleRepository): Response
    {
        $panier = $this->getPanier($session, $articleRepository);

        return $this->render('pages/panier/index.html.twig', [
            'articlesPanier' => $panier['articlesPanier'],
            'total' => $panier['total'],
            'quantiteTotale' => $panier['quantiteTotale'],
            'imagesArticlesPath' => $this->getParameter('images_articles_path'),
            'stripe_public' => $this->getParameter('stripe_public'),
        ]);
    }

    private function getPanier(SessionInterface $session, ArticleRepository $articleRepository): array
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('security.login');
        }

        $userId = $user->getId();
        $panier = $session->get('panier', []);
        $articlesPanier = [];
        $total = 0;
        $quantiteTotale = 0;

        if (isset($panier[$userId])) {
            foreach ($panier[$userId] as $articleId => $quantite) {
                $article = $articleRepository->find($articleId);
                if ($article) {
                    $prixTotalArticle = $article->getPrixUnitaire() * $quantite;
                    $total += $prixTotalArticle;
                    $quantiteTotale += $quantite;

                    $suppressionArticlePanierForm = $this->createForm(SuppressionArticlePanierType::class, null, [
                        'action' => $this->generateUrl('panier.supprimer', ['id' => $articleId]),
                        'method' => 'POST',
                        'quantiteMax' => $quantite,
                    ]);

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

    #[Route('/panier/validation', name: 'panier.validation', methods: ['GET', 'POST'])]
    public function validationPanier(SessionInterface $session, ArticleRepository $articleRepository, Request $request): Response
    {
        $panierData = $this->getPanier($session, $articleRepository);
        $user = $panierData['user'];

        if ($request->isMethod('POST')) {
            return $this->redirectToRoute('stripe_checkout');
        }

        return $this->render('pages/panier/validation.html.twig', [
            'articlesPanier' => $panierData['articlesPanier'],
            'total' => $panierData['total'],
            'quantiteTotale' => $panierData['quantiteTotale'],
            'imagesArticlesPath' => $this->getParameter('images_articles_path'),
        ]);
    }

    #[Route('/panier/confirmation/{id}', name: 'panier.confirmation', methods: ['GET'])]
    public function confirmationPanier(int $id, HistoriqueAchatRepository $historiqueAchatRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('security.login');
        }

        $historiqueAchat = $historiqueAchatRepository->find($id);
        if (!$historiqueAchat) {
            throw $this->createNotFoundException('Achat non trouvé.');
        }

        return $this->render('pages/panier/confirmation.html.twig', [
            'historiqueAchat' => $historiqueAchat,
        ]);
    }

    #[Route('/panier/supprimer/{id}', name: 'panier.supprimer', methods: ['POST'])]
    public function suppressionPanier(int $id, ArticleRepository $articleRepository, SessionInterface $session, Request $request, TranslatorInterface $translator): Response
    {
        $locale = $request->getLocale();

        /** @var Utilisateur $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('security.login');
        }

        $article = $articleRepository->find($id);
        
        if (!$article) {
            $this->addFlash('danger', $translator->trans('article_not_found', ['%id%' => $id], 'messages', $locale));
            return $this->redirectToRoute('panier.list');
        }

        $userId = $user->getId();
        $panier = $session->get('panier', []);
        
        if (isset($panier[$userId][$id])) {
            $quantite = intval($request->get('suppression_article_panier')['quantite']);
            $panier[$userId][$id] -= $quantite;

            if ($panier[$userId][$id] <= 0) {
                unset($panier[$userId][$id]);
            }
            
            $session->set('panier', $panier);

            $this->addFlash('success', $translator->trans('article_removed_quantity_from_cart', ['%quantity%' => $quantite], 'messages', $locale));
        }

        return $this->redirectToRoute('panier.list');
    }
}
