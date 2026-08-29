<?php

declare(strict_types=1);

namespace App\DTOs\Orders;

use App\Enums\Commerce\CurrencyEnum;

readonly class CheckoutPreviewDTO
{
    /**
     * @param  list<CheckoutPreviewLineDTO>  $lines
     */
    public function __construct(
        public int $cartId,
        public CurrencyEnum $currency,
        public array $lines,
        public int $subtotal,
        public int $shippingCost,
        public int $discount,
        public int $taxAmount,
        public int $total,
        public int $thresholdDiscount = 0,
    ) {}
}
