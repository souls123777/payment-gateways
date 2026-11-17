<?php

declare(strict_types=1);

namespace App\Application\Payment\Exception;

use App\Domain\Payment\PaymentMethod;

final class PaymentGatewayNotFoundException extends \RuntimeException
{
    private const MESSAGE = 'No payment gateway is registered for method "%s".';

    public static function forMethod(PaymentMethod $method): self
    {
        return new self(sprintf(self::MESSAGE, $method->value));
    }
}
