<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\ProfilType;
use App\Repository\HistoriqueAchatRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UtilisateurController extends AbstractAchatsUtilisateurController
{
    #[Route('/utilisateur/profil/{id}', name: 'utilisateur.profil', methods: ['GET', 'POST'])]
    public function profil(int $id, UtilisateurRepository $utilisateurRepository, Request $request, EntityManagerInterface $manager): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('security.login');
        }

        $utilisateur = $utilisateurRepository->find($id);

        if (is_null($utilisateur) || $this->getUser() !== $utilisateur) {
            return $this->redirectToRoute('home.index');
        }

        $form = $this->createForm(ProfilType::class, $utilisateur);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            if (!is_null($formData->getPlainPassword())) {
                $utilisateur->setPlainPassword($formData->getPlainPassword());
            }
            $photoProfil = $form->get('photo_profil')->getData();
            if (!is_null($photoProfil) && $photoProfil instanceof UploadedFile) {
               $nomFichier = uniqid() . '_' . date('Ymd_His') . '.png';
               $photoProfil->move($this->getParameter('images_photos_profil_directory'), $nomFichier);
               $utilisateur->setPhotoProfil($nomFichier);
            }
            $manager->persist($utilisateur);
            $manager->flush();

            $this->addFlash('success', 'Les informations de votre compte ont bien été modifiées !');

            return $this->redirectToRoute('utilisateur.profil', ['id' => $utilisateur->getId()]);
        }

        return $this->render('pages/utilisateur/profil.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/utilisateur/achats_utilisateur', 'list_achats_utilisateur', methods: ['GET'])]
    public function list_achats(Request $request, HistoriqueAchatRepository $historiqueAchatRepository): Response
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();

        if (!$user) {
            $this->redirectToRoute('security.login');
        }

        return $this->voirAchatsUtilisateur($user, $historiqueAchatRepository);
    }

    #[Route('/utilisateur/details_achat/{id}', 'details_achat_utilisateur', methods: ['GET'])]
    public function details_achat(int $id, Request $request, HistoriqueAchatRepository $historiqueAchatRepository): Response
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();

        if (!$user) {
            $this->redirectToRoute('security.login');
        }

        $routePath = $request->attributes->get('_route');

        return $this->voirDetailsAchat($id, $historiqueAchatRepository, $routePath);
    }
}
