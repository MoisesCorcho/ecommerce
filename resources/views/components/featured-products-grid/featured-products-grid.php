<?php

use App\Enums\Commerce\CurrencyEnum;
use App\Models\Product;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

new class extends Component
{
    public string $currency;

    public function mount(): void
    {
        $code = (string) config('ecommerce.default_currency', CurrencyEnum::Cop->value);

        $this->currency = CurrencyEnum::tryFrom($code)?->value ?? CurrencyEnum::Cop->value;
    }

    public function with(): array
    {
        $currencyEnum = CurrencyEnum::from($this->currency);

        if (! Schema::hasColumn('products', 'is_featured')) {
            return [
                'products' => collect(),
                'currencyEnum' => $currencyEnum,
            ];
        }

        $products = Product::query()
            ->publishedForStorefront($currencyEnum)
            ->where('is_featured', true)
            ->with([
                'category',
                'images' => fn ($q) => $q->orderByDesc('is_primary')->orderBy('sort_order'),
                'variants' => fn ($q) => $q->active()->withPriceIn($currencyEnum)->with([
                    'prices' => fn ($pq) => $pq->where('currency', $currencyEnum->value),
                ]),
            ])
            ->limit(8)
            ->get();

        return [
            'products' => $products,
            'currencyEnum' => $currencyEnum,
        ];
    }
};
