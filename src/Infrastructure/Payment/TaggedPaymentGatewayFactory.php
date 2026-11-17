<?php

declare(strict_types=1);

namespace App\Infrastructure\Payment;

use App\Application\Payment\Exception\PaymentGatewayNotFoundException;
use App\Application\Payment\Port\PaymentGateway;
use App\Application\Payment\Port\PaymentGatewayFactory;
use App\Domain\Payment\PaymentMethod;

/**
 * Picks the first gateway that supports the method. The gateways are collected by the
 * Symfony container through a service tag (see config/services.yaml), so adding a gateway
 * means adding one class: this factory does not change.
 */
final readonly class TaggedPaymentGatewayFactory implements PaymentGatewayFactory
{
    /**
     * @param iterable<PaymentGateway> $gateways
     */
    public function __construct(private iterable $gateways) {}

    public function forMethod(PaymentMethod $method): PaymentGateway
    {
        foreach ($this->gateways as $gateway) {
            if ($gateway->supports($method)) {
                return $gateway;
            }
        }

        throw PaymentGatewayNotFoundException::forMethod($method);
    }
}
