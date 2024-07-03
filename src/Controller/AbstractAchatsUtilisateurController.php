<?php

namespace App\Controller;

use App\Entity\HistoriqueAchat;
use App\Entity\Utilisateur;
use App\Repository\HistoriqueAchatRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

abstract class AbstractAchatsUtilisateurController extends AbstractController
{
    protected function voirAchatsUtilisateur(Utilisateur $utilisateur, HistoriqueAchatRepository $historiqueAchatRepository)
    {
        $achats_utilisateur = $historiqueAchatRepository->recupererHistoriqueAchats($utilisateur);

        return $this->render('pages/achats_utilisateur/list.html.twig', [
            'achats_utilisateur' => $achats_utilisateur,
        ]);
    }

    protected function voirDetailsAchat(int $id, HistoriqueAchatRepository $historiqueAchatRepository, string $redirectPath): Response
    {
        $historiqueAchat = $historiqueAchatRepository->find($id);

        if (!$historiqueAchat) {
            $this->addFlash('danger', `L'historique d'achat avec l'id $id n'existe pas dans la base de données !`);
            return $this->redirectToRoute($redirectPath);
        }
        
        $details_achat = $historiqueAchatRepository->recupererDetailsAchat($id);

        return $this->render('pages/achats_utilisateur/details.html.twig', [
            'details_achat' => $details_achat,
            'imagesArticlesPath' => $this->getParameter('images_articles_path'),
        ]);
    }

    protected function telechargerFacture(HistoriqueAchat $historiqueAchat, $details_achat, string $redirectPath): Response
    {
        // Configuration de Dompdf selon nos besoins
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

        $response->send();

        return $this->redirectToRoute($redirectPath);
    }
}
