<?php

declare(strict_types=1);

namespace App\Application\Payment\Port;

use App\Application\Payment\Exception\PaymentGatewayNotFoundException;
use App\Domain\Payment\PaymentMethod;

/**
 * Factory port: chooses the gateway (strategy) that handles a payment method.
 */
interface PaymentGatewayFactory
{
    /**
     * @throws PaymentGatewayNotFoundException
     */
    public function forMethod(PaymentMethod $method): PaymentGateway;
}
