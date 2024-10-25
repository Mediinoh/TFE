<?php

namespace App\Controller;

// Importation des classes nécessaires
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
    // Route pour afficher et ajouter des commentaires sur un article
    #[Route('/commentaires/{id}', name: 'commentaires.article')]
    public function list(int $id, ArticleRepository $articleRepository, CommentaireRepository $commentaireRepository, Request $request, EntityManagerInterface $manager, TranslatorInterface $translator): Response
    {
        $locale = $request->getLocale(); // Récupère la langue actuelle pour la traduction

        /** @var Utilisateur $user */
        $user = $this->getUser(); // Obtient l'utilisateur connecté

        $article = $articleRepository->findOneBy(['id' => $id]); // Récupère l'article correspondant à l'ID passé en paramètre

        // Si l'article n'existe pas, un message est affiché et l'utilisateur est redirigé
        if (!$article) {
            $this->addFlash('danger', $translator->trans('article_not_found', [], 'messages', $locale));
            return $this->redirectToRoute('blog.list');
        }

        // Crée le formulaire de commentaire
        $form = $this->createForm(CommentaireType::class);
        $form->handleRequest($request);

        // Vérifie si le formulaire a été soumis et est valide
        if ($form->isSubmitted() && $form->isValid()) {
            $commentaire = $form->getData(); // Récupère les données du commentaire
            $commentaire->setUtilisateur($user)->setArticle($article); // Associe l'utilisateur et l'article au commentaire
            
            // Enregistre le commentaire
            $manager->persist($commentaire);
            $manager->flush();

            // Message de succès et réinitialisation du formulaire
            $this->addFlash('success', $translator->trans('comment_added', [], 'messages', $locale));
             // Crée un nouveau formulaire pour effacer les champs du précédent
            $form = $this->createForm(CommentaireType::class);
            // Redirection pour éviter le double envoi du formulaire
            return $this->redirectToRoute('commentaires.article', ['id' => $id]);
        }

        // Récupère tous les commentaires de l'article, triés par date décroissante
        $commentaires = $commentaireRepository->findBy(['article' => $article], ['date_commentaire' => 'DESC']);

        // Affiche la page des commentaires avec le formulaire et la liste des commentaires
        return $this->render('pages/commentaires/index.html.twig', [
            'commentaires' => $commentaires,
            'form' => $form->createView(),
        ]);
    }
}
