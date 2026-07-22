<article class="group">
    <div class="relative">
        <a href="{{ $detailUrl }}" class="block overflow-hidden">
            <div class="aspect-[4/5] overflow-hidden rounded-none bg-soft-sand">
                @if ($primaryImage)
                    <img
                        src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($primaryImage->path) }}"
                        alt="{{ $product->name }}"
                        class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105 {{ $isOutOfStock ? 'opacity-60' : '' }}"
                    >
                @else
                    <div class="flex h-full w-full items-center justify-center">
                        <span class="text-label-caps text-intense-cocoa/40">{{ __('storefront.no_image') }}</span>
                    </div>
                @endif

                @if ($isOutOfStock)
                    <div class="absolute inset-0 bg-silk-cream/40 backdrop-blur-[1px] flex items-center justify-center">
                        <span class="rounded bg-soft-gold px-3 py-1 font-chillax text-label-caps font-semibold uppercase tracking-widest text-intense-cocoa">
                            {{ __('storefront.out_of_stock') }}
                        </span>
                    </div>
                @endif
            </div>
        </a>

        <div class="absolute top-3 right-3 z-10">
            <livewire:favorite-button :product-id="$product->id" wire:key="fb-{{ $product->id }}" />
        </div>
    </div>

    <div class="mt-4 space-y-1 {{ $isOutOfStock ? 'opacity-60' : '' }}">
        @if ($product->category)
            <p class="text-label-caps font-semibold uppercase tracking-wider text-intense-cocoa/60">
                {{ $product->category->name }}
            </p>
        @endif

        <h3 class="text-body-md font-medium text-intense-cocoa">
            <a href="{{ $detailUrl }}">{{ $product->name }}</a>
        </h3>

        @if ($price)
            <p class="text-headline-sm font-semibold tabular-nums text-intense-cocoa">
                {{ number_format($price->price, 0, ',', '.') }}
                <span class="text-label-caps font-normal text-intense-cocoa/60">{{ $currencyEnum->value }}</span>
            </p>
        @endif

        @if ($availableColors->count() > 1)
            <div class="flex items-center gap-1.5 mt-1">
                @foreach ($availableColors->take(5) as $colorName)
                    <span
                        class="h-2.5 w-2.5 rounded-full border border-intense-cocoa/20"
                        style="background-color: {{ ColorMap::HEX[strtolower($colorName)] ?? '#8B8B8B' }}"
                        title="{{ $colorName }}"
                    ></span>
                @endforeach
                @if ($availableColors->count() > 5)
                    <span class="text-label-caps text-intense-cocoa/40">+{{ $availableColors->count() - 5 }}</span>
                @endif
            </div>
        @endif
    </div>

    @if (! $isOutOfStock && $variant)
        <livewire:add-to-cart-button :product-variant-id="$variant->id" wire:key="atcb-{{ $variant->id }}" />
    @endif
</article>
