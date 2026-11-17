<?php

declare(strict_types=1);

namespace App\Domain\Payment\Exception;

final class InvalidMoneyException extends \InvalidArgumentException
{
    private const NOT_POSITIVE_MESSAGE = 'Amount must be positive, got %d.';

    public static function notPositive(int $amountInMinorUnits): self
    {
        return new self(sprintf(self::NOT_POSITIVE_MESSAGE, $amountInMinorUnits));
    }
}
