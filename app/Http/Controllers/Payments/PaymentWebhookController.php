<?php

declare(strict_types=1);

namespace App\Http\Controllers\Payments;

use App\Actions\Payments\ProcessPaymentWebhookAction;
use App\Enums\Payments\PaymentProviderEnum;
use App\Exceptions\Payments\InvalidPaymentWebhookSignatureException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

class PaymentWebhookController extends Controller
{
    public function stripe(
        Request $request,
        ProcessPaymentWebhookAction $processWebhook,
    ): Response {
        return $this->handle(
            PaymentProviderEnum::Stripe,
            $request,
            $processWebhook,
            (string) $request->header('Stripe-Signature', ''),
        );
    }

    public function bold(
        Request $request,
        ProcessPaymentWebhookAction $processWebhook,
    ): Response {
        return $this->handle(
            PaymentProviderEnum::Bold,
            $request,
            $processWebhook,
            (string) $request->header('x-bold-signature', ''),
        );
    }

    private function handle(
        PaymentProviderEnum $provider,
        Request $request,
        ProcessPaymentWebhookAction $processWebhook,
        string $signatureHeader,
    ): Response {
        $rawPayload = $request->getContent();

        try {
            $result = $processWebhook($provider, $rawPayload, $signatureHeader);

            return response($result['status'], 200);
        } catch (InvalidPaymentWebhookSignatureException) {
            return response('invalid_signature', 400);
        } catch (Throwable $e) {
            report($e);

            return response('processing_error', 500);
        }
    }
}
