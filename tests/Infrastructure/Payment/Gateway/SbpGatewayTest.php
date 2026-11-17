<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Payment\Gateway;

use App\Domain\Payment\Currency;
use App\Domain\Payment\DeclineReason;
use App\Domain\Payment\Money;
use App\Domain\Payment\PaymentMethod;
use App\Domain\Payment\PaymentStatus;
use App\Infrastructure\Payment\Gateway\SbpGateway;
use PHPUnit\Framework\TestCase;

final class SbpGatewayTest extends TestCase
{
    private SbpGateway $gateway;

    protected function setUp(): void
    {
        $this->gateway = new SbpGateway();
    }

    public function testSupportsOnlyTheSbpMethod(): void
    {
        foreach (PaymentMethod::cases() as $method) {
            self::assertSame(PaymentMethod::Sbp === $method, $this->gateway->supports($method));
        }
    }

    public function testApprovesRoublesUpToTheLimit(): void
    {
        $result = $this->gateway->pay(new Money(SbpGateway::LIMIT_IN_MINOR_UNITS, Currency::Rub));

        self::assertSame(PaymentStatus::Approved, $result->status);
        self::assertSame(PaymentMethod::Sbp, $result->method);
        self::assertNull($result->declineReason);
    }

    public function testDeclinesAnythingButRoubles(): void
    {
        $result = $this->gateway->pay(new Money(100, Currency::Usd));

        self::assertSame(PaymentStatus::Declined, $result->status);
        self::assertSame(DeclineReason::CurrencyNotSupported, $result->declineReason);
    }

    public function testDeclinesAmountAboveTheLimit(): void
    {
        $result = $this->gateway->pay(new Money(SbpGateway::LIMIT_IN_MINOR_UNITS + 1, Currency::Rub));

        self::assertSame(PaymentStatus::Declined, $result->status);
        self::assertSame(DeclineReason::LimitExceeded, $result->declineReason);
    }
}
