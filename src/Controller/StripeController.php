<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Packages;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StripeController extends AbstractController
{

    public function __construct(private ParameterBagInterface $params, private Packages $assets, private RequestStack $requestStack, private ArticleRepository $articleRepository)
    {
        
    }

    #[Route('/stripe', 'stripe.index')]
    public function index(): Response
    {
        return $this->render('pages/stripe/index.html.twig', [
            'stripe_public' => $this->params->get('stripe_public'),
        ]);
    }

    #[Route('/create-checkout-session', 'stripe_checkout', methods: ['GET', 'POST'])]
    public function checkout(Request $request): Response
    {
        /** @var Utilisateur $utilisateur */
        $utilisateur = $this->getUser();

        if (!$utilisateur) {
            return $this->redirectToRoute('security.login');
        }

        $session = $this->requestStack->getSession();
        
        if (!$session) {
            return $this->redirectToRoute('security.login');
        }
        
        $userId = $utilisateur->getId();
        $panier = $session->get('panier', []);

        $lineItems = [];

        /*if (isset($panier[$userId])) {
            $panier = [$userId];
            $panierUtilisateur = $panier[$userId];
            foreach ($panierUtilisateur as $articleId => $quantite) {
                $article = $this->articleRepository->find($articleId);

                $imagesArticlesPath = $this->params->get('images_articles_path');
                $photoArticle = $this->assets->getUrl($imagesArticlesPath) . $article->getPhotoArticle();

                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => $article->getTitre(),
                            //'images' => [$photoArticle],
                        ],
                        'unit_amount' => $article->getPrixUnitaire() * 100, // Convert to cents
                    ],
                    'quantity' => $quantite,
                ];
            }
        }*/

        try {
            Stripe::setApiKey($this->params->get('stripe_secret'));

            $checkout_session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [
                    [
                        'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => 'T-shirt',
                        ],
                        'unit_amount' => 2000,
                        ],
                        'quantity' => 2,
                    ],
                    [
                        'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => 'Shirt',
                        ],
                        'unit_amount' => 450,
                        ],
                        'quantity' => 7,
                    ],
                ],
                'mode' => 'payment',
                'success_url' => $this->generateUrl('stripe_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'cancel_url' => $this->generateUrl('stripe_error', [], UrlGeneratorInterface::ABSOLUTE_URL),
            ]);

            return new JsonResponse(['id' => $checkout_session->id]);
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
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