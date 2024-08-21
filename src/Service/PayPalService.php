<?php

namespace App\Service;

use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

class PayPalService
{
    private ApiContext $apiContext;

    public function __construct(string $clientId, string $clientSecret, string $mode)
    {
        $this->apiContext = new ApiContext(
            new OAuthTokenCredential($clientId, $clientSecret)
        );

        $this->apiContext->setConfig([
            'mode' => $mode,
            'http.ConnectionTimeOut' => 30,
            'log.LogEnabled' => true,
            'log.FileName' => '../PayPal.log',
            'log.LogLevel' => 'DEBUG',
            'validation.level' => 'log',
            'cache.enabled' => true,
        ]);
    }

    public function createPayment(float $total, string $currency, string $description, string $returnUrl, string $cancelUrl)
    {
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $amount = new Amount();
        $amount->setTotal($total);
        $amount->setCurrency($currency);

        $transaction = new Transaction();
        $transaction->setAmount($amount);
        $transaction->setDescription($description);

        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl($returnUrl);
        $redirectUrls->setCancelUrl($cancelUrl);

        $payment = new Payment();
        $payment->setIntent('sale')
                ->setPayer($payer)
                ->setTransactions([$transaction])
                ->setRedirectUrls($redirectUrls);
        try {
            $payment->create($this->apiContext);
            return $payment;
        } catch (\Exception $ex) {
            // Gérer l'erreur
            throw new \Exception("Erreur lors de la création du paiement : " . $ex->getMessage());
        }
    }

    public function executePayment(string $paymentId, string $payerId)
    {
        $payment = Payment::get($paymentId, $this->apiContext);

        $execution = new PaymentExecution();
        $execution->setPayerId($payerId);

        try {
            $result = $payment->execute($execution, $this->apiContext);
            return $result;
        } catch (\Exception $ex) {
            // Gérer l'erreur
            throw new \Exception("Erreur lors de l'exécution du paiement : " . $ex->getMessage());
        }
    }
}