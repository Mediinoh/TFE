<?php

namespace App\Controller;

use App\Entity\HistoriqueAchat;
use App\Entity\Utilisateur;
use App\Repository\HistoriqueAchatRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractAchatsUtilisateurController extends AbstractController
{
    protected function voirAchatsUtilisateur(Utilisateur $utilisateur, HistoriqueAchatRepository $historiqueAchatRepository, string $redirectPath)
    {
        $achats_utilisateur = $historiqueAchatRepository->recupererHistoriqueAchats($utilisateur);

        return $this->render('pages/achats_utilisateur/list.html.twig', [
            'achats_utilisateur' => $achats_utilisateur,
            'redirectPath' => $redirectPath,
        ]);
    }

    protected function voirDetailsAchat(int $id, HistoriqueAchatRepository $historiqueAchatRepository, string $redirectPath, TranslatorInterface $translator, RequestStack $requestStack): Response
    {
        $locale = $requestStack->getCurrentRequest()->getLocale();

        $historiqueAchat = $historiqueAchatRepository->find($id);

        if (!$historiqueAchat) {
            $this->addFlash('danger', $translator->trans('purchase_history_not_found', ['%id%' => $id], 'messages', $locale));
            return $this->redirectToRoute($redirectPath);
        }
        
        $details_achat = $historiqueAchatRepository->recupererDetailsAchat($id);

        return $this->render('pages/achats_utilisateur/details.html.twig', [
            'details_achat' => $details_achat,
            'imagesArticlesPath' => $this->getParameter('images_articles_path'),
        ]);
    }

    #[Route('/telecharger_facture/{id}', 'telecharger_facture')]
    public function telechargerFactureAction(int $id, HistoriqueAchatRepository $historiqueAchatRepository, TranslatorInterface $translator, RequestStack $requestStack) {
        $locale = $requestStack->getCurrentRequest()->getLocale();

        $historiqueAchat = $historiqueAchatRepository->find($id);
        
        if (!$historiqueAchat) {
            $this->addFlash('danger', $translator->trans('purchase_history_not_found', ['%id%' => $id], 'messages', $locale));
            $redirectPath = $this->isGranted('ROLE_ADMIN') ? 'admin_achats_utilisateur' : 'list_achats_utilisateur';
            return $this->redirectToRoute($redirectPath);
        }

        return $this->telechargerFacture($historiqueAchat, $historiqueAchatRepository);
    }

    protected function telechargerFacture(HistoriqueAchat $historiqueAchat, HistoriqueAchatRepository $historiqueAchatRepository): Response
    {
        // Récupérer les détails de l'achat
        $details_achat = $historiqueAchatRepository->recupererDetailsAchat($historiqueAchat->getId());

        // Générer le PDF
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($pdfOptions);

        // Génération du contenu HTML du PDF
        $html = $this->renderView('pages/achats_utilisateur/facture.html.twig', [
            'historiqueAchat' => $historiqueAchat,
            'details_achat' => $details_achat,
            'imagesArticlesPath' => $this->getParameter('images_articles_path'),
        ]);

        // Chargement du HTML dans Dompdf
        $dompdf->loadHtml($html);

        // Configuration du format du papier et l'orientation
        $dompdf->setPaper('A4', 'portrait');

        // Rendu du PDF
        $dompdf->render();

        // Création du fichier de la facture
        $filename = date_format($historiqueAchat->getDateAchat(), 'Y_m_d_H_i') . '_' . $historiqueAchat->getId() . '_facture.pdf';

        // Envoi du PDF au navigateur
        $response = new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);

        return $response;
    }
}
