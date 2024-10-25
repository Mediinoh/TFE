<?php

namespace App\Controller;

// Importation des classes nécessaires
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
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProduitsController extends AbstractController
{

    private string $imagesArticlesPath;

    // Constructeur pour initialiser le chemin d'accès aux images des articles
    public function __construct(ParameterBagInterface $params)
    {
        $this->imagesArticlesPath = $params->get('images_articles_path');
    }

    // Route pour afficher la liste des produits
    #[Route('/produits', name: 'produits.list', methods: ['GET'])]
    public function list(ArticleRepository $articleRepository, Request $request): Response
    {
        // Récupération des articles non supprimés
        $articles = $articleRepository->findBy(['supprime' => false]);

        // Création du formulaire de filtrafe
        $searchForm = $this->createForm(FiltreArticleParType::class);
        // Gestion de la requête du formulaire
        $searchForm->handleRequest($request);

        // Vérifie si le formulaire a été soumis et est valide
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $data = $searchForm->getData(); // Récupération des données du formulaire
            $articles = $articleRepository->filtrerArticlesPar($data['categorie'], $data['mot_cle']); // Filtrage des articles
        }

        // Initialisation des formulaires pour l'ajout au panier et les favoris
        $formulairesAjoutPanier = [];
        $formulairesFavoris = [];
        /** @var Utilisateur $user */
       $user = $this->getUser(); // Récupération de l'utilisateur connecté
       
       if ($user) {
            foreach($articles as $article) {
                // Création du formulaire pour ajouter un article au panier
                $ajoutPanierForm = $this->createForm(AjoutPanierType::class, null, [
                    'action' => $this->generateUrl('ajout_panier', ['id' => $article->getId()]), // URL de l'action
                    'method' => 'POST',
                ]);
                $ajoutPanierForm->handleRequest($request); // Gestion de la requête pour le formulaire
                $formulairesAjoutPanier['article_' . $article->getId()] = $ajoutPanierForm->createView(); // Récupération de la vue du formulaire

                // Vérification si l'article est déjà dans les favoris
                $isFavorite = $user->getFavoris()->contains($article);
                // Création du formulaire pour gérer les favories
                $favorisForm = $this->createForm(FavorisType::class, null, [
                    'action' => $this->generateUrl($isFavorite ? 'suppression_favoris' : 'ajout_favoris', ['id' => $article->getId()]),
                    'method' => 'POST',
                    'isFavorite' => $isFavorite,
                ]);
                $favorisForm->handleRequest($request); // Gestion de la requête du formulaire
                $formulairesFavoris['article_' . $article->getId()] = $favorisForm->createView(); // Récupération de la vue du formulaire
            }
       }

       // Rendu de la vue avec les articles et formulaires associés
        return $this->render('pages/produits/list.html.twig', [
            'searchForm' => $searchForm->createView(),
            'articles' => $articles,
            'formulairesAjoutPanier' => $formulairesAjoutPanier,
            'formulairesFavoris' => $formulairesFavoris,
            'imagesArticlesPath' => $this->imagesArticlesPath,
        ]);
    }

    // Route pour afficher les détails d'un produit
    #[Route('/produits/details/{id}', 'produits_details', methods: ['GET', 'POST'])]
    public function produitDetails(int $id, ArticleRepository $articleRepository, Request $request): Response
    {
        /** @var Utilisateur $user */
       $user = $this->getUser(); // Récupération de l'utilisateur connecté
       $formulaireAjoutPanier = null; // Initialisation du formulaire d'ajout au panier

       $article = $articleRepository->find($id); // Récupération de l'article par ID

       // Redirection si l'article n'existe pas
       if (!$article) {
        return $this->redirectToRoute('produits.list');
       }

       // Si l'utilisateur est connecté
        if ($user) {
            // Création du formulaire d'ajout au panier
            $formulaireAjoutPanier = $this->createForm(AjoutPanierType::class, null, [
                'action' => $this->generateUrl('ajout_panier', ['id' => $article->getId()]),
                'method' => 'POST',
            ]);
            $formulaireAjoutPanier->handleRequest($request); // Gestion de la requête du formulaire
        }

        // Rendu de la vue avec les détails de l'article
        return $this->render('pages/produits/details.html.twig', [
            'article' => $article,
            'formulaireAjoutPanier' => is_null($formulaireAjoutPanier) ? null : $formulaireAjoutPanier->createView(),
            'imagesArticlesPath' => $this->imagesArticlesPath,
        ]);
    }

    // Route pour ajouter un article au panier
    #[Route('/ajout-panier/{id}', 'ajout_panier', methods: ['POST'])]
    public function ajoutPanier(int $id, ArticleRepository $articleRepository, Request $request, SessionInterface $session, TranslatorInterface $translator): Response
    {
        $locale = $request->getLocale(); // Récupération de la locale de la requête

       /** @var Utilisateur $user */
       $user = $this->getUser(); // Récupération de l'utilisateur connecté
       
       // Si l'utilisateur n'est pas connecté, redirige vers la page de connexion
       if (!$user) {
        return $this->redirectToRoute('security.login');
       }

       $article = $articleRepository->find($id); // Récupération de l'article par ID

       // Redirection si l'article n'existe pas
       if (!$article) {
            $this->addFlash('danger', $translator->trans('article_not_dound', ['%id%' => $id], 'messages', $locale));
            return $this->redirectToRoute('produits.list');
        }

        // Initialisation du panier dans la session
        if (!$session->has('panier')) {
            $session->set('panier', []);
        }

        $panier = $session->get('panier'); // Récupération du panier
        $userId = $user->getId(); // Récupération de l'ID de l'utilisateur

        // Initialisation du panier de l'utilisateur s'il n'existe pas encore
        if (!isset($panier[$userId])) {
            $panier[$userId] = [];
        }

        // Récupération des données du formulaire
        $formData = $request->request->all('ajout_panier', []);
        // Récupération de la quantité
        $quantite = intval($formData['quantite'] ?? 1);

        // Ajout de l'article au panier
        if (isset($panier[$userId][$article->getId()])) {
            $panier[$userId][$article->getId()] += $quantite; // Augmenter la quantité si déjà présent
        } else {
            $panier[$userId][$article->getId()] = $quantite; // Ajouter l'article
        }

        $session->set('panier', $panier); // Mise à jour du panier dans la session

        // Message de succès
        $this->addFlash('success', $translator->trans('article_added_to_cart', [], 'messages', $locale));

        // Redirection vers la liste des produits
        return $this->redirectToRoute('produits.list');
    }

    // Route pour ajouter un article aux favoris
    #[Route('/ajout-favoris/{id}', 'ajout_favoris', methods: ['POST'])]
    public function ajoutFavoris(int $id, ArticleRepository $articleRepository, EntityManagerInterface $manager, TranslatorInterface $translator, Request $request): Response
    {
        $locale = $request->getLocale(); // Récupération de la locale de la requête

       /** @var Utilisateur $user */
       $user = $this->getUser(); // Récupération de l'utilisateur connecté
       
       // Si l'utilisateur n'est pas connecté, redirection vers la page de connexion
       if (!$user) {
        return $this->redirectToRoute('security.login');
       }

       $article = $articleRepository->find($id); // Récupération de l'article par ID

       // Redirection vers la liste des produits si l'article n'existe pas
       if (!$article) {
            $this->addFlash('danger', $translator->trans('article_not_found', ['%id%' => $id], 'messages', $locale));
            return $this->redirectToRoute('produits.list');
        }

        // Ajout de l'article aux favoris de l'utilisateur
        $user->addFavori($article);
        $manager->flush();

        // Message de succès
        $this->addFlash('success', $translator->trans('article_added_to_favorites'));

        // Redirection vers la liste des articles
        return $this->redirectToRoute('produits.list');
    }

    // Route pour supprimer un article des favoris
    #[Route('/suppression-favoris/{id}', 'suppression_favoris', methods: ['POST'])]
    public function suppressionFavoris(int $id, ArticleRepository $articleRepository, EntityManagerInterface $manager, Request $request, TranslatorInterface $translator): Response
    {
        $locale = $request->getLocale(); // Récupération de la locale de la requête

       /** @var Utilisateur $user */
       $user = $this->getUser(); // Récupération de l'utilisateur connecté
       
       // Si l'utilisateur n'est pas connecté, redirection vers la page de connexion
       if (!$user) {
        return $this->redirectToRoute('security.login');
       }

       $article = $articleRepository->find($id); // Récupération de l'article par ID

       // Redirection si l'article n'existe pas
       if (!$article) {
            $this->addFlash('danger', $translator->trans('article_not_found', ['%id%' => $id], 'messages', $locale));
            return $this->redirectToRoute('produits.list');
        }

        // Supprime l'article aux favoris de l'utilisateur
        $user->removeFavori($article);
        $manager->flush();

        // Message de succès
        $this->addFlash('success', $translator->trans('article_removed_from_favorites', [], 'messages', $locale));

        // Redirection vers la liste d'articles
        return $this->redirectToRoute('produits.list');
    }

    // Route pour afficher les meilleures ventes
    #[Route('/produits/meilleures_ventes', name: 'produits.meilleures_ventes')]
    public function meilleuresVentes(ArticleRepository $articleRepository): Response
    {
        $articles = $articleRepository->recupererMeilleuresVentes();
        return $this->render('pages/produits/meilleures_ventes.html.twig', [
            'articles' => $articles,
            'imagesArticlesPath' => $this->imagesArticlesPath,
        ]);
    }

    // Route pour afficher les nouveautés
    #[Route('/produits/nouveautes', name: 'produits.nouveautes')]
    public function nouveautes(ArticleRepository $articleRepository): Response
    {
        $articles = $articleRepository->recupererNouveautes();
        return $this->render('pages/produits/nouveautes.html.twig', [
            'articles' => $articles,
            'imagesArticlesPath' => $this->imagesArticlesPath,
        ]);
    }

    // Route pour afficher les articles favoris de l'utilisateur
    #[Route('/produits/favoris', name: 'produits.favoris')]
    public function favoris(Request $request): Response
    {
        /** @var Utilisateur $user */
        $user = $this->getUser(); // Récupération de l'utilisateur connecté
        
        // Si l'utilisateur n'est pas connecté, redirection vers la page de connexion
        if (!$user) {
            return $this->redirectToRoute('security.login');
        }

        // Initialisation des formulaires de favoris et récupération des articles favoris
        $formulairesFavoris = [];
        $articles = array_reverse($user->getFavoris()->toArray());
        
        foreach($articles as $article) {
            // Création du formulaire pour gérer les favoris
            $favorisForm = $this->createForm(FavorisType::class, null, [
                'action' => $this->generateUrl('suppression_favoris', ['id' => $article->getId()]),
                'method' => 'POST',
                'isFavorite' => true,
            ]);
            $favorisForm->handleRequest($request); // Gestion de la requête du formulaire
            $formulairesFavoris['article_' . $article->getId()] = $favorisForm->createView(); // Récupération de la vue du formulaire
        }

        // Rendu de la vue avec les articles favoris
        return $this->render('pages/produits/favoris.html.twig', [
            'articles' => $articles,
            'formulairesFavoris' => $formulairesFavoris,
            'imagesArticlesPath' => $this->imagesArticlesPath,
        ]);
    }
}
