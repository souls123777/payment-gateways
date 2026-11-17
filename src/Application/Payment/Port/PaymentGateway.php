<?php

declare(strict_types=1);

namespace App\Application\Payment\Port;

use App\Domain\Payment\Money;
use App\Domain\Payment\PaymentMethod;
use App\Domain\Payment\PaymentResult;

/**
 * Strategy: one implementation per way of taking money (an adapter for a payment provider).
 */
interface PaymentGateway
{
    public function supports(PaymentMethod $method): bool;

    /**
     * A decline is returned as a result, not thrown.
     */
    public function pay(Money $amount): PaymentResult;
}
