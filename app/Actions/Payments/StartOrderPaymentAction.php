<?php

declare(strict_types=1);

namespace App\Actions\Payments;

use App\DTOs\Payments\HostedCheckoutReturnDTO;
use App\DTOs\Payments\StartOrderPaymentResultDTO;
use App\Enums\Orders\OrderStatusEnum;
use App\Enums\Payments\PaymentStatusEnum;
use App\Exceptions\Payments\OrderNotPayableException;
use App\Exceptions\Payments\PaymentGatewayException;
use App\Exceptions\Payments\PaymentStockUnavailableException;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\ProductVariant;
use App\Services\Payments\PaymentGatewayResolver;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Throwable;

class StartOrderPaymentAction
{
    public function __construct(
        private readonly PaymentGatewayResolver $gatewayResolver,
    ) {}

    /**
     * Start hosted checkout for a pending order. Does not mark paid or decrement stock.
     *
     * @throws OrderNotPayableException
     * @throws PaymentStockUnavailableException
     * @throws PaymentGatewayException
     * @throws Throwable
     */
    public function __invoke(int $orderId): StartOrderPaymentResultDTO
    {
        // Keep DB locks only for order validation + Payment insert.
        // Provider HTTP must not run inside lockForUpdate (availability under load).
        /** @var array{order: Order, payment: Payment, returns: HostedCheckoutReturnDTO} $prepared */
        $prepared = DB::transaction(function () use ($orderId): array {
            /** @var Order $order */
            $order = Order::query()
                ->with(['items'])
                ->lockForUpdate()
                ->findOrFail($orderId);

            if ($order->status === OrderStatusEnum::Paid) {
                throw OrderNotPayableException::alreadyPaid();
            }

            if ($order->status !== OrderStatusEnum::Pending) {
                throw OrderNotPayableException::make();
            }

            if ((int) $order->total < $order->currency->minimumChargeableAmount()) {
                throw OrderNotPayableException::make();
            }

            $this->assertStockAvailable($order);

            $provider = $order->currency->paymentProvider();

            $payment = Payment::query()->create([
                'order_id' => $order->id,
                'provider' => $provider,
                'currency' => $order->currency,
                'external_id' => null,
                'payment_method' => null,
                'status' => PaymentStatusEnum::Pending,
                'amount' => (int) $order->total,
                'raw_response' => null,
                'paid_at' => null,
                'refunded_at' => null,
            ]);

            // Signed return URLs are bearer tokens — only passed to the provider, never logged.
            $returns = new HostedCheckoutReturnDTO(
                successUrl: URL::temporarySignedRoute(
                    'orders.thank-you',
                    now()->addDay(),
                    ['order' => $order->id, 'payment' => 'processing'],
                ),
                cancelUrl: URL::temporarySignedRoute(
                    'orders.thank-you',
                    now()->addDay(),
                    ['order' => $order->id, 'payment' => 'cancelled'],
                ),
            );

            return [
                'order' => $order,
                'payment' => $payment,
                'returns' => $returns,
            ];
        });

        $order = $prepared['order'];
        $payment = $prepared['payment'];
        $returns = $prepared['returns'];
        $provider = $payment->provider;

        try {
            $gateway = $this->gatewayResolver->for($provider);
            $session = $gateway->createHostedCheckout($order, $payment, $returns);
        } catch (PaymentGatewayException $e) {
            Log::channel('payments')->warning('payments.start.gateway_failed', [
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'provider' => $provider->value,
                'exception' => $e::class,
                'diagnostic' => $e->diagnostic,
            ]);
            $this->discardUnstartedPayment($payment);
            throw $e;
        } catch (Throwable $e) {
            Log::channel('payments')->warning('payments.start.gateway_failed', [
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'provider' => $provider->value,
                'exception' => $e::class,
                'diagnostic' => $e->getMessage(),
            ]);
            $this->discardUnstartedPayment($payment);
            throw PaymentGatewayException::make($e, $e->getMessage());
        }

        $payment->update([
            'external_id' => $session->externalId,
            'raw_response' => $session->raw,
        ]);

        Log::channel('payments')->info('payments.start.created', [
            'order_id' => $order->id,
            'payment_id' => $payment->id,
            'provider' => $provider->value,
            'amount' => (int) $payment->amount,
            'currency' => $payment->currency->value,
        ]);

        return new StartOrderPaymentResultDTO(
            payment: $payment->fresh() ?? $payment,
            redirectUrl: $session->redirectUrl,
        );
    }

    /**
     * Remove a pending payment that never obtained a provider session (failed create).
     */
    private function discardUnstartedPayment(Payment $payment): void
    {
        if ($payment->external_id !== null || $payment->status !== PaymentStatusEnum::Pending) {
            return;
        }

        $payment->delete();
    }

    /**
     * @throws PaymentStockUnavailableException
     */
    private function assertStockAvailable(Order $order): void
    {
        $order->loadMissing('items');

        $variantIds = $order->items
            ->pluck('product_variant_id')
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        if ($variantIds === []) {
            return;
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
                throw PaymentStockUnavailableException::make();
            }

            $variant = $variants->get((int) $item->product_variant_id);

            if ($variant === null || ! $variant->is_active || (int) $variant->stock < (int) $item->quantity) {
                throw PaymentStockUnavailableException::make();
            }
        }
    }
}
