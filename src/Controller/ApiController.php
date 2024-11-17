<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Categorie;
use App\Entity\Utilisateur;
use App\Form\InscriptionType;
use App\Repository\ArticleRepository;
use App\Repository\CategorieRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/admin/api')]
class ApiController extends AbstractController {

    public function __construct()
    {
        
    }

    #[Route('/', 'api_doc', methods: ['GET'])]
    public function api(CategorieRepository $categorieRepository, ArticleRepository $articleRepository, UtilisateurRepository $utilisateurRepository): Response
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

        // Récupère toutes les catégories triées selon l'ID
        $categories = $categorieRepository->findBy([], ['id' => 'ASC']);

        // Récupère tous les articles triés selon l'ID
        $articles = $articleRepository->findBy([], ['id' => 'ASC']);

        // Récupère tous les utilisateurs triés selon l'ID
        $utilisateurs = $utilisateurRepository->findBy([], ['id' => 'ASC']);

        // Rendu de la vue avec les achats de l'utilisateur
        return $this->render('pages/admin/api.html.twig', [
            'categories' => $categories,
            'articles' => $articles,
            'utilisateurs' => $utilisateurs,
        ]);
    }

    #[Route('/inscription', 'api_inscription', methods: ['POST'])]
    public function apiInscription(Request $request, EntityManagerInterface $manager): JsonResponse
    {
        // Crée une nouvelle instance de l'entité
        $utilisateur = new Utilisateur();

        // Crée le formulaire et gère les données de requête
        $form = $this->createForm(InscriptionType::class, $utilisateur);
        $form->handleRequest($request);

        // Si le formulaire est valide, l'utilisateur est sauvée en DB
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($utilisateur);
            $manager->flush();

            return new JsonResponse([
                'message' => 'Utilisateur crée avec succès',
            ], JsonResponse::HTTP_CREATED);
        }

        // Si le formulaire n'est pas valide, retourne les erreurs
        $errors = [];
        foreach($form->getErrors(true) as $error) {
            $errors[] = [
                'field' => $error->getOrigin()->getName(),
                'message' => $error->getMessage(),
            ];
        }

        return new JsonResponse([
            'errors' => $errors,
        ], JsonResponse::HTTP_BAD_REQUEST);
    }

    #[Route('/logout', 'api_logout', methods: ['POST'])]
    public function apiLogout(): void
    {
        // Symfony gère la déconnexion automatiquement
    }

    private function getArticleData(Article $article): array
    {
        // Transforme les données de l'article en tableau
        return [
            "id" => $article->getId(),
            "photo_article" => $article->getPhotoArticle(),
            "titre" => $article->getTitre(),
            "categorie" => $this->getCategorieData($article->getCategorie()),
            "prix_unitaire" => $article->getPrixUnitaire(),
            "description" => $article->getDescription(),
        ];
    }

    private function getCategorieData(Categorie $categorie): array
    {
        return [
            "id" => $categorie->getId(),
            "nom_categorie" => $categorie->getNomCategorie(),
        ];
    }

    private function getUtilisateurData(Utilisateur $utilisateur): array
    {
        return [
            "id" => $utilisateur->getId(),
            "nom" => $utilisateur->getNom(),
            "prenom" => $utilisateur->getPrenom(),
            "adresse" => $utilisateur->getAdresse(),
            "code_postal" => $utilisateur->getCodePostal(),
            "date_naissance" => $utilisateur->getPseudo(),
            "bloque" => $utilisateur->isBloque(),
            "admin" => in_array('ROLE_ADMIN', $utilisateur->getRoles()),
        ];
    }

    #[Route('/articles/{id}', 'api_article', methods: ['GET'])]
    public function apiGetArticle(int $id, ArticleRepository $articleRepository, SerializerInterface $serializer)
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
        
        // Récupère l'article selon l'ID
        $article = $articleRepository->findOneBy(['id' => $id]);

        // Transforme le tableau en JSON
        $json = $serializer->serialize($this->getArticleData($article), 'json', [
            'json_encode_options' => JSON_UNESCAPED_UNICODE,
        ]);

        // Retourne la réponse JSON
        return new JsonResponse($json, 200, [], true);
    }
    
    #[Route('/articles', 'api_articles', methods: ['GET'])]
    public function apiGetArticles(ArticleRepository $articleRepository, SerializerInterface $serializer)
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
        
        // Récupère tous les articles triés par ID
        $articles = $articleRepository->findBy([], ['id' => 'ASC']);
        
        // Crée un tableau de données
        $data = [];
        
        foreach ($articles as $article) {
            // Crée un tableau de données pour chaque article
            $data[] = $this->getArticleData($article);
        }

        // Transforme le tableau en JSON
        $json = $serializer->serialize($data, 'json', [
            'json_encode_options' => JSON_UNESCAPED_UNICODE,
        ]);

        // Renvoie la réponse JSON
        return new JsonResponse($json, 200, [], true);
    }

    #[Route('/categories/{id}', 'api_categorie', methods: ['GET'])]
    public function apiGetCategorie(int $id, CategorieRepository $categorieRepository, SerializerInterface $serializer)
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
        
        // Récupère la catégorie selon l'ID
        $categorie = $categorieRepository->findOneBy(['id' => $id]);

        // Transforme le tableau en JSON
        $json = $serializer->serialize($this->getCategorieData($categorie), 'json', [
            'json_encode_options' => JSON_UNESCAPED_UNICODE,
        ]);

        // Retourne la réponse JSON
        return new JsonResponse($json, 200, [], true);
    }

    #[Route('/categories', 'api_categories', methods: ['GET'])]
    public function apiGetCategories(CategorieRepository $categorieRepository, SerializerInterface $serializer)
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
        
        // Récupère toutes les catégories triées par ID
        $categories = $categorieRepository->findBy([], ['id' => 'ASC']);
        
        // Crée un tableau de données
        $data = [];
        
        foreach ($categories as $categorie) {
            // Crée un tableau de données pour chaque catégorie
            $data[] = $this->getCategorieData($categorie);
        }

        // Transforme le tableau en JSON
        $json = $serializer->serialize($data, 'json', [
            'json_encode_options' => JSON_UNESCAPED_UNICODE,
        ]);

        // Renvoie la réponse JSON
        return new JsonResponse($json, 200, [], true);
    }

    #[Route('/utilisateurs/{id}', 'api_utilisateur', methods: ['GET'])]
    public function apiGetUtilisateur(int $id, UtilisateurRepository $utilisateurRepository)
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
        
        // Récupère l'utilisateur selon l'ID
        $utilisateur = $utilisateurRepository->findOneBy(['id' => $id]);

        $data = $this->getUtilisateurData($utilisateur);

        // Retourne la réponse JSON
        return $this->json($data, context: [
            'json_encode_options' => JSON_UNESCAPED_UNICODE,
        ]);
    }

    #[Route('/utilisateurs', 'api_utilisateurs', methods: ['GET'])]
    public function apiGetUtilisateurs(UtilisateurRepository $utilisateurRepository)
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
        
        // Récupère tous les utilisateurs triés par ID
        $utilisateurs = $utilisateurRepository->findBy([], ['id' => 'ASC']);
        
        // Crée un tableau de données
        $data = [];
        
        foreach ($utilisateurs as $utilisateur) {
            // Crée un tableau de données pour chaque utilisateur
            $data[] = $this->getUtilisateurData($utilisateur);
        }

        // Renvoie la réponse JSON
        return $this->json($data, context: [
            'json_encode_options' => JSON_UNESCAPED_UNICODE,
        ]);
    }

}