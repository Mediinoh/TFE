<?php

// Importation des services et des classes nécessaires
namespace App\Controller;
use App\Service\TmdbApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TmdbController extends AbstractController
{
    // Injection du service utilisé pour interagir avec l'API TMDB via le constructeur
    public function __construct(private TmdbApiService $tmdbApiService)
    {

    }

    // Route pour lister les films populaires
    #[Route('/tmdb/films', 'popular_movies', methods: ['GET'])]
    public function listFilms(): Response
    {
        try {
            // Appelle le service pour récupérer les films populaires
            $films = $this->tmdbApiService->getPopularMovies();

            // Rendu de la vue avec les films récupérés
            return $this->render('pages/tmdb/films.twig.html', [ 
                'films' => $films,
                'tmdb_images_url' => $this->getParameter('tmdb_api_images_url'),
            ]);
        } catch(\Exception $e) {
            // En cas d'erreur, retourne une réponse JSON avec le message d'erreur
            return new JsonResponse(['error' => $e->getMessage()]);
        }
    }

    // Route pour lister les séries télévisées populaires
    #[Route('/tmdb/tvshows', 'popular_series', methods: ['GET'])]
    public function listSeries(): Response
    {
        try {
            // Appelle le service pour récupérer les séries populaires
            $tvShows = $this->tmdbApiService->getPopularTvShows();

            // Rendu de la vue avec les séries récupérées
            return $this->render('pages/tmdb/tvshows.twig.html', [ 
                'tvShows' => $tvShows,
                'tmdb_images_url' => $this->getParameter('tmdb_api_images_url'),
            ]);
        } catch(\Exception $e) {
            // En cas d'erreur, retourne une réponse JSON avec le message d'erreur
            return new JsonResponse(['error' => $e->getMessage()]);
        }
    }

    // Route pour liste les acteurs populaires
    #[Route('/tmdb/actors', 'popular_actors', methods: ['GET'])]
    public function listActors(): Response
    {
        try {
            // Appelle le service pour récupérer les acteurs populaires
            $actors = $this->tmdbApiService->getPopularActors();
            
            // Rendu de la vue avec les acteurs récupérés
            return $this->render('pages/tmdb/actors.html.twig', [ 
                'actors' => $actors,
                'tmdb_images_url' => $this->getParameter('tmdb_api_images_url'),
            ]);
        } catch(\Exception $e) {
            // En cas d'erreur, retourne une réponse JSON avec le message d'erreur
            return new JsonResponse(['error' => $e->getMessage()]);
        }
    }
}
