<?php

declare(strict_types=1);

namespace App\Domain\Payment;

/**
 * Outcome of one payment attempt. A decline is a normal business result, not an exception.
 */
final readonly class PaymentResult
{
    private function __construct(
        public PaymentMethod $method,
        public PaymentStatus $status,
        public ?DeclineReason $declineReason,
    ) {}

    public static function approved(PaymentMethod $method): self
    {
        return new self($method, PaymentStatus::Approved, null);
    }

    public static function declined(PaymentMethod $method, DeclineReason $reason): self
    {
        return new self($method, PaymentStatus::Declined, $reason);
    }

    public function isApproved(): bool
    {
        return PaymentStatus::Approved === $this->status;
    }
}
