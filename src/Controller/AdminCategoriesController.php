<?php

namespace App\Controller;

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
        /** @var Utilisateur $user */
        $user = $this->getUser();

        if (!$user) {
            $this->redirectToRoute('security.login');
        }

        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->redirectToRoute('home.index');
        }

        $form = $this->createForm(AjoutCategorieType::class);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $categorie = $form->getData();
            
            $manager->persist($categorie);
            $manager->flush();
            $form = $this->createForm(AjoutCategorieType::class);
        }

        $categories = $categorieRepository->findBy([], ['id' => 'ASC']);

        return $this->render('pages/admin/categories.html.twig', [
            'categories' => $categories,
            'form' => $form->createView(),
        ]);
    }
}
