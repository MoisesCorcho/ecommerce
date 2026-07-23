<?php

declare(strict_types=1);

namespace App\Exceptions\Payments;

use RuntimeException;

/**
 * Thrown when a payment webhook signature cannot be verified.
 */
class InvalidPaymentWebhookSignatureException extends RuntimeException
{
    public static function make(): self
    {
        return new self(__('payments.errors.invalid_webhook_signature'));
    }
}
