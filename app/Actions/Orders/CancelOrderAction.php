<?php

declare(strict_types=1);

namespace App\Actions\Orders;

use App\Enums\Orders\OrderStatusEnum;
use App\Exceptions\Orders\OrderCannotBeCancelledException;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Throwable;

class CancelOrderAction
{
    /**
     * Admin-only domain transition: pending → cancelled. Does not restore stock (never decremented in F04).
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

            $order->update([
                'status' => OrderStatusEnum::Cancelled,
            ]);

            return $order->fresh(['items']) ?? $order;
        });
    }
}
