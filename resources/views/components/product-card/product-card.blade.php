@php
    use App\Support\ColorMap;
@endphp

<article class="group relative flex flex-col cursor-pointer bg-surface-container transition-shadow duration-300 ease-out hover:shadow-ambient">
    {{-- Image --}}
    <div class="relative w-full aspect-[4/5] bg-surface-container overflow-hidden mb-2">
        <a href="{{ $detailUrl }}" class="block h-full">
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

            {{-- Out-of-stock overlay --}}
            @if ($isOutOfStock)
                <div class="absolute inset-0 bg-silk-cream/40 backdrop-blur-[1px] flex items-center justify-center z-10">
                    <span class="bg-soft-gold px-5 py-2.5 text-label-caps font-semibold uppercase tracking-widest text-intense-cocoa">
                        {{ __('storefront.out_of_stock') }}
                    </span>
                </div>
            @endif
        </a>

        {{-- Hover actions (Heart + Cart) --}}
        <div class="absolute top-4 right-4 z-20 flex flex-col gap-2 opacity-0 translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all duration-300">
            @if ($variant)
                <livewire:favorite-button :product-variant-id="$variant->id" wire:key="favorite-{{ $variant->id }}" />
            @endif
            @if (! $isOutOfStock && $variant)
                <button
                    type="button"
                    wire:click="$dispatch('add-to-cart', { variantId: {{ $variant->id }} })"
                    aria-label="{{ __('storefront.add_to_cart') }}"
                    class="flex h-10 w-10 cursor-pointer items-center justify-center bg-soft-sand text-intense-cocoa shadow-sm transition-colors hover:bg-soft-gold hover:text-intense-cocoa"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-5 w-5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.46 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM11.25 10.5h.008v.008h-.008V10.5Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                    </svg>
                </button>
            @endif
        </div>
    </div>

    {{-- Text content --}}
    <div class="flex flex-col gap-1.5 px-6 pb-6 pt-4 {{ $isOutOfStock ? 'opacity-60' : '' }}">
        @if ($product->category)
            <span class="text-xs font-semibold uppercase tracking-wider text-intense-cocoa/50">
                {{ $product->category->name }}
            </span>
        @endif

        <h3 class="font-headline-sm text-xl text-intense-cocoa">
            <a href="{{ $detailUrl }}">{{ $product->name }}</a>
        </h3>

        @if ($price)
            <p class="font-headline-sm text-2xl text-soft-gold">
                {{ number_format($price->price, 0, ',', '.') }}
                <span class="text-sm font-normal text-intense-cocoa/60">{{ $currencyEnum->value }}</span>
            </p>
        @endif

        @if ($availableColors->count() > 1)
            <div class="flex items-center gap-1.5 mt-2">
                @foreach ($availableColors->take(5) as $colorName)
                    <span
                        class="h-3 w-3 border border-intense-cocoa/10"
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
</article>
