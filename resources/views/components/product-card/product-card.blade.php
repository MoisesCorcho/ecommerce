<article class="group">
    <a href="{{ $detailUrl }}" class="block overflow-hidden">
        <div class="aspect-[4/5] overflow-hidden rounded-none bg-soft-sand">
            @if ($primaryImage)
                <img
                    src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($primaryImage->path) }}"
                    alt="{{ $product->name }}"
                    class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                >
            @else
                <div class="flex h-full w-full items-center justify-center">
                    <span class="text-label-caps text-intense-cocoa/40">{{ __('storefront.no_image') }}</span>
                </div>
            @endif
        </div>
    </a>

    <div class="mt-4 space-y-1">
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
    </div>

    @if ($variant)
        <livewire:add-to-cart-button :product-variant-id="$variant->id" wire:key="atcb-{{ $variant->id }}" />
    @endif

    {{-- favorite-button: integrated in Slice 4 --}}
</article>
