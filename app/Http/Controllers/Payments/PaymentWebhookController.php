<?php

declare(strict_types=1);

namespace App\Http\Controllers\Payments;

use App\Actions\Payments\ProcessPaymentWebhookAction;
use App\Enums\Payments\PaymentProviderEnum;
use App\Exceptions\Payments\InvalidPaymentWebhookSignatureException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
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
            // Rate-limit log noise under signature-forge storms; always return 400.
            RateLimiter::attempt(
                'payments.webhook.invalid_signature:'.$provider->value.':'.hash('xxh3', (string) $request->ip()),
                maxAttempts: 10,
                callback: static function () use ($provider): void {
                    Log::channel('payments')->warning('payments.webhook.invalid_signature', [
                        'provider' => $provider->value,
                    ]);
                },
                decaySeconds: 60,
            );

            return response('invalid_signature', 400);
        } catch (Throwable $e) {
            report($e);

            return response('processing_error', 500);
        }
    }
}
