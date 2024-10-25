<?php

    namespace App\Controller;

    // Importation des classes nécessaires
    use App\Entity\Utilisateur;
    use App\Form\BloqueUtilisateurType;
    use App\Repository\HistoriqueConnexionRepository;
    use App\Repository\UtilisateurRepository;
    use Doctrine\ORM\EntityManagerInterface;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\RequestStack;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Annotation\Route;
    use Symfony\Contracts\Translation\TranslatorInterface;

    class AdminUtilisateursController extends AbstractController
    {

        #[Route('/admin/utilisateurs', 'admin_utilisateurs', methods: ['GET'])]
        public function index(UtilisateurRepository $utilisateurRepository, HistoriqueConnexionRepository $historiqueConnexionRepository, Request $request): Response
        {
            // Récupération si l'utilisateur est authentifié
            /** @var Utilisateur $user */
            $user = $this->getUser();

            // Redirection vers la page de connexion si l'utilisateur n'est pas authentifié
            if (!$user) {
                return $this->redirectToRoute('security.login');
            }

            // Redirection vers la page d'accueil si l'utilisateur n'est pas le rôle ADMIN
            if (!$this->isGranted('ROLE_ADMIN')) {
                return $this->redirectToRoute('home.index');
            }

            // Récupération de tous les utilisateurs
            $utilisateurs = $utilisateurRepository->findAll();
            $infosUtilisateurs = [];

            // Boucle pour construire les informations de chaque utilisateur
            foreach($utilisateurs as $utilisateur) {
                $nbConnexionsJour = $historiqueConnexionRepository->recupererNbConnexions($utilisateur);
                $nbConnexionsSemaine = $historiqueConnexionRepository->recupererNbConnexions($utilisateur, true);
                
                // Création du formulaire de blocage d'utilisateur
                $bloquerUtilisateurForm = $this->createForm(BloqueUtilisateurType::class, null, [
                    'action' => $this->generateUrl('admin_bloquer_utilisateur', ['id' => $utilisateur->getId()]),
                    'method' => 'POST',
                    'bloque' => !$utilisateur->isBloque(),
                ]);
                $bloquerUtilisateurForm->handleRequest($request);
                
                // Tableau contenant les infos de chaque utilissateur
                $infosUtilisateurs[] = [
                    'utilisateur' => $utilisateur,
                    'nbConnexionsJour' => $nbConnexionsJour,
                    'nbConnexionsSemaine' => $nbConnexionsSemaine,
                    'bloqueUtilisateurForm' => $bloquerUtilisateurForm->createView(),
                ];
            }

            // Rendu de la vue avec les données utilisateurs
            return $this->render('pages/admin/utilisateurs.html.twig', [
                'infosUtilisateurs' => $infosUtilisateurs,
            ]);
        }

        #[Route('/bloquer_utilisateur/{id}', 'admin_bloquer_utilisateur', methods: ['POST'])]
        public function bloquerUtilisateur(int $id, UtilisateurRepository $utilisateurRepository, EntityManagerInterface $manager, TranslatorInterface$translator, RequestStack $requestStack): Response
        {
            // Récupération de la langue actuelle
            $locale = $requestStack->getCurrentRequest()->getLocale();

            // Récupération de l'utilisateur connecté
            /** @var Utilisateur $user */
            $user = $this->getUser();
            
            // Redirection vers la page de connexion s'il n'est pas connecté
            if (!$user) {
                return $this->redirectToRoute('security.login');
            }

            // Redirection vers la page d'accueil si l'utilisateur a le rôle ADMIN
            if (!$this->isGranted('ROLE_ADMIN')) {
                return $this->redirectToRoute('home.index');
            }

            // Récupération de l'utilisateur à bloquer
            $utilisateur = $utilisateurRepository->find($id);

            // Vérification si l'utilisateur existe
            if (!$utilisateur) {
                    $this->addFlash('danger', $translator->trans('user_not_found', ['%id%' => $id], 'messages', $locale));
                    return $this->redirectToRoute('admin_utilisateurs');
            }

            // Interdiction de bloquer l'utilisateur actuellement connecté
            if ($utilisateur === $user) {
                $this->addFlash('danger', $translator->trans('cannot_block_unbock_current_user', [], 'messages', $locale));
                return $this->redirectToRoute('admin_utilisateurs');
            }

            // Inversion de l'état de blocage
            $blocked = !$utilisateur->isBloque();

            $utilisateur->setBloque($blocked);

            // Persistance et enregistrement dans la page de données
            $manager->persist($utilisateur);
            $manager->flush();

            // Affichage d'un message de succès
            $this->addFlash('success', $translator->trans($blocked ? 'user_blocked' : 'user_unblocked', [], 'messages', $locale));

            // Redirection vers la liste des utilisateurs
            return $this->redirectToRoute('admin_utilisateurs');
        }

        #[Route('/supprimer_utilisateur/{id}', 'admin_supprimer_utilisateur', methods: ['POST'])]
        public function supprimerUtilisateur(int $id, UtilisateurRepository $utilisateurRepository, EntityManagerInterface $manager, TranslatorInterface$translator, RequestStack $requestStack): Response
        {
            // Récupération de la langue actuelle
            $locale = $requestStack->getCurrentRequest()->getLocale();

            // Récupération de l'utilisateur connecté
            /** @var Utilisateur $user */
            $user = $this->getUser();
            
            // Redirige vers la page de connexion si l'utilisateur n'est pas connecté
            if (!$user) {
                return $this->redirectToRoute('security.login');
            }

            // Redirection vers la page d'accueil si l'utilisateur n'a pas le rôle ADMIN
            if (!$this->isGranted('ROLE_ADMIN')) {
                return $this->redirectToRoute('home.index');
            }

            // Récupération de l'utilisateur à supprimer
            $utilisateur = $utilisateurRepository->find($id);

            // Vérification si l'utilisateur existe pas
            if (!$utilisateur) {
                    $this->addFlash('danger', $translator->trans('user_not_found', ['%id%' => $id], 'messages', $locale));
                    return $this->redirectToRoute('admin_utilisateurs');
            }

            // Interdiction de supprimer l'utilisateur actuellement connecté
            if ($utilisateur === $user) {
                $this->addFlash('danger', $translator->trans('cannot_remove_current_user', [], 'messages', $locale));
                return $this->redirectToRoute('admin_utilisateurs');
            }

            // Suppression de l'utilisateur de la base de données
            $manager->remove($utilisateur);
            $manager->flush();

            // Affichage d'un message de succès
            $this->addFlash('success', $translator->trans('user_removed', [], 'messages', $locale));

            // Redirection vers la liste des utilisateurs
            return $this->redirectToRoute('admin_utilisateurs');
        }
    }