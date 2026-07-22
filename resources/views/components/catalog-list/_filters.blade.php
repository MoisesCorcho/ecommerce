{{-- Shared filter panel — included in desktop sidebar and mobile drawer --}}

@php
    $colorMap = [
        'negro'       => '#201b14',
        'black'       => '#201b14',
        'cognac'      => '#8B5A2B',
        'marrón'      => '#6B4226',
        'marron'      => '#6B4226',
        'brown'       => '#6B4226',
        'chocolate'   => '#372621',
        'cocoa'       => '#372621',
        'tan'         => '#D2B48C',
        'camel'       => '#C19A6B',
        'arena'       => '#D2B48C',
        'sand'        => '#D2B48C',
        'crema'       => '#FAF3E0',
        'cream'       => '#FAF3E0',
        'dorado'      => '#D2AE36',
        'gold'        => '#D2AE36',
        'dune'        => '#C2A67D',
        'oliva'       => '#6B6B3C',
        'olive'       => '#6B6B3C',
        'verde'       => '#5A6B3C',
        'green'       => '#5A6B3C',
        'azul'        => '#2C3E50',
        'blue'        => '#2C3E50',
        'navy'        => '#2C3E50',
        'burdeos'     => '#6B2D3E',
        'burgundy'    => '#6B2D3E',
        'wine'        => '#6B2D3E',
        'vino'        => '#6B2D3E',
        'rosa'        => '#D4A0A0',
        'pink'        => '#D4A0A0',
        'blush'       => '#D4A0A0',
        'rojo'        => '#8B3A3A',
        'red'         => '#8B3A3A',
        'gris'        => '#8B8B8B',
        'gray'        => '#8B8B8B',
        'grey'        => '#8B8B8B',
        'slate'       => '#6B7B8D',
        'piedra'      => '#9B9B8B',
        'stone'       => '#9B9B8B',
        'nude'        => '#D4B5A0',
        'camelot'     => '#A67B5B',
        'whisky'      => '#C5832A',
        'whiskey'     => '#C5832A',
    ];
@endphp

{{-- Category filter --}}
<div class="flex flex-col gap-stack-sm">
    <h3 class="border-b border-intense-cocoa/10 pb-2 text-label-caps font-semibold uppercase tracking-widest text-intense-cocoa">
        {{ __('storefront.shop.filter_category') }}
    </h3>
    <ul class="flex flex-col gap-3 text-body-md text-intense-cocoa/80">
        @foreach ($categories as $cat)
            <li>
                <label class="flex cursor-pointer items-center gap-3 transition-colors hover:text-soft-gold">
                    <input
                        type="checkbox"
                        wire:model.live="category"
                        value="{{ $cat['slug'] }}"
                        class="rounded border-intense-cocoa/20 text-soft-gold focus:ring-soft-gold"
                    >
                    {{ $cat['name'] }}
                    <span class="text-intense-cocoa/40">({{ $cat['count'] }})</span>
                </label>
            </li>
        @endforeach
    </ul>
</div>

{{-- Color filter --}}
@if ($colors !== [])
    <div class="flex flex-col gap-stack-sm">
        <h3 class="border-b border-intense-cocoa/10 pb-2 text-label-caps font-semibold uppercase tracking-widest text-intense-cocoa">
            {{ __('storefront.shop.filter_color') }}
        </h3>
        <div class="flex flex-wrap gap-3">
            @foreach ($colors as $colorName)
                @php
                    $hex = $colorMap[strtolower($colorName)] ?? '#8B8B8B';
                    $isSelected = in_array($colorName, $color, true);
                @endphp
                <button
                    type="button"
                    wire:click="toggleColor('{{ $colorName }}')"
                    aria-label="{{ $colorName }}"
                    title="{{ $colorName }}"
                    class="h-6 w-6 rounded-full border border-intense-cocoa/20 transition-all hover:ring-2 hover:ring-soft-gold hover:ring-offset-2 {{ $isSelected ? 'ring-2 ring-intense-cocoa ring-offset-2' : '' }}"
                    style="background-color: {{ $hex }}"
                ></button>
            @endforeach
        </div>
    </div>
@endif

{{-- Price filter --}}
@if ($globalMinPrice !== null && $globalMaxPrice !== null)
    <div class="flex flex-col gap-stack-sm">
        <h3 class="border-b border-intense-cocoa/10 pb-2 text-label-caps font-semibold uppercase tracking-widest text-intense-cocoa">
            {{ __('storefront.shop.filter_price') }}
        </h3>
        <div class="flex items-center gap-3 pt-2">
            <input
                type="number"
                wire:model.live.debounce.500ms="minPrice"
                placeholder="{{ number_format($globalMinPrice, 0, ',', '.') }}"
                min="{{ $globalMinPrice }}"
                max="{{ $globalMaxPrice }}"
                class="w-full border-b border-intense-cocoa/30 bg-transparent py-2 text-body-md text-intense-cocoa placeholder:text-intense-cocoa/30 focus:border-intense-cocoa focus:outline-none"
            >
            <span class="text-intense-cocoa/40">—</span>
            <input
                type="number"
                wire:model.live.debounce.500ms="maxPrice"
                placeholder="{{ number_format($globalMaxPrice, 0, ',', '.') }}"
                min="{{ $globalMinPrice }}"
                max="{{ $globalMaxPrice }}"
                class="w-full border-b border-intense-cocoa/30 bg-transparent py-2 text-body-md text-intense-cocoa placeholder:text-intense-cocoa/30 focus:border-intense-cocoa focus:outline-none"
            >
        </div>
        <p class="text-label-caps text-intense-cocoa/40">
            {{ $currencyEnum->value }}
        </p>
    </div>
@endif

{{-- Availability filter --}}
<div class="flex flex-col gap-stack-sm">
    <h3 class="border-b border-intense-cocoa/10 pb-2 text-label-caps font-semibold uppercase tracking-widest text-intense-cocoa">
        {{ __('storefront.shop.filter_availability') }}
    </h3>
    <label class="flex cursor-pointer items-center gap-3 text-body-md text-intense-cocoa/80 transition-colors hover:text-soft-gold">
        <input
            type="checkbox"
            wire:model.live="inStock"
            class="rounded border-intense-cocoa/20 text-soft-gold focus:ring-soft-gold"
        >
        {{ __('storefront.shop.filter_in_stock') }}
    </label>
</div>

{{-- Clear filters --}}
@if ($category !== [] || $color !== [] || $minPrice !== null || $maxPrice !== null || $inStock)
    <button
        type="button"
        wire:click="clearFilters"
        class="mt-2 w-full rounded border border-intense-cocoa py-3 text-label-caps font-semibold uppercase tracking-widest text-intense-cocoa transition-colors hover:bg-soft-sand"
    >
        {{ __('storefront.shop.clear_filters') }}
    </button>
@endif
