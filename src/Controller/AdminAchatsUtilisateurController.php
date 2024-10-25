<?php

namespace App\Controller;

// Importation des classes nécessaires
use App\Entity\Utilisateur;
use App\Repository\HistoriqueAchatRepository;
use App\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class AdminAchatsUtilisateurController extends AbstractAchatsUtilisateurController
{
    // Route pour afficher les achats d'un utilisateur
    #[Route('/admin/achats_utilisateur/{id}', 'admin_achats_utilisateur', methods: ['GET'])]
    public function list_achats(int $id, UtilisateurRepository $utilisateurRepository, HistoriqueAchatRepository $historiqueAchatRepository, TranslatorInterface $translator, RequestStack $requestStack): Response
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

        // Récupération de l'utilisateur ciblé par ID
        $utilisateur = $utilisateurRepository->find($id);

        // Affiche un message d'erreur si l'utilisateur n'existe pas
        if (!$utilisateur) {
            $this->addFlash('danger', $translator->trans('user_not_found', ['%id' => $id], 'messages', $locale));
            return $this->redirectToRoute('admin_utilisateurs');
        }

        // Appel de la méthode pour afficher les achats d'un utilisateur
        return $this->voirAchatsUtilisateur($utilisateur, $historiqueAchatRepository, 'admin_details_achat_utilisateur');
    }

    // Route pour afficher les détails d'un achat spécifique
    #[Route('/admin/details_achat/{id}', 'admin_details_achat_utilisateur', methods: ['GET'])]
    public function details_achat(int $id, Request $request, HistoriqueAchatRepository $historiqueAchatRepository, TranslatorInterface $translator, RequestStack $requestStack): Response
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

        // Récupération de la route actuelle pour une utilisation ultérieure
        $routePath = $request->attributes->get('_route');

        // Appel de la méthode pour afficher les détails d'un achat spécifique
        return $this->voirDetailsAchat($id, $historiqueAchatRepository, $routePath, $translator, $requestStack);
    }
}
