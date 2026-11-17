<?php

declare(strict_types=1);

namespace App\Infrastructure\Payment\Gateway;

use App\Application\Payment\Port\PaymentGateway;
use App\Domain\Payment\Currency;
use App\Domain\Payment\DeclineReason;
use App\Domain\Payment\Money;
use App\Domain\Payment\PaymentMethod;
use App\Domain\Payment\PaymentResult;

/**
 * Demo stand-in for a fast-payments gateway: roubles only, no network.
 */
final class SbpGateway implements PaymentGateway
{
    public const LIMIT_IN_MINOR_UNITS = 10_000_000;

    public function supports(PaymentMethod $method): bool
    {
        return PaymentMethod::Sbp === $method;
    }

    public function pay(Money $amount): PaymentResult
    {
        if (Currency::Rub !== $amount->currency) {
            return PaymentResult::declined(PaymentMethod::Sbp, DeclineReason::CurrencyNotSupported);
        }

        if ($amount->exceeds(self::LIMIT_IN_MINOR_UNITS)) {
            return PaymentResult::declined(PaymentMethod::Sbp, DeclineReason::LimitExceeded);
        }

        return PaymentResult::approved(PaymentMethod::Sbp);
    }
}
