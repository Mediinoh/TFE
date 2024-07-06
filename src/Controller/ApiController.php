<?php

namespace App\Controller;

use App\Service\BraintreeService;
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

    #[Route('/checkout', 'checkout')]
    public function checkout(BraintreeService $braintreeService): Response
    {
        $clientToken = $braintreeService->getGateway()->clientToken()->generate();

        return $this->render('checkout/checkout.html.twig', [
            'clientToken' => $clientToken,
        ]);
    }

    
    #[Route('/checkout/process', 'checkout_process')]
    public function processCheckout(BraintreeService $braintreeService): Response
    {
        // Code pour traiter le paiement Braintree
        return new Response();
    }
}
