<?php

declare(strict_types=1);

namespace App\Actions\Orders;

use App\Enums\Orders\OrderStatusEnum;
use App\Exceptions\Orders\OrderCannotBeCancelledException;
use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Throwable;

class CancelOrderAction
{
    /**
     * Admin-only domain transition: pending → cancelled.
     * Releases coupon redemption and decrements used_count (R9). Does not restore stock.
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
