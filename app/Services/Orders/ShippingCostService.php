<?php

declare(strict_types=1);

namespace App\Services\Orders;

use App\Enums\Commerce\CurrencyEnum;

/**
 * Resolves the single "standard shipping" cost for F04 from config.
 */
class ShippingCostService
{
    public function standardCost(CurrencyEnum $currency): int
    {
        return match ($currency) {
            CurrencyEnum::Eur => (int) config('ecommerce.shipping.standard_cost_eur', 0),
            CurrencyEnum::Usd => (int) config('ecommerce.shipping.standard_cost_usd', 0),
            CurrencyEnum::Cop => (int) config('ecommerce.shipping.standard_cost_cop', 0),
        };
    }
}
