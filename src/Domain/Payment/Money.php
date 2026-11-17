<?php

declare(strict_types=1);

namespace App\Domain\Payment;

use App\Domain\Payment\Exception\InvalidMoneyException;

/**
 * An amount in minor units (kopecks, cents). Never a float: floats cannot represent money exactly.
 */
final readonly class Money
{
    /**
     * Valid for every Currency case (ISO 4217 exponent 2).
     * Revisit together with DECIMAL_FORMAT before adding currencies such as JPY or KWD.
     */
    public const MINOR_UNITS_PER_MAJOR = 100;

    private const DECIMAL_FORMAT = '%d.%02d';

    public function __construct(
        public int $amountInMinorUnits,
        public Currency $currency,
    ) {
        if ($amountInMinorUnits <= 0) {
            throw InvalidMoneyException::notPositive($amountInMinorUnits);
        }
    }

    public function exceeds(int $limitInMinorUnits): bool
    {
        return $this->amountInMinorUnits > $limitInMinorUnits;
    }

    public function toDecimalString(): string
    {
        return sprintf(
            self::DECIMAL_FORMAT,
            intdiv($this->amountInMinorUnits, self::MINOR_UNITS_PER_MAJOR),
            $this->amountInMinorUnits % self::MINOR_UNITS_PER_MAJOR,
        );
    }
}
