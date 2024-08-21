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
    #[Route('/utilisateur/profil/{id}', name: 'utilisateur.profil', methods: ['GET', 'POST'])]
    public function profil(int $id, UtilisateurRepository $utilisateurRepository, Request $request, EntityManagerInterface $manager, TranslatorInterface $translator): Response
    {
        $locale = $request->getLocale();

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

            $this->addFlash('success', $translator->trans('account_information_updated', [], 'messages', $locale));

            return $this->redirectToRoute('utilisateur.profil', ['id' => $utilisateur->getId()]);
        }

        return $this->render('pages/utilisateur/profil.html.twig', [
            'form' => $form->createView(),
            'userId' => $id,
        ]);
    }

    #[Route('/utilisateur/profil/delete/{id}', name: 'utilisateur.profil.delete', methods: ['GET'])]
    public function removeProfil(int $id, UtilisateurRepository $utilisateurRepository, Request $request, EntityManagerInterface $manager, TranslatorInterface $translator): Response
    {
        $locale = $request->getLocale();

        /** @var Utilisateur $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('security.login');
        }

        if ($this->isGranted('ROLE_ADMIN')) {
            $this->redirectToRoute('utilisateur.profil');
        }

        $utilisateur = $utilisateurRepository->find($id);

        if (is_null($utilisateur) || $user !== $utilisateur) {
            return $this->redirectToRoute('home.index');
        }

        $manager->remove($utilisateur);
        $manager->flush();

        $this->addFlash('success', $translator->trans('account_deleted',[], 'messages', $locale));

        return $this->redirectToRoute('security.logout');
    }

    #[Route('/utilisateur/achats_utilisateur', 'list_achats_utilisateur', methods: ['GET'])]
    public function list_achats(HistoriqueAchatRepository $historiqueAchatRepository): Response
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();

        if (!$user) {
            $this->redirectToRoute('security.login');
        }

        return $this->voirAchatsUtilisateur($user, $historiqueAchatRepository, 'details_achat_utilisateur');
    }

    #[Route('/utilisateur/details_achat/{id}', 'details_achat_utilisateur', methods: ['GET'])]
    public function details_achat(int $id, Request $request, HistoriqueAchatRepository $historiqueAchatRepository, TranslatorInterface $translator, RequestStack $requestStack): Response
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();

        if (!$user) {
            $this->redirectToRoute('security.login');
        }

        $routePath = $request->attributes->get('_route');

        return $this->voirDetailsAchat($id, $historiqueAchatRepository, $routePath, $translator, $requestStack);
    }
}
