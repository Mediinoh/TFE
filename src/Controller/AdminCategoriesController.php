<?php

namespace App\Controller;

// Importation des classes nécessaires
use App\Entity\Utilisateur;
use App\Form\AjoutCategorieType;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminCategoriesController extends AbstractController
{
    #[Route('/admin/categories', 'admin_categories', methods: ['GET', 'POST'])]
    public function index(CategorieRepository $categorieRepository, Request $request, EntityManagerInterface $manager): Response
    {
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

        // Création du formulaire pour ajouter une catégorie
        $form = $this->createForm(AjoutCategorieType::class);
        $form->handleRequest($request);
        
        // Traitement du formulaire lorsqu'il est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupération des données du formulaire
            $categorie = $form->getData();
            
            // Persistance de la nouvelle catégorie dans la base de données
            $manager->persist($categorie);
            $manager->flush();

            // Réinitialisation du formulaire après l'ajout de la catégorie
            $form = $this->createForm(AjoutCategorieType::class);
        }

        // Récupération de toutes les catégories pour affichage
        $categories = $categorieRepository->findBy([], ['id' => 'ASC']);

        // Rendu de la vue avec la liste des catégories et le formulaire
        return $this->render('pages/admin/categories.html.twig', [
            'categories' => $categories,
            'form' => $form->createView(),
        ]);
    }
}
