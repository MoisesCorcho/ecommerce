<?php

declare(strict_types=1);

namespace App\Enums\Wishlist;

use Filament\Support\Contracts\HasLabel;

enum WishlistNotificationTypeEnum: string implements HasLabel
{
    case PriceDrop = 'price_drop';
    case LowStock = 'low_stock';

    public function getLabel(): string
    {
        return $this->label();
    }

    public function label(): string
    {
        return __('wishlist.notification_types.'.$this->value);
    }
}
