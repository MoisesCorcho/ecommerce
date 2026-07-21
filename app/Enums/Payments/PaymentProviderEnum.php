<?php

declare(strict_types=1);

namespace App\Enums\Payments;

enum PaymentProviderEnum: string
{
    case Stripe = 'stripe';
    case Bold = 'bold';

    public function label(): string
    {
        return match ($this) {
            self::Stripe => __('enums.payment_provider.stripe'),
            self::Bold => __('enums.payment_provider.bold'),
        };
    }
}
