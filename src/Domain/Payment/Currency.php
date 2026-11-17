<?php

declare(strict_types=1);

namespace App\Domain\Payment;

enum Currency: string
{
    case Rub = 'RUB';
    case Usd = 'USD';
    case Eur = 'EUR';
}
