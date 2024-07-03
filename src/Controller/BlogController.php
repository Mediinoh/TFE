<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends AbstractController
{
    #[Route('/blog', name: 'blog.list')]
    public function index(ArticleRepository $articleRepository): Response
    {

        $articles = $articleRepository->findBy([], ['categorie' => 'ASC']);

        return $this->render('pages/blog/index.html.twig', [
            'articles' => $articles,
            'imagesArticlesPath' => $this->getParameter('images_articles_path'),
        ]);
    }
}
