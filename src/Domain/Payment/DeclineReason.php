<?php

declare(strict_types=1);

namespace App\Domain\Payment;

enum DeclineReason: string
{
    case LimitExceeded = 'limit_exceeded';
    case CurrencyNotSupported = 'currency_not_supported';
}
