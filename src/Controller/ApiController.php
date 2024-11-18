<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Categorie;
use App\Entity\Utilisateur;
use App\Form\AjoutCategorieType;
use App\Form\InscriptionType;
use App\Form\ProfilType;
use App\Form\SupprimeArticleType;
use App\Repository\ArticleRepository;
use App\Repository\CategorieRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api')]
class ApiController extends AbstractController {

    private function getArticleData(Article $article): array
    {
        // Transforme les données de l'article en tableau
        return [
            'id' => $article->getId(),
            'photo_article' => $article->getPhotoArticle(),
            'titre' => $article->getTitre(),
            'categorie' => $this->getCategorieData($article->getCategorie()),
            'prix_unitaire' => $article->getPrixUnitaire(),
            'description' => $article->getDescription(),
            'supprime' => $article->isSupprime(),
        ];
    }

    private function getCategorieData(Categorie $categorie): array
    {
        return [
            'id' => $categorie->getId(),
            'nom_categorie' => $categorie->getNomCategorie(),
        ];
    }

    private function getUtilisateurData(Utilisateur $utilisateur): array
    {
        return [
            'id' => $utilisateur->getId(),
            'nom' => $utilisateur->getNom(),
            'prenom' => $utilisateur->getPrenom(),
            'email' => $utilisateur->getEmail(),
            'adresse' => $utilisateur->getAdresse(),
            'code_postal' => $utilisateur->getCodePostal(),
            'pseudo' => $utilisateur->getPseudo(),
            'date_naissance' => $utilisateur->getDateNaissance(),
            'photo_profil' => $utilisateur->getPhotoProfil(),
            'bloque' => $utilisateur->isBloque(),
            'admin' => in_array('ROLE_ADMIN', $utilisateur->getRoles()),
        ];
    }

    #[Route('/doc', name: 'api_doc')]
    public function index(): Response
    {
        return $this->redirect('/swagger-ui');
    }

