<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\CommentaireType;
use App\Repository\ArticleRepository;
use App\Repository\CommentaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class CommentairesController extends AbstractController
{
    #[Route('/commentaires/{id}', name: 'commentaires.article')]
    public function list(int $id, ArticleRepository $articleRepository, CommentaireRepository $commentaireRepository, Request $request, EntityManagerInterface $manager, TranslatorInterface $translator): Response
    {
        $locale = $request->getLocale();

        /** @var Utilisateur $user */
        $user = $this->getUser();

        $article = $articleRepository->findOneBy(['id' => $id]);

        if (!$article) {
            $this->addFlash('danger', $translator->trans('article_not_found', [], 'messages', $locale));
            return $this->redirectToRoute('blog.list');
        }

        $form = $this->createForm(CommentaireType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $commentaire = $form->getData();
            $commentaire->setUtilisateur($user)->setArticle($article);
            
            $manager->persist($commentaire);
            $manager->flush();

            $this->addFlash('success', $translator->trans('comment_added', [], 'messages', $locale));
            $form = $this->createForm(CommentaireType::class);
            $this->redirectToRoute('commentaires.article', ['id' => $id]);
        }

        $commentaires = $commentaireRepository->findBy(['article' => $article], ['date_commentaire' => 'DESC']);

        return $this->render('pages/commentaires/index.html.twig', [
            'commentaires' => $commentaires,
            'form' => $form->createView(),
        ]);
    }
}
