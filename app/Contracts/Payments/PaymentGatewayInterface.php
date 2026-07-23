<?php

declare(strict_types=1);

namespace App\Contracts\Payments;

use App\DTOs\Payments\HostedCheckoutReturnDTO;
use App\DTOs\Payments\HostedCheckoutSessionDTO;
use App\DTOs\Payments\ParsedWebhookEventDTO;
use App\Exceptions\Payments\InvalidPaymentWebhookSignatureException;
use App\Exceptions\Payments\PaymentGatewayException;
use App\Models\Order;
use App\Models\Payment;

interface PaymentGatewayInterface
{
    /**
     * Create a hosted checkout session and return the redirect URL.
     *
     * @throws PaymentGatewayException
     */
    public function createHostedCheckout(
        Order $order,
        Payment $payment,
        HostedCheckoutReturnDTO $returns,
    ): HostedCheckoutSessionDTO;

    /**
     * @throws InvalidPaymentWebhookSignatureException
     */
    public function verifyWebhookSignature(string $rawPayload, string $signatureHeader): void;

    public function parseWebhook(string $rawPayload): ParsedWebhookEventDTO;
}
