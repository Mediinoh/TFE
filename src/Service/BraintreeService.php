<?php

namespace App\Service;

use Braintree\Gateway;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class BraintreeService
{
    private Gateway $gateway;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->gateway = new Gateway([
            'environment' => $parameterBag->get('environment'),
            'merchantId' => $parameterBag->get('merchantId'),
            'publicKey' => $parameterBag->get('publicKey'),
            'privateKey' => $parameterBag->get('privateKey'),
        ]);
    }

    public function getGateway(): Gateway
    {
        return $this->gateway;
    }
}