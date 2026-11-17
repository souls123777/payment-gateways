<?php

declare(strict_types=1);

namespace App\Infrastructure\Payment\Gateway;

use App\Application\Payment\Port\PaymentGateway;
use App\Domain\Payment\DeclineReason;
use App\Domain\Payment\Money;
use App\Domain\Payment\PaymentMethod;
use App\Domain\Payment\PaymentResult;

/**
 * Demo stand-in for a card acquirer: no network, the outcome depends only on the amount.
 * The limit is compared in minor units whatever the currency, which is enough for a fake.
 */
final class CardGateway implements PaymentGateway
{
    public const LIMIT_IN_MINOR_UNITS = 50_000_000;

    public function supports(PaymentMethod $method): bool
    {
        return PaymentMethod::Card === $method;
    }

    public function pay(Money $amount): PaymentResult
    {
        if ($amount->exceeds(self::LIMIT_IN_MINOR_UNITS)) {
            return PaymentResult::declined(PaymentMethod::Card, DeclineReason::LimitExceeded);
        }

        return PaymentResult::approved(PaymentMethod::Card);
    }
}
