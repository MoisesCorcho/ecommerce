<?php

use App\Enums\Commerce\CurrencyEnum;
use App\Models\Product;
use App\Support\ColorMap;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component
{
    #[Locked]
    public Product $product;

    #[Locked]
    public string $currency;

    public function mount(Product $product, string $currency): void
    {
        $this->product = $product;
        $this->currency = $currency;
    }

    public function with(): array
    {
        $currencyEnum = CurrencyEnum::from($this->currency);
        $variant = $this->product->variants->first();

        return [
            'currencyEnum' => $currencyEnum,
            'primaryImage' => $this->product->primaryImage(),
            'variant' => $variant,
            'price' => $variant?->priceIn($currencyEnum),
            'detailUrl' => route('products.show', $this->product->slug),
            'isOutOfStock' => $this->product->isOutOfStock(),
            'availableColors' => $this->product->availableColors(),
        ];
    }
};