    #[Route('/inscription', 'api_inscription', methods: ['POST'])]
    #[OA\Post(
        summary: "Créer un utilisateur",
        requestBody: new OA\RequestBody(
            description: "Données de l'utilisateur",
            required: true,
            content:  new OA\JsonContent(
                example: ["nom" => "Dupont", "prenom" => "Jean", "email" => "jean.dupont@example.com", "password" => "password123"]
            )
            ),
            responses: [
                new OA\Response(response: 201, description: "Utilisateur créé"),
                new OA\Response(response: 400, description: "Données invalides"),
            ]
    )]
    public function apiInscription(Request $request, EntityManagerInterface $manager, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $utilisateur = new Utilisateur();

        $form = $this->createForm(InscriptionType::class, $utilisateur, [
            'csrf_protection' => $request->attributes->get('disable_csrf', false) ? false : true,
        ]);
        $form->submit($data);

        if ($form->isValid()) {
            $manager->persist($utilisateur);
            $manager->flush();

            return $this->json(['message' => 'Utilisateur créé avec succès'], Response::HTTP_CREATED);
        }

        // Collecte des erreurs
        $errors = [];
        foreach ($validator->validate($utilisateur) as $violation) {
            $errors[] = [
                'field' => $violation->getPropertyPath(),
                'message' => $violation->getMessage(),
            ];
        }

        return $this->json(['errors' => $errors], Response::HTTP_BAD_REQUEST);
    }

    #[Route('/articles/{id}', 'api_article', methods: ['GET'])]
    public function apiGetArticle(int $id, ArticleRepository $articleRepository)
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
        $data = $this->getArticleData($article);

        // Renvoie la réponse JSON
        return $this->json($data, context: [
            'json_encode_options' => JSON_UNESCAPED_UNICODE,
        ]);
    }
    
    #[Route('/articles', 'api_articles', methods: ['GET'])]
    public function apiGetArticles(ArticleRepository $articleRepository)
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

        // Renvoie la réponse JSON
        return $this->json($data, context: [
            'json_encode_options' => JSON_UNESCAPED_UNICODE,
        ]);
    }

    #[Route('/categories/{id}', 'api_categorie', methods: ['GET'])]
    public function apiGetCategorie(int $id, CategorieRepository $categorieRepository)
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

        $data = $this->getCategorieData($categorie);

        // Renvoie la réponse JSON
        return $this->json($data, context: [
            'json_encode_options' => JSON_UNESCAPED_UNICODE,
        ]);
    }

    #[Route('/categories', 'api_categories', methods: ['GET'])]
    public function apiGetCategories(CategorieRepository $categorieRepository)
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

        // Renvoie la réponse JSON
        return $this->json($data, context: [
            'json_encode_options' => JSON_UNESCAPED_UNICODE,
        ]);
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

    #[Route('/categories', 'api_post_categorie', methods: ['POST'])]
    public function apiPostCategorie(Request $request, EntityManagerInterface $manager, ValidatorInterface $validator) {
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
        $categorie = new Categorie();
        $form = $this->createForm(AjoutCategorieType::class, $categorie, [
            'csrf_protection' => $request->attributes->get('disable_csrf', false) ? false : true,
        ]);
        $form->submit($request->request->all());

        // Traitement du formulaire lorsqu'il est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Persistance de la nouvelle catégorie dans la base de données
            $manager->persist($categorie);
            $manager->flush();

            // Retourner la réponse avec succès
            return $this->json(['message' => 'Catégorie ajoutée avec succès'], Response::HTTP_CREATED);
        }

        // Collecte des erreurs de formulaire
        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = [
                'field' => $error->getOrigin()->getName(),
                'message' => $error->getMessage(),
            ];
        }

        // Retourner les erreurs de validation
        return $this->json(['errors' => $errors], Response::HTTP_BAD_REQUEST);
    }

    #[Route('/utilisateurs/profil/{id}', 'api_put_profil', methods: ['PUT'])]
    public function apiPutProfil(int $id, Request $request, EntityManagerInterface $manager, ValidatorInterface $validator, UtilisateurRepository $utilisateurRepository) {
        
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

        // Récupère l'utilisateur avec l'identifiant
        $utilisateur = $utilisateurRepository->find($id);

        // Redirige si l'utilisateur est introuvable ou s'il ne correspond pas à l'utilisateur authentifié
        if (is_null($utilisateur) || $this->getUser() !== $utilisateur) {
            return $this->redirectToRoute('home.index');
        }

        // Création et gestion du formulaire de modification du profil
        $form = $this->createForm(ProfilType::class, $utilisateur, [
            'csrf_protection' => $request->attributes->get('disable_csrf', false) ? false : true,
        ]);
        // $form->handleRequest($request);
        $form->submit($request->request->all());

        // Vérifie si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            if (!is_null($formData->getPlainPassword())) {
                // Mise à jour du mot de passe s'il est modifié par l'utilisateur
                $utilisateur->setPlainPassword($formData->getPlainPassword());
            }

            // Gestion de l'upload de la photo de profil si elle a été ajoutée par l'utilisateur
            $photoProfil = $form->get('photo_profil')->getData();
            if (!is_null($photoProfil) && $photoProfil instanceof UploadedFile) {
               $nomFichier = uniqid() . '_' . date('Ymd_His') . '.png';
               $photoProfil->move($this->getParameter('images_photos_profil_directory'), $nomFichier);
               $utilisateur->setPhotoProfil($nomFichier);
            }

            // Persistance des modifications
            $manager->persist($utilisateur);
            $manager->flush();

            return $this->json(['message' => 'Utilisateur créé avec succès'], Response::HTTP_CREATED);
        }

        // Collecte des erreurs
        $errors = [];
        foreach ($validator->validate($$utilisateur) as $violation) {
            $errors[] = [
                'field' => $violation->getPropertyPath(),
                'message' => $violation->getMessage(),
            ];
        }

        return $this->json(['errors' => $errors], Response::HTTP_BAD_REQUEST);
    }

    #[Route('/articles/{id}', 'api_delete_article', methods: ['DELETE'])]
    public function apiDeleteArticle(int $id, Request $request, EntityManagerInterface $manager, ValidatorInterface $validator, ArticleRepository $articleRepository) {
        
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

        // Récupère l'article avec l'identifiant
        $article = $articleRepository->find($id);

        // Création et gestion du formulaire de modification du profil
        $form = $this->createForm(SupprimeArticleType::class, null, [
            'csrf_protection' => $request->attributes->get('disable_csrf', false) ? false : true,
            'supprime' => !$article->isSupprime(),
        ]);
        // $form->handleRequest($request);
        $form->submit($request->request->all());

        // Vérifie si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Marque l'article comme supprimé ou le réactive
            $article->setSupprime(!$article->isSupprime());

            // Enregistrement des modifications dans la base de données
            $manager->persist($article);
            $manager->flush();

            return $this->json(['message' => 'Article supprimé/réinjecté avec succès'], Response::HTTP_OK);
        }

        // Collecte des erreurs
        $errors = [];
        foreach ($validator->validate($$utilisateur) as $violation) {
            $errors[] = [
                'field' => $violation->getPropertyPath(),
                'message' => $violation->getMessage(),
            ];
        }

        return $this->json(['errors' => $errors], Response::HTTP_BAD_REQUEST);
    }

}