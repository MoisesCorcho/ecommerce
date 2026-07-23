<?php

declare(strict_types=1);

namespace App\Enums\Payments;

/**
 * Normalized domain outcomes from provider webhook events.
 */
enum PaymentWebhookOutcomeEnum: string
{
    case Approved = 'approved';
    case Declined = 'declined';
    case Refunded = 'refunded';
    case Ignored = 'ignored';
}
