<?php

namespace App\Controller;

// Importation des classes nécessaires
use App\Entity\Utilisateur;
use App\Repository\CommentaireRepository;
use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class AdminCommentairesUtilisateurController extends AbstractController
{
    #[Route('/admin/commentaires_utilisateur/{id}', name: 'admin_commentaires_utilisateur')]
    public function index(int $id, UtilisateurRepository $utilisateurRepository, CommentaireRepository $commentaireRepository, TranslatorInterface $translator, RequestStack $requestStack): Response
    {
        // Récupération de la langue actuelle depuis la requête
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

        // Récupération de l'utilisateur pour lequel on veut voir les commentaires
        $utilisateur = $utilisateurRepository->find($id);

        // Vérification si l'utilisateur n'existe pas
        if (!$utilisateur) {
            $this->addFlash('danger', $translator->trans('user_not_found', ['%id' => $id], 'messages', $locale));
            return $this->redirectToRoute('admin_utilisateurs');
        }

        // Récupération des commentaires de l'utilisateur
        $commentaires_utilisateur = $commentaireRepository->recupererCommentairesUtilisateur($utilisateur);

        // Rendu de la vue avec les données des commentaires de l'utilisateur
        return $this->render('pages/admin/commentaires_utilisateur.html.twig', [
            'commentaires_utilisateur' => $commentaires_utilisateur,
            'imagesArticlesPath' => $this->getParameter('images_articles_path'),
        ]);
    }
}
