<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\ProfilType;
use App\Repository\HistoriqueAchatRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class UtilisateurController extends AbstractAchatsUtilisateurController
{
    // Méthode pour afficher et modifier le profil de l'utilisateur
    #[Route('/utilisateur/profil/{id}', name: 'utilisateur.profil', methods: ['GET', 'POST'])]
    public function profil(int $id, UtilisateurRepository $utilisateurRepository, Request $request, EntityManagerInterface $manager, TranslatorInterface $translator): Response
    {
        // Récupération de la locale de la requête pour les traductions
        $locale = $request->getLocale();

        // Redirection vers la page de connexion si l'utilisateur n'est pas authentifié
        if (!$this->getUser()) {
            return $this->redirectToRoute('security.login');
        }

        // Récupère l'utilisateur avec l'identifiant
        $utilisateur = $utilisateurRepository->find($id);

        // Redirige si l'utilisateur est introuvable ou s'il ne correspond pas à l'utilisateur authentifié
        if (is_null($utilisateur) || $this->getUser() !== $utilisateur) {
            return $this->redirectToRoute('home.index');
        }

        // Création et gestion du formulaire de modification du profil
        $form = $this->createForm(ProfilType::class, $utilisateur);
        $form->handleRequest($request);

        // Vérifie si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            if (!is_null($formData->getPlainPassword())) {
                // Mise à jour du mot de passe s'il est modifié par l'utilisateur
                $utilisateur->setPlainPassword($formData->getPlainPassword());
            }

            // Gestion de l'upload de la photo de profil si elle a été ajoutée par l'utilisateur
            $photoProfil = $form->get('photo_profil')->getData();
            if (!is_null($photoProfil) && $photoProfil instanceof UploadedFile) {
               $nomFichier = uniqid() . '_' . date('Ymd_His') . '.png';
               $photoProfil->move($this->getParameter('images_photos_profil_directory'), $nomFichier);
               $utilisateur->setPhotoProfil($nomFichier);
            }

            // Persistance des modifications
            $manager->persist($utilisateur);
            $manager->flush();

            // Message de confirmation de la mise à jour du profil
            $this->addFlash('success', $translator->trans('account_information_updated', [], 'messages', $locale));

            // Redirection vers la même page pour afficher les modifications
            return $this->redirectToRoute('utilisateur.profil', ['id' => $utilisateur->getId()]);
        }

        // Rend la vue pour afficher le profil avec le formulaire de modification
        return $this->render('pages/utilisateur/profil.html.twig', [
            'form' => $form->createView(),
            'userId' => $id,
        ]);
    }

    // Méthode pour supprimer le profil de l'utilisateur
    #[Route('/utilisateur/profil/delete/{id}', name: 'utilisateur.profil.delete', methods: ['GET'])]
    public function removeProfil(int $id, UtilisateurRepository $utilisateurRepository, Request $request, EntityManagerInterface $manager, TranslatorInterface $translator): Response
    {
        // Récupère la locale pour traduire les messages
        $locale = $request->getLocale();

        /** @var Utilisateur $user */
        $user = $this->getUser();

        // Vérifie que l'utilisateur est bien authentifié
        if (!$user) {
            return $this->redirectToRoute('security.login');
        }

        // Redirection pour éviter que les administrateurs puissent supprimer leur propre profil
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('utilisateur.profil');
        }

        // Récupère l'utilisateur
        $utilisateur = $utilisateurRepository->find($id);

        // Redirige si l'utilisateur à supprimer n'existe pas ou n'est pas le même que l'utilisateur connecté
        if (is_null($utilisateur) || $user !== $utilisateur) {
            return $this->redirectToRoute('home.index');
        }

        // Supprime l'utilisateur de la base de données
        $manager->remove($utilisateur);
        $manager->flush();

        // Ajoute un message flash pour indiquer que le compte a été supprimé
        $this->addFlash('success', $translator->trans('account_deleted',[], 'messages', $locale));

        // Redirection vers la déconnexion après la suppression du compte
        return $this->redirectToRoute('security.logout');
    }

    // Méthode pour afficher la liste des achats de l'utilisateur connecté
    #[Route('/utilisateur/achats_utilisateur', 'list_achats_utilisateur', methods: ['GET'])]
    public function list_achats(HistoriqueAchatRepository $historiqueAchatRepository): Response
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();

        // Redirection vers la page de connexion si l'utilisateur n'est pas connecté
        if (!$user) {
            return $this->redirectToRoute('security.login');
        }

        // Appel d'une méthode héritée pour afficher la liste des achats de l'utilisateur
        return $this->voirAchatsUtilisateur($user, $historiqueAchatRepository, 'details_achat_utilisateur');
    }

    // Méthode pour afficher les achats d'un achat spécifique
    #[Route('/utilisateur/details_achat/{id}', 'details_achat_utilisateur', methods: ['GET'])]
    public function details_achat(int $id, Request $request, HistoriqueAchatRepository $historiqueAchatRepository, TranslatorInterface $translator, RequestStack $requestStack): Response
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();

        // Redirection vers la page de connexion si l'utilisateur n'est pas connecté
        if (!$user) {
            return $this->redirectToRoute('security.login');
        }

        // Récupère le chemin de la route actuelle pour la redirection après visualisation
        $routePath = $request->attributes->get('_route');

        // Appel de la méthode pour afficher les détails d'un achat spécifique
        return $this->voirDetailsAchat($id, $historiqueAchatRepository, $routePath, $translator, $requestStack);
    }
}
