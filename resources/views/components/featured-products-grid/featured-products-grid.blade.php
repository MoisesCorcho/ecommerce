<div>
    @if ($products->isNotEmpty())
        <section class="py-16 lg:py-24" aria-labelledby="featured-heading">
            <div class="mb-8 flex items-end justify-between">
                <h2 id="featured-heading" class="font-chillax text-headline-md text-intense-cocoa">
                    {{ __('storefront.home.featured') }}
                </h2>
                <a
                    href="{{ route('products.index') }}"
                    class="text-label-caps font-semibold uppercase tracking-wider text-intense-cocoa underline underline-offset-4 transition-colors hover:text-soft-gold"
                >
                    {{ __('storefront.home.view_all') }}
                </a>
            </div>

            <div class="grid grid-cols-1 gap-gutter sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($products as $product)
                    <livewire:product-card :product="$product" :currency="$currencyEnum->value" wire:key="pc-{{ $product->id }}" />
                @endforeach
            </div>
        </section>
    @endif
</div>
