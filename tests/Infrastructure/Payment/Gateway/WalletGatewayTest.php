<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Payment\Gateway;

use App\Domain\Payment\Currency;
use App\Domain\Payment\DeclineReason;
use App\Domain\Payment\Money;
use App\Domain\Payment\PaymentMethod;
use App\Domain\Payment\PaymentStatus;
use App\Infrastructure\Payment\Gateway\WalletGateway;
use PHPUnit\Framework\TestCase;

final class WalletGatewayTest extends TestCase
{
    private WalletGateway $gateway;

    protected function setUp(): void
    {
        $this->gateway = new WalletGateway();
    }

    public function testSupportsOnlyTheWalletMethod(): void
    {
        foreach (PaymentMethod::cases() as $method) {
            self::assertSame(PaymentMethod::Wallet === $method, $this->gateway->supports($method));
        }
    }

    public function testApprovesAmountUpToTheLimit(): void
    {
        $result = $this->gateway->pay(new Money(WalletGateway::LIMIT_IN_MINOR_UNITS, Currency::Eur));

        self::assertSame(PaymentStatus::Approved, $result->status);
        self::assertSame(PaymentMethod::Wallet, $result->method);
        self::assertNull($result->declineReason);
    }

    public function testDeclinesAmountAboveTheLimit(): void
    {
        $result = $this->gateway->pay(new Money(WalletGateway::LIMIT_IN_MINOR_UNITS + 1, Currency::Rub));

        self::assertSame(PaymentStatus::Declined, $result->status);
        self::assertSame(DeclineReason::LimitExceeded, $result->declineReason);
    }
}
