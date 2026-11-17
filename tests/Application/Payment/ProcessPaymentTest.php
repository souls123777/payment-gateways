<?php

declare(strict_types=1);

namespace App\Tests\Application\Payment;

use App\Application\Payment\Port\PaymentGateway;
use App\Application\Payment\Port\PaymentGatewayFactory;
use App\Application\Payment\ProcessPayment;
use App\Domain\Payment\Currency;
use App\Domain\Payment\Money;
use App\Domain\Payment\PaymentMethod;
use App\Domain\Payment\PaymentResult;
use PHPUnit\Framework\TestCase;

final class ProcessPaymentTest extends TestCase
{
    public function testPaysThroughTheGatewayChosenByTheFactory(): void
    {
        $expected = PaymentResult::approved(PaymentMethod::Card);
        $amount = new Money(1_000, Currency::Rub);

        $gateway = $this->createMock(PaymentGateway::class);
        $gateway->expects(self::once())->method('pay')->with($amount)->willReturn($expected);

        $factory = $this->createStub(PaymentGatewayFactory::class);
        $factory->method('forMethod')->willReturn($gateway);

        $result = (new ProcessPayment($factory))->execute(PaymentMethod::Card, $amount);

        self::assertSame($expected, $result);
    }
}
