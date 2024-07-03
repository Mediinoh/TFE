<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\AjoutPanierType;
use App\Form\FavorisType;
use App\Form\FiltreArticleParType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class ProduitsController extends AbstractController
{

    private $imagesArticlesPath;

    public function __construct(ParameterBagInterface $params)
    {
        $this->imagesArticlesPath = $params->get('images_articles_path');
    }

    #[Route('/produits', name: 'produits.list', methods: ['GET'])]
    public function list(ArticleRepository $articleRepository, Request $request): Response
    {
        
        $articles = $articleRepository->findBy(['supprime' => false]);

        $searchForm = $this->createForm(FiltreArticleParType::class);

        $searchForm->handleRequest($request);

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $data = $searchForm->getData();
            $articles = $articleRepository->filtrerArticlesPar($data['categorie'], $data['mot_cle']);
        }

        $formulairesAjoutPanier = [];
        $formulairesFavoris = [];
        /** @var Utilisateur $user */
       $user = $this->getUser();
       
       if ($user) {
            foreach($articles as $article) {
                $ajoutPanierForm = $this->createForm(AjoutPanierType::class, null, [
                    'action' => $this->generateUrl('ajout_panier', ['id' => $article->getId()]),
                    'method' => 'POST',
                ]);
                $ajoutPanierForm->handleRequest($request);
                $formulairesAjoutPanier['article_' . $article->getId()] = $ajoutPanierForm->createView();

                $isFavorite = $user->getFavoris()->contains($article);
                $favorisForm = $this->createForm(FavorisType::class, null, [
                    'action' => $this->generateUrl($isFavorite ? 'suppression_favoris' : 'ajout_favoris', ['id' => $article->getId()]),
                    'method' => 'POST',
                    'isFavorite' => $isFavorite,
                ]);
                $favorisForm->handleRequest($request);
                $formulairesFavoris['article_' . $article->getId()] = $favorisForm->createView();
            }
       }

        return $this->render('pages/produits/list.html.twig', [
            'searchForm' => $searchForm->createView(),
            'articles' => $articles,
            'formulairesAjoutPanier' => $formulairesAjoutPanier,
            'formulairesFavoris' => $formulairesFavoris,
            'imagesArticlesPath' => $this->imagesArticlesPath,
        ]);
    }

    #[Route('/ajout-panier/{id}', 'ajout_panier', methods: ['POST'])]
    public function ajoutPanier(int $id, ArticleRepository $articleRepository, Request $request, SessionInterface $session): Response
    {
       /** @var Utilisateur $user */
       $user = $this->getUser();
       
       if (!$user) {
        return $this->redirectToRoute('security.login');
       }

       $article = $articleRepository->find($id);

       if (!$article) {
            throw new NotFoundHttpException('L\'article n\'existe pas.');
        }

        if (!$session->has('panier')) {
            $session->set('panier', []);
        }

        $panier = $session->get('panier');
        $userId = $user->getId();

        if (!isset($panier[$userId])) {
            $panier[$userId] = [];
        }

        $formData = $request->request->all('ajout_panier', []);

        $quantite = intval($formData['quantite'] ?? 1);

        if (isset($panier[$userId][$article->getId()])) {
            $panier[$userId][$article->getId()] += $quantite;
        } else {
            $panier[$userId][$article->getId()] = $quantite;
        }

        $session->set('panier', $panier);

        $this->addFlash('success', 'Cet article a été ajouté dans votre panier !');

        return $this->redirectToRoute('produits.list');
    }

    #[Route('/ajout-favoris/{id}', 'ajout_favoris', methods: ['POST'])]
    public function ajoutFavoris(int $id, ArticleRepository $articleRepository, EntityManagerInterface $manager): Response
    {
       /** @var Utilisateur $user */
       $user = $this->getUser();
       
       if (!$user) {
        return $this->redirectToRoute('security.login');
       }

       $article = $articleRepository->find($id);

       if (!$article) {
            throw new NotFoundHttpException('L\'article n\'existe pas.');
        }

        $user->addFavori($article);
        $manager->flush();

        $this->addFlash('success', 'Cet article a été ajouté à vos favoris !');

        return $this->redirectToRoute('produits.list');
    }

    #[Route('/suppression-favoris/{id}', 'suppression_favoris', methods: ['POST'])]
    public function suppressionFavoris(int $id, ArticleRepository $articleRepository, EntityManagerInterface $manager): Response
    {
       /** @var Utilisateur $user */
       $user = $this->getUser();
       
       if (!$user) {
        return $this->redirectToRoute('security.login');
       }

       $article = $articleRepository->find($id);

       if (!$article) {
            throw new NotFoundHttpException('L\'article n\'existe pas.');
        }

        $user->removeFavori($article);
        $manager->flush();

        $this->addFlash('success', 'Cet article a été supprimé de vos favoris !');

        return $this->redirectToRoute('produits.list');
    }

    #[Route('/produits/meilleures_ventes', name: 'produits.meilleures_ventes')]
    public function meilleuresVentes(ArticleRepository $articleRepository): Response
    {
        $articles = $articleRepository->recupererMeilleuresVentes();
        return $this->render('pages/produits/meilleures_ventes.html.twig', [
            'articles' => $articles,
            'imagesArticlesPath' => $this->imagesArticlesPath,
        ]);
    }

    #[Route('/produits/nouveautes', name: 'produits.nouveautes')]
    public function nouveautes(ArticleRepository $articleRepository): Response
    {
        $articles = $articleRepository->recupererNouveautes();
        return $this->render('pages/produits/nouveautes.html.twig', [
            'articles' => $articles,
            'imagesArticlesPath' => $this->imagesArticlesPath,
        ]);
    }

    #[Route('/produits/favoris', name: 'produits.favoris')]
    public function favoris(): Response
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('security.login');
        }

        $articles = array_reverse($user->getFavoris()->toArray());
        return $this->render('pages/produits/favoris.html.twig', [
            'articles' => $articles,
            'imagesArticlesPath' => $this->imagesArticlesPath,
        ]);
    }
}
