<?php

declare(strict_types=1);

namespace App\Tests\Domain\Payment;

use App\Domain\Payment\Currency;
use App\Domain\Payment\Exception\InvalidMoneyException;
use App\Domain\Payment\Money;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    public function testKeepsAmountAndCurrency(): void
    {
        $money = new Money(150_000, Currency::Rub);

        self::assertSame(150_000, $money->amountInMinorUnits);
        self::assertSame(Currency::Rub, $money->currency);
    }

    #[DataProvider('nonPositiveAmounts')]
    public function testRejectsNonPositiveAmount(int $amount): void
    {
        $this->expectException(InvalidMoneyException::class);

        new Money($amount, Currency::Rub);
    }

    /**
     * @return iterable<string, array{int}>
     */
    public static function nonPositiveAmounts(): iterable
    {
        yield 'zero' => [0];
        yield 'negative' => [-1];
    }

    public function testExceedsIsStrict(): void
    {
        $money = new Money(100, Currency::Usd);

        self::assertFalse($money->exceeds(100));
        self::assertTrue($money->exceeds(99));
    }

    #[DataProvider('decimalRepresentations')]
    public function testFormatsMinorUnitsAsDecimalString(int $amount, string $expected): void
    {
        self::assertSame($expected, (new Money($amount, Currency::Eur))->toDecimalString());
    }

    /**
     * @return iterable<string, array{int, string}>
     */
    public static function decimalRepresentations(): iterable
    {
        yield 'one minor unit' => [1, '0.01'];
        yield 'less than one major unit' => [5, '0.05'];
        yield 'exactly one major unit' => [100, '1.00'];
        yield 'regular amount' => [150_000, '1500.00'];
        yield 'with minor part' => [150_075, '1500.75'];
    }
}
