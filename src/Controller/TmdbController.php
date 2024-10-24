<?php

namespace App\Controller;
use App\Service\TmdbApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TmdbController extends AbstractController
{
    public function __construct(private TmdbApiService $tmdbApiService)
    {

    }

    #[Route('/tmdb/films', 'popular_movies', methods: ['GET'])]
    public function listFilms(): Response
    {
        try {
            $films = $this->tmdbApiService->getPopularMovies();
            return $this->render('pages/tmdb/films.twig.html', [ 
                'films' => $films,
                'tmdb_images_url' => $this->getParameter('tmdb_api_images_url'),
            ]);
        } catch(\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()]);
        }
    }

    #[Route('/tmdb/tvshows', 'popular_series', methods: ['GET'])]
    public function listSeries(): Response
    {
        try {
            $tvShows = $this->tmdbApiService->getPopularTvShows();
            return $this->render('pages/tmdb/tvshows.twig.html', [ 
                'tvShows' => $tvShows,
                'tmdb_images_url' => $this->getParameter('tmdb_api_images_url'),
            ]);
        } catch(\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()]);
        }
    }

    #[Route('/tmdb/actors', 'popular_actors', methods: ['GET'])]
    public function listActors(): Response
    {
        try {
            $actors = $this->tmdbApiService->getPopularActors();
            return $this->render('pages/tmdb/actors.html.twig', [ 
                'actors' => $actors,
                'tmdb_images_url' => $this->getParameter('tmdb_api_images_url'),
            ]);
        } catch(\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()]);
        }
    }
}
