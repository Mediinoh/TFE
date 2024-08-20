<?php

namespace App\Controller;

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
    #[Route('/admin/achats_utilisateur/{id}', 'admin_achats_utilisateur', methods: ['GET'])]
    public function list_achats(int $id, UtilisateurRepository $utilisateurRepository, HistoriqueAchatRepository $historiqueAchatRepository, TranslatorInterface $translator, RequestStack $requestStack): Response
    {
        $locale = $requestStack->getCurrentRequest()->getLocale();

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
            $this->addFlash('danger', $translator->trans('user_not_found', ['%id' => $id], 'messages', $locale));
            return $this->redirectToRoute('admin_utilisateurs');
        }

        return $this->voirAchatsUtilisateur($utilisateur, $historiqueAchatRepository, 'admin_details_achat_utilisateur');
    }

    #[Route('/admin/details_achat/{id}', 'admin_details_achat_utilisateur', methods: ['GET'])]
    public function details_achat(int $id, Request $request, HistoriqueAchatRepository $historiqueAchatRepository, TranslatorInterface $translator, RequestStack $requestStack): Response
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();

        if (!$user) {
            $this->redirectToRoute('security.login');
        }

        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->redirectToRoute('home.index');
        }

        $routePath = $request->attributes->get('_route');

        return $this->voirDetailsAchat($id, $historiqueAchatRepository, $routePath, $translator, $requestStack);
    }
}
