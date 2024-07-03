<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Repository\CommentaireRepository;
use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminCommentairesUtilisateurController extends AbstractController
{
    #[Route('/admin/commentaires_utilisateur/{id}', name: 'admin_commentaires_utilisateur')]
    public function index(int $id, UtilisateurRepository $utilisateurRepository, CommentaireRepository $commentaireRepository): Response
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();

        if (!$user) {
            $this->redirectToRoute('security.login');
        }

        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->redirectToRoute('home.index');
        }

        $utilisateur = $utilisateurRepository->find($id);

        if (!$utilisateur) {
            $this->addFlash('danger', `L'utilisateur avec l'id $id n'existe pas dans la base de données !`);
            return $this->redirectToRoute('admin_utilisateurs');
        }

        $commentaires_utilisateur = $commentaireRepository->recupererCommentairesUtilisateur($utilisateur);

        return $this->render('pages/admin/commentaires_utilisateur.html.twig', [
            'commentaires_utilisateur' => $commentaires_utilisateur,
            'imagesArticlesPath' => $this->getParameter('images_articles_path'),
        ]);
    }
}
