<?php

namespace App\Controller;

use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StripeController extends AbstractController
{

    public function __construct(private ParameterBagInterface $params)
    {
        
    }

    #[Route('/stripe', 'stripe.index')]
    public function index(): Response
    {
        return $this->render('pages/stripe/index.html.twig', [
            'stripe_public' => $this->params->get('stripe_public'),
        ]);
    }

    #[Route('/create-checkout-session', 'stripe_checkout', methods: ['POST'])]
    public function checkout(): JsonResponse
    {
        try {
            Stripe::setApiKey($this->params->get('stripe_secret'));

            $checkout_session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => 'T-shirt',
                        ],
                        'unit_amount' => 2000,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $this->generateUrl('stripe_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'cancel_url' => $this->generateUrl('stripe_error', [], UrlGeneratorInterface::ABSOLUTE_URL),
            ]);

            return new JsonResponse(['id' => $checkout_session->id]);
        } catch (\Exception $e) {
            // Log the error message
            $this->addFlash('error', 'An error occurred: ' . $e->getMessage());
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/stripe_success', 'stripe_success')]
    public function success(): Response
    {
        return $this->render('pages/stripe/success.html.twig', [
            
        ]);
    }

    #[Route('/stripe_error', 'stripe_error')]
    public function error(): Response
    {
        return $this->render('pages/stripe/error.html.twig', [
            
        ]);
    }
}