<?php

declare(strict_types=1);

namespace App\Application\Payment;

use App\Application\Payment\Exception\PaymentGatewayNotFoundException;
use App\Application\Payment\Port\PaymentGatewayFactory;
use App\Domain\Payment\Money;
use App\Domain\Payment\PaymentMethod;
use App\Domain\Payment\PaymentResult;

/**
 * Use case: take a payment through the gateway that matches the method.
 */
final readonly class ProcessPayment
{
    public function __construct(private PaymentGatewayFactory $gatewayFactory) {}

    /**
     * @throws PaymentGatewayNotFoundException
     */
    public function execute(PaymentMethod $method, Money $amount): PaymentResult
    {
        return $this->gatewayFactory->forMethod($method)->pay($amount);
    }
}
