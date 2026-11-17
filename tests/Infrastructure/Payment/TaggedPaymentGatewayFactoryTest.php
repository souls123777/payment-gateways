<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Payment;

use App\Application\Payment\Exception\PaymentGatewayNotFoundException;
use App\Domain\Payment\PaymentMethod;
use App\Infrastructure\Payment\Gateway\CardGateway;
use App\Infrastructure\Payment\Gateway\SbpGateway;
use App\Infrastructure\Payment\TaggedPaymentGatewayFactory;
use PHPUnit\Framework\TestCase;

final class TaggedPaymentGatewayFactoryTest extends TestCase
{
    public function testReturnsTheGatewayThatSupportsTheMethod(): void
    {
        $card = new CardGateway();
        $sbp = new SbpGateway();
        $factory = new TaggedPaymentGatewayFactory([$card, $sbp]);

        self::assertSame($card, $factory->forMethod(PaymentMethod::Card));
        self::assertSame($sbp, $factory->forMethod(PaymentMethod::Sbp));
    }

    public function testThrowsWhenNoGatewaySupportsTheMethod(): void
    {
        $factory = new TaggedPaymentGatewayFactory([new CardGateway()]);

        $this->expectException(PaymentGatewayNotFoundException::class);

        $factory->forMethod(PaymentMethod::Wallet);
    }

    public function testThrowsWhenThereAreNoGatewaysAtAll(): void
    {
        $factory = new TaggedPaymentGatewayFactory([]);

        $this->expectException(PaymentGatewayNotFoundException::class);

        $factory->forMethod(PaymentMethod::Card);
    }
}
