<?php

use App\Enums\Commerce\CurrencyEnum;
use App\Models\Product;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Catálogo')] class extends Component
{
    use WithPagination;

    public string $currency;

    public function mount(): void
    {
        $code = (string) config('ecommerce.default_currency', CurrencyEnum::Cop->value);

        $this->currency = CurrencyEnum::tryFrom($code)?->value ?? CurrencyEnum::Cop->value;
    }

    public function with(): array
    {
        $currency = CurrencyEnum::from($this->currency);

        $products = Product::query()
            ->publishedForStorefront($currency)
            ->with([
                'category',
                'images',
                'variants' => fn ($q) => $q->active()->withPriceIn($currency)->with(['prices' => fn ($pq) => $pq->where('currency', $currency->value)]),
            ])
            ->orderBy('name')
            ->paginate(12);

        return [
            'products' => $products,
            'currencyEnum' => $currency,
        ];
    }
};
