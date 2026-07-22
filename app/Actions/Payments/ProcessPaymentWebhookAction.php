<?php

declare(strict_types=1);

namespace App\Actions\Payments;

use App\DTOs\Payments\ParsedWebhookEventDTO;
use App\Enums\Orders\OrderStatusEnum;
use App\Enums\Payments\PaymentProviderEnum;
use App\Enums\Payments\PaymentStatusEnum;
use App\Enums\Payments\PaymentWebhookOutcomeEnum;
use App\Exceptions\Payments\InvalidPaymentWebhookSignatureException;
use App\Exceptions\Payments\OrderPaidStockConflictException;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentWebhookEvent;
use App\Models\ProductVariant;
use App\Services\Payments\PaymentGatewayResolver;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessPaymentWebhookAction
{
    public function __construct(
        private readonly PaymentGatewayResolver $gatewayResolver,
    ) {}

    /**
     * Verify, persist, and apply a provider webhook. Idempotent by (provider, event_id).
     *
     * @return array{status: string, event_id: string|null}
     *
     * @throws InvalidPaymentWebhookSignatureException
     * @throws Throwable
     */
    public function __invoke(PaymentProviderEnum $provider, string $rawPayload, string $signatureHeader): array
    {
        $gateway = $this->gatewayResolver->for($provider);
        $gateway->verifyWebhookSignature($rawPayload, $signatureHeader);

        $parsed = $gateway->parseWebhook($rawPayload);

        if ($parsed->eventId === '') {
            return ['status' => 'ignored', 'event_id' => null];
        }

        $event = $this->persistEvent($provider, $parsed);

        if ($event === null) {
            // Duplicate (provider, event_id) — already processed or in flight; ack without re-applying.
            return ['status' => 'duplicate', 'event_id' => $parsed->eventId];
        }

        if ($event->processed_at !== null) {
            return ['status' => 'already_processed', 'event_id' => $parsed->eventId];
        }

        try {
            $this->applyOutcome($provider, $parsed);
            $event->update(['processed_at' => now()]);
        } catch (Throwable $e) {
            // Leave processed_at null so the provider can retry on 5xx.
            throw $e;
        }

        return ['status' => 'processed', 'event_id' => $parsed->eventId];
    }

    /**
     * @return PaymentWebhookEvent|null Null when unique constraint hits (duplicate delivery).
     */
    private function persistEvent(PaymentProviderEnum $provider, ParsedWebhookEventDTO $parsed): ?PaymentWebhookEvent
    {
        try {
            return PaymentWebhookEvent::query()->create([
                'provider' => $provider,
                'event_id' => $parsed->eventId,
                'event_type' => $parsed->eventType,
                'payload' => $parsed->payload,
                'processed_at' => null,
            ]);
        } catch (QueryException $e) {
            if ($this->isUniqueViolation($e)) {
                return null;
            }

            throw $e;
        }
    }

    private function isUniqueViolation(QueryException $e): bool
    {
        $sqlState = $e->errorInfo[0] ?? null;
        $driverCode = $e->errorInfo[1] ?? null;

        // SQLSTATE 23000 + MySQL 1062 duplicate, or SQLite UNIQUE constraint.
        if ($sqlState === '23000' || $driverCode === 1062) {
            return true;
        }

        $message = strtolower($e->getMessage());

        return str_contains($message, 'unique') || str_contains($message, 'duplicate');
    }

    /**
     * @throws Throwable
     */
    private function applyOutcome(PaymentProviderEnum $provider, ParsedWebhookEventDTO $parsed): void
    {
        if ($parsed->outcome === PaymentWebhookOutcomeEnum::Ignored) {
            return;
        }

        $payment = $this->resolvePayment($provider, $parsed);

        if ($payment === null) {
            Log::warning('payments.webhook.payment_not_found', [
                'provider' => $provider->value,
                'event_id' => $parsed->eventId,
                'event_type' => $parsed->eventType,
                'payment_id' => $parsed->paymentId,
                'external_id' => $parsed->externalId,
            ]);

            return;
        }

        match ($parsed->outcome) {
            PaymentWebhookOutcomeEnum::Approved => $this->applyApproved($payment, $parsed),
            PaymentWebhookOutcomeEnum::Declined => $this->applyDeclined($payment, $parsed),
            PaymentWebhookOutcomeEnum::Refunded => $this->applyRefunded($payment, $parsed),
            PaymentWebhookOutcomeEnum::Ignored => null,
        };
    }

    private function resolvePayment(PaymentProviderEnum $provider, ParsedWebhookEventDTO $parsed): ?Payment
    {
        if ($parsed->paymentId !== null) {
            /** @var Payment|null $byId */
            $byId = Payment::query()
                ->whereKey($parsed->paymentId)
                ->where('provider', $provider)
                ->first();

            if ($byId !== null) {
                return $byId;
            }
        }

        $candidates = array_values(array_filter([
            $parsed->externalId,
            $parsed->providerPaymentIntent,
        ]));

        if ($candidates === []) {
            return null;
        }

        return Payment::query()
            ->where('provider', $provider)
            ->where(function ($query) use ($candidates): void {
                $query->whereIn('external_id', $candidates);
                foreach ($candidates as $candidate) {
                    $query->orWhere('raw_response->payment_intent', $candidate);
                }
            })
            ->latest('id')
            ->first();
    }

    private function applyApproved(Payment $payment, ParsedWebhookEventDTO $parsed): void
    {
        // TX1: mark payment approved (survives stock failure — D25).
        DB::transaction(function () use ($payment, $parsed): void {
            /** @var Payment $locked */
            $locked = Payment::query()->lockForUpdate()->findOrFail($payment->id);

            if ($locked->status === PaymentStatusEnum::Approved || $locked->status === PaymentStatusEnum::Refunded) {
                return;
            }

            $raw = is_array($locked->raw_response) ? $locked->raw_response : [];
            if ($parsed->providerPaymentIntent !== null) {
                $raw['payment_intent'] = $parsed->providerPaymentIntent;
            }
            $raw['last_webhook'] = [
                'event_id' => $parsed->eventId,
                'event_type' => $parsed->eventType,
            ];

            $locked->update([
                'status' => PaymentStatusEnum::Approved,
                'paid_at' => now(),
                'payment_method' => $parsed->paymentMethod ?? $locked->payment_method,
                'raw_response' => $raw,
            ]);
        });

        // TX2: mark order paid + decrement stock when possible.
        try {
            DB::transaction(function () use ($payment): void {
                /** @var Payment $lockedPayment */
                $lockedPayment = Payment::query()->lockForUpdate()->findOrFail($payment->id);

                /** @var Order $order */
                $order = Order::query()
                    ->with('items')
                    ->lockForUpdate()
                    ->findOrFail($lockedPayment->order_id);

                if ($order->status === OrderStatusEnum::Paid) {
                    return;
                }

                if ($order->status === OrderStatusEnum::Cancelled) {
                    Log::info('payments.webhook.approved_on_cancelled_order', [
                        'order_id' => $order->id,
                        'payment_id' => $lockedPayment->id,
                    ]);

                    return;
                }

                if ($order->status !== OrderStatusEnum::Pending) {
                    return;
                }

                if (! $this->canFulfillStock($order)) {
                    throw OrderPaidStockConflictException::make((int) $order->id, (int) $lockedPayment->id);
                }

                $this->decrementStock($order);

                $order->update([
                    'status' => OrderStatusEnum::Paid,
                    'paid_at' => now(),
                ]);
            });
        } catch (OrderPaidStockConflictException $e) {
            Log::error('payments.webhook.stock_conflict_d25', [
                'message' => $e->getMessage(),
                'order_id' => $payment->order_id,
                'payment_id' => $payment->id,
            ]);
            // Payment stays approved; order stays pending. No auto-refund in F05.
        }
    }

    private function applyDeclined(Payment $payment, ParsedWebhookEventDTO $parsed): void
    {
        DB::transaction(function () use ($payment, $parsed): void {
            /** @var Payment $locked */
            $locked = Payment::query()->lockForUpdate()->findOrFail($payment->id);

            if ($locked->status->isFinal()) {
                return;
            }

            $raw = is_array($locked->raw_response) ? $locked->raw_response : [];
            $raw['last_webhook'] = [
                'event_id' => $parsed->eventId,
                'event_type' => $parsed->eventType,
            ];

            $locked->update([
                'status' => PaymentStatusEnum::Declined,
                'raw_response' => $raw,
            ]);
        });
    }

    private function applyRefunded(Payment $payment, ParsedWebhookEventDTO $parsed): void
    {
        DB::transaction(function () use ($payment, $parsed): void {
            /** @var Payment $locked */
            $locked = Payment::query()->lockForUpdate()->findOrFail($payment->id);

            if ($locked->status === PaymentStatusEnum::Refunded) {
                return;
            }

            $raw = is_array($locked->raw_response) ? $locked->raw_response : [];
            $raw['last_webhook'] = [
                'event_id' => $parsed->eventId,
                'event_type' => $parsed->eventType,
            ];

            $locked->update([
                'status' => PaymentStatusEnum::Refunded,
                'refunded_at' => now(),
                'raw_response' => $raw,
            ]);

            /** @var Order $order */
            $order = Order::query()->lockForUpdate()->findOrFail($locked->order_id);

            if ($order->status === OrderStatusEnum::Paid) {
                $order->update([
                    'status' => OrderStatusEnum::Refunded,
                ]);
            }
            // Stock is intentionally NOT restored in F05 (D24).
        });
    }

    private function canFulfillStock(Order $order): bool
    {
        $variantIds = $order->items
            ->pluck('product_variant_id')
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        if ($variantIds === []) {
            return true;
        }

        /** @var Collection<int, ProductVariant> $variants */
        $variants = ProductVariant::query()
            ->whereIn('id', $variantIds)
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        /** @var OrderItem $item */
        foreach ($order->items as $item) {
            if ($item->product_variant_id === null) {
                return false;
            }

            $variant = $variants->get((int) $item->product_variant_id);

            if ($variant === null || (int) $variant->stock < (int) $item->quantity) {
                return false;
            }
        }

        return true;
    }

    private function decrementStock(Order $order): void
    {
        /** @var OrderItem $item */
        foreach ($order->items as $item) {
            if ($item->product_variant_id === null) {
                continue;
            }

            /** @var ProductVariant $variant */
            $variant = ProductVariant::query()
                ->lockForUpdate()
                ->findOrFail($item->product_variant_id);

            $variant->update([
                'stock' => (int) $variant->stock - (int) $item->quantity,
            ]);
        }
    }
}
