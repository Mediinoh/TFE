<?php

namespace App\Controller;

// Importation des classes nécessaires
use App\Entity\Utilisateur;
use App\Form\AjoutArticleType;
use App\Form\AjoutStockType;
use App\Form\SupprimeArticleType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class AdminArticlesController extends AbstractController
{
    #[Route('/admin/articles', 'admin_articles', methods: ['GET', 'POST'])]
    public function index(ArticleRepository $articleRepository, Request $request, EntityManagerInterface $manager): Response
    {
        // Récupération de l'utilisateur actuellement connecté
        /** @var Utilisateur $user */
        $user = $this->getUser();

        // Redirection vers la page de connexion si l'utilisateur n'est pas authentifié
        if (!$user) {
            return $this->redirectToRoute('security.login');
        }

        // Redirection vers la page d'accueil si l'utilisateur n'a pas le rôle ADMIN
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('home.index');
        }

        // Création et gestion du formulaire pour ajouter un nouvel article
        $form = $this->createForm(AjoutArticleType::class);
        $form->handleRequest($request);

        // Vérifie si le formulaire a été soumis et est valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupération des données du formulaire
            $article = $form->getData();

            // Gestion de l'upload de l'image associée à l'article
            $photoArticle = $form->get('photo_article')->getData();
            
            if (!is_null($photoArticle) && $photoArticle instanceof UploadedFile) {
               $nomFichier = uniqid() . '_' . date('Ymd_His') . '.png';
               $photoArticle->move($this->getParameter('images_articles_directory'), $nomFichier);
               $article->setPhotoArticle($nomFichier);
            }

            // Enregistrement de l'article de la base de données
            $manager->persist($article);
            $manager->flush();

            // Redirection vers la liste des articles après l'ajout
            return $this->redirectToRoute('admin_articles');
        }

        // Récupération de tous les articles pour affichage
        $articles = $articleRepository->findBy([], ['id' => 'ASC']);

        // Création des formulaires de suppression pour chaque article
        $formulairesSupprimerArticle = [];
        // Création des formulaires de gestion de stock pour chaque article
        $formulairesStockArticle = [];
        
        foreach ($articles as $article) {
            $supprimerArticleForm = $this->createForm(SupprimeArticleType::class, null, [
                'action' => $this->generateUrl('admin_supprimer_article', ['id' => $article->getId()]),
                'method' => 'POST',
                'supprime' => !$article->isSupprime(),
            ]);
            $supprimerArticleForm->handleRequest($request);
            $formulairesSupprimerArticle[$article->getId()] = $supprimerArticleForm->createView();

            $ajoutStockForm = $this->createForm(AjoutStockType::class, null, [
                'action' => $this->generateUrl('admin_ajout_stock', ['id' => $article->getId()]),
                'method' => 'POST',
            ]);
            $ajoutStockForm->handleRequest($request);
            $formulairesStockArticle[$article->getId()] = $ajoutStockForm->createView();
        }

        // Rendu de la vue avec la liste des articles et les formulaires
        return $this->render('pages/admin/articles.html.twig', [
            'articles' => $articles,
            'form' => $form->createView(),
            'formulairesSupprimerArticle' => $formulairesSupprimerArticle,
            'formulairesStockArticle' => $formulairesStockArticle,
            'imagesArticlesPath' => $this->getParameter('images_articles_path'),
        ]);
    }

    #[Route('/ajout_stock/{id}', 'admin_ajout_stock', methods: ['POST'])]
    public function ajoutStock(int $id, ArticleRepository $articleRepository, EntityManagerInterface $manager, TranslatorInterface $translator, RequestStack $requestStack, Request $request): Response
    {
        // Récupération de la locale de la requête
        $locale = $requestStack->getCurrentRequest()->getLocale();

        // Récupération de l'utilisateur actuellement connecté
        /** @var Utilisateur $user */
        $user = $this->getUser();
       
        // Redirection vers la page de connexion si l'utilisateur n'est pas authentifié
        if (!$user) {
            return $this->redirectToRoute('security.login');
        }

        // Redirection vers la page d'accueil si l'utilisateur n'a pas le rôle ADMIN
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('home.index');
        }

        // Récupération de l'article à supprimer
        $article = $articleRepository->find($id);

        // Vérification si l'article n'existe pas
        if (!$article) {
            $this->addFlash('danger', $translator->trans('article_not_found', ['%id%' => $id], 'messages', $locale));
            return $this->redirectToRoute('admin_articles');    
        }

        $article->setStock($article->getStock() + $request->get('ajout_stock')['quantite']);

        // Enregistrement des modifications dans la base de données
        $manager->persist($article);
        $manager->flush();

        // Message de confirmation
        $this->addFlash('success', $translator->trans('article_removed_from_cart', [], 'messages', $locale));

        // Redirection vers la liste des articles après la suppression
        return $this->redirectToRoute('admin_articles');
    }

    #[Route('/supprimer_article/{id}', 'admin_supprimer_article', methods: ['POST'])]
    public function supprimerArticle(int $id, ArticleRepository $articleRepository, EntityManagerInterface $manager, TranslatorInterface $translator, RequestStack $requestStack): Response
    {
        // Récupération de la locale de la requête
        $locale = $requestStack->getCurrentRequest()->getLocale();

        // Récupération de l'utilisateur actuellement connecté
        /** @var Utilisateur $user */
        $user = $this->getUser();
       
        // Redirection vers la page de connexion si l'utilisateur n'est pas authentifié
        if (!$user) {
            return $this->redirectToRoute('security.login');
        }

        // Redirection vers la page d'accueil si l'utilisateur n'a pas le rôle ADMIN
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('home.index');
        }

        // Récupération de l'article à supprimer
        $article = $articleRepository->find($id);

        // Vérification si l'article n'existe pas
        if (!$article) {
            $this->addFlash('danger', $translator->trans('article_not_found', ['%id%' => $id], 'messages', $locale));
            return $this->redirectToRoute('admin_articles');    
        }

        // Marque l'article comme supprimé ou le réactive
        $article->setSupprime(!$article->isSupprime());

        // Enregistrement des modifications dans la base de données
        $manager->persist($article);
        $manager->flush();

        // Message de confirmation
        $this->addFlash('success', $translator->trans('article_removed_from_cart', [], 'messages', $locale));

        // Redirection vers la liste des articles après la suppression
        return $this->redirectToRoute('admin_articles');
    }
}
