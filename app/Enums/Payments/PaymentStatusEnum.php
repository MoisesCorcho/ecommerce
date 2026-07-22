<?php

declare(strict_types=1);

namespace App\Enums\Payments;

enum PaymentStatusEnum: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Declined = 'declined';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('enums.payment_status.pending'),
            self::Approved => __('enums.payment_status.approved'),
            self::Declined => __('enums.payment_status.declined'),
            self::Refunded => __('enums.payment_status.refunded'),
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Approved, self::Declined, self::Refunded], true);
    }
}
