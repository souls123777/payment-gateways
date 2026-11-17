<?php

declare(strict_types=1);

namespace App\Domain\Payment;

/**
 * Our own status vocabulary: every gateway maps its provider-specific answers onto it.
 */
enum PaymentStatus: string
{
    case Approved = 'approved';
    case Declined = 'declined';
}
