<?php

    namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\BloqueUtilisateurType;
use App\Repository\HistoriqueConnexionRepository;
    use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

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
        public function bloquerUtilisateur(int $id, UtilisateurRepository $utilisateurRepository, Request $request, EntityManagerInterface $manager): Response
        {
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
                    throw new NotFoundHttpException('L\'utilisateur n\'existe pas !');
            }

            if ($utilisateur === $user) {
                $this->addFlash('danger', 'Vous ne pouvez pas bloquer/débloquer l\'utilisateur courant !');
                return $this->redirectToRoute('admin_utilisateurs');
            }

            $utilisateur->setBloque(!$utilisateur->isBloque());

            $manager->persist($utilisateur);
            $manager->flush();

            $this->addFlash('success', 'Cet utilisateur a été bloqué !');

            return $this->redirectToRoute('admin_utilisateurs');
        }
    }