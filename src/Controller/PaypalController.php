<?php

namespace App\Controller;

use App\Service\PayPalService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PayPalController extends AbstractController
{
    public function __construct(private PayPalService $payPalService)
    {
        
    }

    #[Route('/paypal_payment', name: 'paypal_payment')]
    public function payment(): Response
    {
        $payment = $this->payPalService->createPayment(
            20.00, // Total
            'USD', // Devise
            'Description du paiement',
            $this->generateUrl('paypal_success', [], true), // URL de retour en cas de succès
            $this->generateUrl('paypal_cancel', [], true) // URL de retour en cas d'annulation
        );

        return $this->redirect($payment->getApprovalLink());
    }

    #[Route('/success', name: 'paypal_success')]
    public function success(Request $request): Response
    {
        $paymentId = $request->query->get('paymentId');
        $payerId = $request->query->get('PayerID');

        try {
            $payment = $this->payPalService->executePayment($paymentId, $payerId);
            return new Response('Paiement réussi !');
        } catch (\Exception $ex) {
            return new Response('Erreur : ' . $ex->getMessage());
        }
    }

    #[Route('/cancel', name: 'paypal_cancel')]
    public function cancel(): Response
    {
        return new Response('Paiement annulé.');
    }
}