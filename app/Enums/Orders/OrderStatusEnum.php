<?php

declare(strict_types=1);

namespace App\Enums\Orders;

enum OrderStatusEnum: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('enums.order_status.pending'),
            self::Paid => __('enums.order_status.paid'),
            self::Processing => __('enums.order_status.processing'),
            self::Shipped => __('enums.order_status.shipped'),
            self::Delivered => __('enums.order_status.delivered'),
            self::Cancelled => __('enums.order_status.cancelled'),
            self::Refunded => __('enums.order_status.refunded'),
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Delivered, self::Cancelled, self::Refunded], true);
    }

    /**
     * Statuses eligible for the buyer's "mis pedidos" account history (F09 D3).
     *
     * @return array<int, self>
     */
    public static function accountHistoryStatuses(): array
    {
        return [self::Paid, self::Processing, self::Shipped, self::Delivered];
    }

    /**
     * Whether items bought under this order status can be reviewed (F07 D8).
     */
    public function isEligibleForReview(): bool
    {
        return in_array($this, [self::Paid, self::Processing, self::Shipped, self::Delivered], true);
    }
}
