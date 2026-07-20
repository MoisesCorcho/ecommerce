<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentProviderEnum: string
{
    case Stripe = 'stripe';
    case Bold = 'bold';

    public function label(): string
    {
        return match ($this) {
            self::Stripe => 'Stripe',
            self::Bold => 'Bold',
        };
    }
}
