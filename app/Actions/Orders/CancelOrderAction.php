<?php

declare(strict_types=1);

namespace App\Actions\Orders;

use App\Enums\Orders\OrderStatusEnum;
use App\Enums\Payments\PaymentStatusEnum;
use App\Exceptions\Orders\OrderCannotBeCancelledException;
use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Throwable;

class CancelOrderAction
{
    /**
     * Admin-only domain transition: pending → cancelled.
     * Releases coupon redemption and decrements used_count (R9). Does not restore stock.
     *
     * Blocks cancel when a payment is already approved or refunded (D25 / money captured)
     * so coupon inventory is not released after a successful charge.
     *
     * @throws OrderCannotBeCancelledException
     * @throws Throwable
     */
    public function __invoke(int $orderId): Order
    {
        return DB::transaction(function () use ($orderId): Order {
            /** @var Order $order */
            $order = Order::query()->lockForUpdate()->findOrFail($orderId);

            if ($order->status !== OrderStatusEnum::Pending) {
                throw OrderCannotBeCancelledException::make();
            }

            $hasCapturedPayment = Payment::query()
                ->where('order_id', $order->id)
                ->whereIn('status', [
                    PaymentStatusEnum::Approved,
                    PaymentStatusEnum::Refunded,
                ])
                ->lockForUpdate()
                ->exists();

            if ($hasCapturedPayment) {
                throw OrderCannotBeCancelledException::becausePaymentCaptured();
            }

            /** @var CouponRedemption|null $redemption */
            $redemption = CouponRedemption::query()
                ->where('order_id', $order->id)
                ->lockForUpdate()
                ->first();

            if ($redemption !== null) {
                $couponId = (int) $redemption->coupon_id;

                $redemption->delete();

                /** @var Coupon|null $coupon */
                $coupon = Coupon::query()->whereKey($couponId)->lockForUpdate()->first();

                if ($coupon !== null) {
                    $coupon->update([
                        'used_count' => max(0, (int) $coupon->used_count - 1),
                    ]);
                }
            }

            $order->update([
                'status' => OrderStatusEnum::Cancelled,
            ]);

            return $order->fresh(['items', 'couponRedemption']) ?? $order;
        });
    }
}
