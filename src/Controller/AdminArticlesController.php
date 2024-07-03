<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\AjoutArticleType;
use App\Form\SupprimeArticleType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class AdminArticlesController extends AbstractController
{
    #[Route('/admin/articles', 'admin_articles', methods: ['GET', 'POST'])]
    public function index(ArticleRepository $articleRepository, Request $request, EntityManagerInterface $manager): Response
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();

        if (!$user) {
            $this->redirectToRoute('security.login');
        }

        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->redirectToRoute('home.index');
        }

        $form = $this->createForm(AjoutArticleType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article = $form->getData();

            $photoArticle = $form->get('photo_article')->getData();
            if (!is_null($photoArticle) && $photoArticle instanceof UploadedFile) {
               $nomFichier = uniqid() . '_' . date('Ymd_His') . '.png';
               $photoArticle->move($this->getParameter('images_articles_directory'), $nomFichier);
               $article->setPhotoArticle($nomFichier);
            }

            $manager->persist($article);
            $manager->flush();

            return $this->redirectToRoute('admin_articles');
        }

        $articles = $articleRepository->findBy([], ['id' => 'ASC']);

        $formulairesSupprimerArticle = [];
        foreach($articles as $article) {
            $supprimerArticleForm = $this->createForm(SupprimeArticleType::class, null, [
                'action' => $this->generateUrl('admin_supprimer_article', ['id' => $article->getId()]),
                'method' => 'POST',
                'supprime' => !$article->isSupprime(),
            ]);
            $supprimerArticleForm->handleRequest($request);
            $formulairesSupprimerArticle[] = $supprimerArticleForm->createView();
        }

        return $this->render('pages/admin/articles.html.twig', [
            'articles' => $articles,
            'form' => $form->createView(),
            'formulairesSupprimerArticle' => $formulairesSupprimerArticle,
            'imagesArticlesPath' => $this->getParameter('images_articles_path'),
        ]);
    }

    #[Route('/supprimer_article/{id}', 'admin_supprimer_article', methods: ['POST'])]
    public function supprimerArticle(int $id, ArticleRepository $articleRepository, Request $request, EntityManagerInterface $manager): Response
    {
       /** @var Utilisateur $user */
       $user = $this->getUser();
       
       if (!$user) {
        return $this->redirectToRoute('security.login');
       }

       if (!$this->isGranted('ROLE_ADMIN')) {
        $this->redirectToRoute('home.index');
    }

       $article = $articleRepository->find($id);

       if (!$article) {
            throw new NotFoundHttpException('L\'article n\'existe pas.');
       }

        $article->setSupprime(!$article->isSupprime());

        $manager->persist($article);
        $manager->flush();

        $this->addFlash('success', 'Cet article a été supprimé de votre panier !');

        return $this->redirectToRoute('admin_articles');
    }
}
