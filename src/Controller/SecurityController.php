<?php

namespace App\Controller;

// Importation des classes nécessaires
use App\Entity\Utilisateur;
use App\Event\UserLoggedInEvent;
use App\Form\ForgotPasswordType;
use App\Form\InscriptionType;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

// Contrôleur pour gérer la sécurité et l'authentification des utilisateurs
class SecurityController extends AbstractController
{
    // Route pour la connexion
    #[Route('/connexion', 'security.login', methods: ['GET', 'POST'])]
    public function login(Request $request, AuthenticationUtils $authenticationUtils, EventDispatcherInterface $dispatcher): Response
    {
        $user = $this->getUser(); // Récupère l'utilisateur connecté

        // Si l'utilisateur est déjà connecté, redirige vers la page d'accueil
        if ($user) {
            return $this->redirectToRoute('home.index');
        }

        // Récupère le dernier nom d'utilisateur et l'erreur d'authentification
        $lastUsername = $authenticationUtils->getLastUsername();
        $error = $authenticationUtils->getLastAuthenticationError();

        // Crée un événement pour signaler la connexion d'un utilisateur
        $event = new UserLoggedInEvent($user);
        $dispatcher->dispatch($event, UserLoggedInEvent::NAME);

        // Rendu de la vue de connexion avec les données nécessaires
        return $this->render('pages/security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    // Route pour la déconnexion
    #[Route('/deconnexion', 'security.logout', methods: ['GET'])]
    public function logout() {
        // Rien à faire ici, la déconnexion est gérée par le middleware
    }

    // Route pour l'inscription
    #[Route('/inscription', 'security.inscription', methods: ['GET', 'POST'])]
    public function inscription(Request $request, EntityManagerInterface $manager, TranslatorInterface $translator): Response
    {
        // Si l'utilisateur est déjà connecté, redirige vers la page d'accueil
        if ($this->getUser()) {
            return $this->redirectToRoute('home.index');
        }

        // Récupère la locale de la requête
        $locale = $request->getLocale();

        // Crée une nouvelle instance de l'entité Utilisateur
        $utilisateur = new Utilisateur();
        $utilisateur->setRoles(['ROLE_USER']); // Définit le rôle par défaut

        // Crée le formulaire d'inscription
        $form = $this->createForm(InscriptionType::class, $utilisateur);
        // Gère la requête pour le formulaire
        $form->handleRequest($request);

        // Vérifie si le formulaire a été soumis et est valide
        if ($form->isSubmitted() && $form->isValid()) {
            $utilisateur = $form->getData(); // Récupère les données du formulaire
            $photoProfil = $form->get('photo_profil')->getData(); // Récupère le fichier de photo de profil

            // Vérifie si une photo a été téléchargée
            if (!is_null($photoProfil) && $photoProfil instanceof UploadedFile) {
                // Gén!re un nom unique pour le fichier de photo
                $nomFichier = uniqid() . '_' . date('Ymd_His') . '.png';
                // Déplace le fichier dans le répertoire défini
               $photoProfil->move($this->getParameter('images_photos_profil_directory'), $nomFichier);
               // Définit le nom de la photo de profil
               $utilisateur->setPhotoProfil($nomFichier);
            }

            // Persiste l'utilisateur en base de données et enregistre les changements
            $manager->persist($utilisateur);
            $manager->flush();

            // Ajoute un message flash de succès
            $this->addFlash('success', $translator->trans('account_created_successfully', [], 'messages', $locale));

            // Redirige vers la page de connexion
            return $this->redirectToRoute('security.login');
        }

        // Rendu de la vue d'inscription avec le formulaire
        return $this->render('pages/security/inscription.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Route pour le mot de passe oublié
    #[Route('/forgot_password', 'security.forgot_password', methods: ['GET', 'POST'])]
    public function forgotPassword(Request $request, EntityManagerInterface $manager, UtilisateurRepository $utilisateurRepository, TranslatorInterface $translator, UserPasswordHasherInterface $hasher): Response
    {
        // Si l'utilisateur est déjà connecté, redirige vers la page d'accueil
        if ($this->getUser()) {
            return $this->redirectToRoute('home.index');
        }

        // Récupère la locale de la requête
        $locale = $request->getLocale();

        // Crée le formulaire pour le mot de passe oublié
        $form = $this->createForm(ForgotPasswordType::class);
        // Gère la requête pour le formulaire
        $form->handleRequest($request);

        // Vérifie si le formulaire a été soumis et est valide
        if ($form->isSubmitted() && $form->isValid()) {
            $utilisateurData = $form->getData(); // Récupère les données du formulaire
            $utilisateur = $utilisateurRepository->findOneBy(['email' => $utilisateurData['email']]); // Recherche l'utilisateur par e-mail

            // Vérifie si l'utilisateur existe
            if (!$utilisateur) {
                // Ajoute un message flash d'erreur si l'utilisateur n'est pas trouvé
                $this->addFlash('danger', $translator->trans('account_not_found_with_email', [], 'messages', $locale));
                // Redirige vers la page de réinitialisation
                return $this->redirectToRoute('security.forgot_password');
            }

            // Hache le nouveau mot de passe et le définit pour l'utilisateur
            $utilisateur->setPassword(
                $hasher->hashPassword($utilisateur, $utilisateurData['plainPassword'])
            );

            // Persiste et enregistre les modifications en base de données
            $manager->persist($utilisateur);
            $manager->flush();

            // Ajoute un message flash de succès
            $this->addFlash('success', $translator->trans('password_changed_successfully', [], 'messages', $locale));

            // Redirige vers la page de connexion
            return $this->redirectToRoute('security.login');
        }

        // Rendu de la vue pour le mot de passe oublié avec le formulaire
        return $this->render('pages/security/forgot_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
