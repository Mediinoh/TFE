<?php

namespace App\Controller;

// Importation des classes nécessaires
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
    // Méthode pour afficher la liste des achats d'un utilisateur
    protected function voirAchatsUtilisateur(Utilisateur $utilisateur, HistoriqueAchatRepository $historiqueAchatRepository, string $redirectPath)
    {
        // Récupération de l'historique des achats de l'utilisateur
        $achats_utilisateur = $historiqueAchatRepository->recupererHistoriqueAchats($utilisateur);

        // Rendu de la vue avec les achats de l'utilisateur
        return $this->render('pages/achats_utilisateur/list.html.twig', [
            'achats_utilisateur' => $achats_utilisateur,
            'redirectPath' => $redirectPath,
        ]);
    }

    // Méthode pour afficher les détails d'un achat spécifique
    protected function voirDetailsAchat(int $id, HistoriqueAchatRepository $historiqueAchatRepository, string $redirectPath, TranslatorInterface $translator, RequestStack $requestStack): Response
    {
        // Récupération de la locale pour la traduction des messages
        $locale = $requestStack->getCurrentRequest()->getLocale();

        // Récupération de l'achat spécifique
        $historiqueAchat = $historiqueAchatRepository->find($id);

        // Vérifie si l'achat existe, sinon affiche un message d'erreur
        if (!$historiqueAchat) {
            $this->addFlash('danger', $translator->trans('purchase_history_not_found', ['%id%' => $id], 'messages', $locale));
            return $this->redirectToRoute($redirectPath);
        }
        
        // Récupération des détails de l'achat
        $details_achat = $historiqueAchatRepository->recupererDetailsAchat($id);

        // Rendu de la vue avec les détails de l'achat
        return $this->render('pages/achats_utilisateur/details.html.twig', [
            'details_achat' => $details_achat,
            'imagesArticlesPath' => $this->getParameter('images_articles_path'),
        ]);
    }

    // Route pour télécharger la facture d'un achat
    #[Route('/telecharger_facture/{id}', 'telecharger_facture')]
    public function telechargerFactureAction(int $id, HistoriqueAchatRepository $historiqueAchatRepository, TranslatorInterface $translator, RequestStack $requestStack) {
        // Récupération de la locale pour la traduction
        $locale = $requestStack->getCurrentRequest()->getLocale();

        // Récupération de l'achat spécifique
        $historiqueAchat = $historiqueAchatRepository->find($id);
        
        // Si l'achat n'existe pas, affiche un message d'erreur
        if (!$historiqueAchat) {
            $this->addFlash('danger', $translator->trans('purchase_history_not_found', ['%id%' => $id], 'messages', $locale));
            $redirectPath = $this->isGranted('ROLE_ADMIN') ? 'admin_achats_utilisateur' : 'list_achats_utilisateur';
            return $this->redirectToRoute($redirectPath);
        }

        // Appel de la méthode pour générer et télécharger la facture
        return $this->telechargerFacture($historiqueAchat, $historiqueAchatRepository);
    }

    // Méthode pour générer et télécharger la facture en PDF
    protected function telechargerFacture(HistoriqueAchat $historiqueAchat, HistoriqueAchatRepository $historiqueAchatRepository): Response
    {
        // Récupérer les détails de l'achat
        $details_achat = $historiqueAchatRepository->recupererDetailsAchat($historiqueAchat->getId());

        // Configuration des options de Dompdf pour la génération du PDF
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true);

        // Initialisation de Dompdf avec les options définies
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

        // Rendu du contenu en PDF
        $dompdf->render();

        // Génération du npm de fichier de la facture avec la date et l'ID de l'achat
        $filename = date_format($historiqueAchat->getDateAchat(), 'Y_m_d_H_i') . '_' . $historiqueAchat->getId() . '_facture.pdf';

        // Création de la réponse HTTP pour le téléchargement du fichier PDF
        $response = new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);

        return $response;
    }
}
