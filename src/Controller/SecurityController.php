<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Event\UserLoggedInEvent;
use App\Form\ForgotPasswordType;
use App\Form\InscriptionType;
use App\Repository\UserRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityController extends AbstractController
{
    #[Route('/connexion', 'security.login', methods: ['GET', 'POST'])]
    public function login(Request $request, AuthenticationUtils $authenticationUtils, EventDispatcherInterface $dispatcher): Response
    {
        $user = $this->getUser();

        if ($user) {
            return $this->redirectToRoute('home.index');
        }

        $lastUsername = $authenticationUtils->getLastUsername();
        $error = $authenticationUtils->getLastAuthenticationError();

        $user = $this->getUser();
        $event = new UserLoggedInEvent($user);
        $dispatcher->dispatch($event, UserLoggedInEvent::NAME);

        if ($user) {
            return $this->redirectToRoute('home.index');
        }

        return $this->render('pages/security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/deconnexion', 'security.logout', methods: ['GET'])]
    public function logout() {
        // Nothing to do here...
    }

    #[Route('/inscription', 'security.inscription', methods: ['GET', 'POST'])]
    public function inscription(Request $request, EntityManagerInterface $manager, TranslatorInterface $translator): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('home.index');
        }

        $locale = $request->getLocale();

        $utilisateur = new Utilisateur();
        $utilisateur->setRoles(['ROLE_USER']);

        $form = $this->createForm(InscriptionType::class, $utilisateur);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $utilisateur = $form->getData();
            $photoProfil = $form->get('photo_profil')->getData();
            if (!is_null($photoProfil) && $photoProfil instanceof UploadedFile) {
               $nomFichier = uniqid() . '_' . date('Ymd_His') . '.png';
               $photoProfil->move($this->getParameter('images_photos_profil_directory'), $nomFichier);
               $utilisateur->setPhotoProfil($nomFichier);
            }
            $manager->persist($utilisateur);
            $manager->flush();

            $this->addFlash('success', $translator->trans('account_created_successfully', [], 'messages', $locale));

            return $this->redirectToRoute('security.login');
        }

        return $this->render('pages/security/inscription.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/forgot_password', 'security.forgot_password', methods: ['GET', 'POST'])]
    public function forgotPassword(Request $request, EntityManagerInterface $manager, UtilisateurRepository $utilisateurRepository, TranslatorInterface $translator): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('home.index');
        }

        $locale = $request->getLocale();

        $form = $this->createForm(ForgotPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $utilisateurData = $form->getData();
            $utilisateur = $utilisateurRepository->findOneBy(['email' => $utilisateurData['email']]);

            if (!$utilisateur) {
                $this->addFlash('danger', $translator->trans('account_not_found_with_email', [], 'messages', $locale));
                return $this->redirectToRoute('app_forgot_password');
            }

            $utilisateur->setPlainPassword($utilisateurData['plainPassword']);

            $manager->persist($utilisateur);
            $manager->flush();

            $this->addFlash('success', $translator->trans('password_changed_successfully', [], 'messages', $locale));

            return $this->redirectToRoute('security.login');
        }

        return $this->render('pages/security/forgot_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
