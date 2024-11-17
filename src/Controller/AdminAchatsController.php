<?php

    namespace App\Controller;

    // Importation des classes nécessaires
    use App\Entity\Utilisateur;
    use App\Repository\HistoriqueAchatRepository;
    use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\RequestStack;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

    class AdminAchatsController extends AbstractController {

        // Route pour afficher les achats d'un utilisateur
        #[Route('/admin/achats', 'admin_achats', methods: ['GET'])]
        public function list_achats(HistoriqueAchatRepository $historiqueAchatRepository, TranslatorInterface $translator, RequestStack $requestStack): Response
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

            $achats = $historiqueAchatRepository->findBy([], ['id' => 'ASC']);

            // Rendu de la vue avec les données des achats
            return $this->render('pages/admin/achats.html.twig', [
                'achats' => $achats,
                'redirectPath' => 'admin_details_achat_utilisateur',
                'imagesArticlesPath' => $this->getParameter('images_articles_path'),
            ]);
        }
    }