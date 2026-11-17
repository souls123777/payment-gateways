<?php

declare(strict_types=1);

namespace App\Domain\Payment;

enum PaymentMethod: string
{
    case Card = 'card';
    case Sbp = 'sbp';
    case Wallet = 'wallet';
}
