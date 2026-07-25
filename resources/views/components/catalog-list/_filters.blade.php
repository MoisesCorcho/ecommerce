{{-- Shared filter panel — included in desktop sidebar and mobile drawer --}}

@php
    use App\Support\ColorMap;
@endphp

{{-- Category filter --}}
<div class="flex flex-col gap-stack-sm">
    <h3 class="border-b border-intense-cocoa/10 pb-2 mb-2 text-[11px] font-semibold uppercase tracking-[0.2em] text-intense-cocoa">
        {{ __('storefront.shop.filter_category') }}
    </h3>
    <ul class="flex flex-col gap-3 text-body-md text-intense-cocoa/80">
        @foreach ($categories as $cat)
            <li>
                <x-checkbox
                    align="center"
                    label-class=""
                    wrapper-class="cursor-pointer transition-colors hover:text-soft-gold"
                    wire:model.live="category"
                    value="{{ $cat['slug'] }}"
                >
                    {{ $cat['name'] }}
                    <span class="text-intense-cocoa/40">({{ $cat['count'] }})</span>
                </x-checkbox>
            </li>
        @endforeach
    </ul>
</div>

{{-- Color filter --}}
@if ($colors !== [])
    <div class="flex flex-col gap-stack-sm">
        <h3 class="border-b border-intense-cocoa/10 pb-2 mb-2 text-[11px] font-semibold uppercase tracking-[0.2em] text-intense-cocoa">
            {{ __('storefront.shop.filter_color') }}
        </h3>
        <div class="flex flex-wrap gap-3">
            @foreach ($colors as $colorName)
                @php
                    $hex = ColorMap::HEX[strtolower($colorName)] ?? '#8B8B8B';
                    $isSelected = in_array($colorName, $color, true);
                @endphp
                <button
                    type="button"
                    wire:click="toggleColor('{{ $colorName }}')"
                    aria-label="{{ $colorName }}"
                    title="{{ $colorName }}"
                    class="h-6 w-6 border border-intense-cocoa/20 transition-all hover:ring-2 hover:ring-soft-gold hover:ring-offset-2 {{ $isSelected ? 'ring-2 ring-intense-cocoa ring-offset-2' : '' }}"
                    style="background-color: {{ $hex }}"
                ></button>
            @endforeach
        </div>
    </div>
@endif

{{-- Price filter --}}
@if ($globalMinPrice !== null && $globalMaxPrice !== null)
    <div class="flex flex-col gap-stack-sm">
        <h3 class="border-b border-intense-cocoa/10 pb-2 mb-2 text-[11px] font-semibold uppercase tracking-[0.2em] text-intense-cocoa">
            {{ __('storefront.shop.filter_price') }}
        </h3>
        <div class="flex items-center gap-3 pt-2">
            <input
                type="number"
                wire:model.live.debounce.500ms="minPrice"
                placeholder="{{ number_format($globalMinPrice, 0, ',', '.') }}"
                min="{{ $globalMinPrice }}"
                max="{{ $globalMaxPrice }}"
                class="w-full border-b border-intense-cocoa/30 bg-transparent py-2 text-body-md text-intense-cocoa placeholder:text-intense-cocoa/60 focus:border-intense-cocoa focus:outline-none"
            >
            <span class="text-intense-cocoa/40">—</span>
            <input
                type="number"
                wire:model.live.debounce.500ms="maxPrice"
                placeholder="{{ number_format($globalMaxPrice, 0, ',', '.') }}"
                min="{{ $globalMinPrice }}"
                max="{{ $globalMaxPrice }}"
                class="w-full border-b border-intense-cocoa/30 bg-transparent py-2 text-body-md text-intense-cocoa placeholder:text-intense-cocoa/60 focus:border-intense-cocoa focus:outline-none"
            >
        </div>
        <p class="text-label-caps text-intense-cocoa/70">
            {{ $currencyEnum->value }}
        </p>
    </div>
@endif

{{-- Availability filter --}}
<div class="flex flex-col gap-stack-sm">
    <h3 class="border-b border-intense-cocoa/10 pb-2 mb-2 text-[11px] font-semibold uppercase tracking-[0.2em] text-intense-cocoa">
        {{ __('storefront.shop.filter_availability') }}
    </h3>
    <x-checkbox
        align="center"
        label-class="text-body-md"
        wrapper-class="cursor-pointer text-intense-cocoa/80 transition-colors hover:text-soft-gold"
        wire:model.live="inStock"
    >
        {{ __('storefront.shop.filter_in_stock') }}
    </x-checkbox>
</div>

{{-- Clear filters --}}
@if ($category !== [] || $color !== [] || $minPrice !== null || $maxPrice !== null || $inStock)
    <button
        type="button"
        wire:click="clearFilters"
        class="mt-2 w-full border border-intense-cocoa py-3 text-label-caps font-semibold uppercase tracking-widest text-intense-cocoa transition-colors hover:bg-intense-cocoa hover:text-silk-cream"
    >
        {{ __('storefront.shop.clear_filters') }}
    </button>
@endif
