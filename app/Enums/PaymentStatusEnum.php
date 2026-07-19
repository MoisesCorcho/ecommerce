<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentStatusEnum: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Declined = 'declined';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente',
            self::Approved => 'Aprobado',
            self::Declined => 'Rechazado',
            self::Refunded => 'Reembolsado',
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Approved, self::Declined, self::Refunded], true);
    }
}
