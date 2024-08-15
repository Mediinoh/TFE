<?php

namespace App\Controller;

use App\Entity\HistoriqueAchat;
use App\Entity\LigneCommande;
use App\Entity\Panier;
use App\Entity\Utilisateur;
use App\Form\PayerType;
use App\Form\SuppressionArticlePanierType;
use App\Repository\ArticleRepository;
use App\Repository\HistoriqueAchatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class PanierController extends AbstractAchatsUtilisateurController
{
    #[Route('/panier', name: 'panier.list', methods: ['GET', 'POST'])]
    public function list(SessionInterface $session, ArticleRepository $articleRepository, Request $request, EntityManagerInterface $manager, HistoriqueAchatRepository $historiqueAchatRepository): Response
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();

        if (!$user) {
            $this->redirectToRoute('security.login');
        }

        $userId = $user->getId();
        $panier = $session->get('panier', []);
        $articlesPanier = [];

        $total = 0;
        $quantiteTotale = 0;

        if (isset($panier[$userId])) {
            foreach($panier[$userId] as $articleId => $quantite) {
                $article = $articleRepository->find($articleId);
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

        $formPayer = $this->createForm(PayerType::class);
        $formPayer->handleRequest($request);

        if ($formPayer->isSubmitted() && $formPayer->isValid()) {
            $panier = new Panier();
            $panier->setUtilisateur($user)->setMontantTotal($total);
            $manager->persist($panier);
            
            foreach($articlesPanier as $articlePanier) {
                $ligneCommande = new LigneCommande();
                $ligneCommande->setArticle($articlePanier['article'])
                                ->setPanier($panier)
                                ->setQuantite($articlePanier['quantite'])
                                ->setPrix($articlePanier['prix_total']);
                $manager->persist($ligneCommande);
            }

            $historiqueAchat = new HistoriqueAchat();
            $historiqueAchat->setUtilisateur($user)->setMontantTotal($total)->setPanier($panier);
            $manager->persist($historiqueAchat);
            
            $manager->flush();

            $session->set('panier', [
                $userId => [],
            ]);

            $factureResponse = $this->telechargerFacture($historiqueAchat, $historiqueAchatRepository);

            return $factureResponse;
        }

        return $this->render('pages/panier/index.html.twig', [
            'articlesPanier' => $articlesPanier,
            'total' => $total,
            'quantiteTotale' => $quantiteTotale,
            'formPayer' => $formPayer->createView(),
            'imagesArticlesPath' => $this->getParameter('images_articles_path'),
        ]);
    }

    #[Route('/panier/supprimer/{id}', 'panier.supprimer', methods: ['POST'])]
    public function suppressionPanier(int $id, ArticleRepository $articleRepository, SessionInterface $session, Request $request): Response
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();

        if (!$user) {
            $this->redirectToRoute('security.login');
        }

        $article = $articleRepository->find($id);

       if (!$article) {
            throw new NotFoundHttpException('L\'article n\'existe pas.');
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

            $this->addFlash('success', "Article supprimé du panier en $quantite exemplaires !");
        }

        return $this->redirectToRoute('panier.list');
    }
}
