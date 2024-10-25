<?php

namespace App\Controller;

// Importation des classes nécessaires
use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends AbstractController
{
    // Route pour accéder à la liste des articles de blog
    #[Route('/blog', name: 'blog.list')]
    public function index(ArticleRepository $articleRepository): Response
    {
        // Récupération des articles depuis la base de données, triés par catégorie (ordre croissant)
        $articles = $articleRepository->findBy([], ['categorie' => 'ASC']);

        // Rendu de la vue avec les articles et le chemin d'accès aux images des articles
        return $this->render('pages/blog/index.html.twig', [
            'articles' => $articles,
            'imagesArticlesPath' => $this->getParameter('images_articles_path'),
        ]);
    }
}
