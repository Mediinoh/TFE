<?php

    namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\BloqueUtilisateurType;
use App\Repository\HistoriqueConnexionRepository;
    use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

    class AdminUtilisateursController extends AbstractController
    {

        #[Route('/admin/utilisateurs', 'admin_utilisateurs', methods: ['GET'])]
        public function index(UtilisateurRepository $utilisateurRepository, HistoriqueConnexionRepository $historiqueConnexionRepository, Request $request): Response
        {
            /** @var Utilisateur $user */
            $user = $this->getUser();

            if (!$user) {
                $this->redirectToRoute('security.login');
            }

            if (!$this->isGranted('ROLE_ADMIN')) {
                $this->redirectToRoute('home.index');
            }

            $utilisateurs = $utilisateurRepository->findAll();
            $infosUtilisateurs = [];

            foreach($utilisateurs as $utilisateur) {
                $nbConnexionsJour = $historiqueConnexionRepository->recupererNbConnexions($utilisateur);
                $nbConnexionsSemaine = $historiqueConnexionRepository->recupererNbConnexions($utilisateur, true);
                
                $bloquerUtilisateurForm = $this->createForm(BloqueUtilisateurType::class, null, [
                    'action' => $this->generateUrl('admin_bloquer_utilisateur', ['id' => $utilisateur->getId()]),
                    'method' => 'POST',
                    'bloque' => !$utilisateur->isBloque(),
                ]);
                $bloquerUtilisateurForm->handleRequest($request);
                
                $infosUtilisateurs[] = [
                    'utilisateur' => $utilisateur,
                    'nbConnexionsJour' => $nbConnexionsJour,
                    'nbConnexionsSemaine' => $nbConnexionsSemaine,
                    'bloqueUtilisateurForm' => $bloquerUtilisateurForm->createView(),
                ];
            }

            return $this->render('pages/admin/utilisateurs.html.twig', [
                'infosUtilisateurs' => $infosUtilisateurs,
            ]);
        }

        #[Route('/bloquer_utilisateur/{id}', 'admin_bloquer_utilisateur', methods: ['POST'])]
        public function bloquerUtilisateur(int $id, UtilisateurRepository $utilisateurRepository, EntityManagerInterface $manager, TranslatorInterface$translator, RequestStack $requestStack): Response
        {
            $locale = $requestStack->getCurrentRequest()->getLocale();

            /** @var Utilisateur $user */
            $user = $this->getUser();
            
            if (!$user) {
                return $this->redirectToRoute('security.login');
            }

            if (!$this->isGranted('ROLE_ADMIN')) {
                $this->redirectToRoute('home.index');
            }

            $utilisateur = $utilisateurRepository->find($id);

            if (!$utilisateur) {
                    $this->addFlash('danger', $translator->trans('user_not_found', ['%id%' => $id], 'messages', $locale));
                    return $this->redirectToRoute('admin_utilisateurs');
            }

            if ($utilisateur === $user) {
                $this->addFlash('danger', $translator->trans('cannot_block_unbock_current_user', [], 'messages', $locale));
                return $this->redirectToRoute('admin_utilisateurs');
            }

            $blocked = !$utilisateur->isBloque();

            $utilisateur->setBloque($blocked);

            $manager->persist($utilisateur);
            $manager->flush();

            $this->addFlash('success', $translator->trans($blocked ? 'user_blocked' : 'user_unblocked', [], 'messages', $locale));

            return $this->redirectToRoute('admin_utilisateurs');
        }
    }