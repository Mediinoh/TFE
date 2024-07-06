<?php

namespace App\Controller;

use App\Service\TmdbApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    public function __construct(private TmdbApiService $tmdbApiService)
    {

    }

    #[Route('/api/films', 'api_films', methods: ['GET'])]
    public function listFilms(): JsonResponse
    {
        try {
            $films = $this->tmdbApiService->getPopularMovies();
            return new JsonResponse($films, 200);
        } catch(\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()]);
        }
    }
}
