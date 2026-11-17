<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Payment\Gateway;

use App\Domain\Payment\Currency;
use App\Domain\Payment\DeclineReason;
use App\Domain\Payment\Money;
use App\Domain\Payment\PaymentMethod;
use App\Domain\Payment\PaymentStatus;
use App\Infrastructure\Payment\Gateway\CardGateway;
use PHPUnit\Framework\TestCase;

final class CardGatewayTest extends TestCase
{
    private CardGateway $gateway;

    protected function setUp(): void
    {
        $this->gateway = new CardGateway();
    }

    public function testSupportsOnlyTheCardMethod(): void
    {
        foreach (PaymentMethod::cases() as $method) {
            self::assertSame(PaymentMethod::Card === $method, $this->gateway->supports($method));
        }
    }

    public function testApprovesAmountUpToTheLimit(): void
    {
        $result = $this->gateway->pay(new Money(CardGateway::LIMIT_IN_MINOR_UNITS, Currency::Rub));

        self::assertSame(PaymentStatus::Approved, $result->status);
        self::assertSame(PaymentMethod::Card, $result->method);
        self::assertNull($result->declineReason);
    }

    public function testDeclinesAmountAboveTheLimit(): void
    {
        $result = $this->gateway->pay(new Money(CardGateway::LIMIT_IN_MINOR_UNITS + 1, Currency::Usd));

        self::assertSame(PaymentStatus::Declined, $result->status);
        self::assertSame(DeclineReason::LimitExceeded, $result->declineReason);
    }
}
