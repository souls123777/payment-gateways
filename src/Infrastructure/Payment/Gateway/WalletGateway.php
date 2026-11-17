<?php

declare(strict_types=1);

namespace App\Infrastructure\Payment\Gateway;

use App\Application\Payment\Port\PaymentGateway;
use App\Domain\Payment\DeclineReason;
use App\Domain\Payment\Money;
use App\Domain\Payment\PaymentMethod;
use App\Domain\Payment\PaymentResult;

/**
 * Demo stand-in for an e-wallet gateway: a low per-payment limit, no network.
 */
final class WalletGateway implements PaymentGateway
{
    public const LIMIT_IN_MINOR_UNITS = 1_500_000;

    public function supports(PaymentMethod $method): bool
    {
        return PaymentMethod::Wallet === $method;
    }

    public function pay(Money $amount): PaymentResult
    {
        if ($amount->exceeds(self::LIMIT_IN_MINOR_UNITS)) {
            return PaymentResult::declined(PaymentMethod::Wallet, DeclineReason::LimitExceeded);
        }

        return PaymentResult::approved(PaymentMethod::Wallet);
    }
}
