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
        <div class="absolute top-4 right-4 flex flex-col gap-2 opacity-0 translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all duration-300">
            <button
                type="button"
                aria-label="{{ __('storefront.favorite_login_required') }}"
                class="flex h-10 w-10 items-center justify-center rounded-full bg-silk-cream/95 text-intense-cocoa shadow-sm transition-colors hover:bg-soft-gold hover:text-silk-cream"
                data-favorite-button
                data-product-id="{{ $product->id }}"
            >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-5 w-5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                </svg>
            </button>
            @if (! $isOutOfStock && $variant)
                <button
                    type="button"
                    wire:click="$dispatch('add-to-cart', { variantId: {{ $variant->id }} })"
                    aria-label="{{ __('storefront.add_to_cart') }}"
                    class="flex h-10 w-10 items-center justify-center rounded-full bg-silk-cream/95 text-intense-cocoa shadow-sm transition-colors hover:bg-soft-gold hover:text-silk-cream"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-5 w-5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.46 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM11.25 10.5h.008v.008h-.008V10.5Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                    </svg>
                </button>
            @endif
        </div>
    </div>

    {{-- Text content --}}
    <div class="flex flex-col gap-1 p-6 {{ $isOutOfStock ? 'opacity-60' : '' }}">
        @if ($product->category)
            <span class="text-[10px] font-semibold uppercase tracking-[0.2em] text-intense-cocoa/50">
                {{ $product->category->name }}
            </span>
        @endif

        <h3 class="font-headline-sm text-lg text-intense-cocoa">
            <a href="{{ $detailUrl }}">{{ $product->name }}</a>
        </h3>

        @if ($price)
            <p class="font-headline-sm text-xl text-soft-gold mt-1">
                {{ number_format($price->price, 0, ',', '.') }}
                <span class="text-label-caps font-normal text-intense-cocoa/60">{{ $currencyEnum->value }}</span>
            </p>
        @endif

        @if ($availableColors->count() > 1)
            <div class="flex items-center gap-1.5 mt-2">
                @foreach ($availableColors->take(5) as $colorName)
                    <span
                        class="h-2.5 w-2.5 rounded-full border border-intense-cocoa/10"
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
