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
    @php
        $pMin = $minPrice ?? $globalMinPrice;
        $pMax = $maxPrice ?? $globalMaxPrice;
    @endphp
    <div class="flex flex-col gap-stack-sm">
        <h3 class="border-b border-intense-cocoa/10 pb-2 mb-2 text-[11px] font-semibold uppercase tracking-[0.2em] text-intense-cocoa">
            {{ __('storefront.shop.filter_price') }}
        </h3>
        <div class="price-slider pt-1" data-min="{{ $globalMinPrice }}" data-max="{{ $globalMaxPrice }}" data-current-min="{{ $pMin }}" data-current-max="{{ $pMax }}">
            <div class="price-slider__track">
                <div class="price-slider__fill"></div>
                <input type="range" class="price-slider__input price-slider__input--min" min="{{ $globalMinPrice }}" max="{{ $globalMaxPrice }}" value="{{ $pMin }}">
                <input type="range" class="price-slider__input price-slider__input--max" min="{{ $globalMinPrice }}" max="{{ $globalMaxPrice }}" value="{{ $pMax }}">
            </div>
            <div class="price-slider__labels">
                <span class="price-slider__label price-slider__label--min">{{ $currencyEnum->format($pMin) }}</span>
                <span class="price-slider__label price-slider__label--max">{{ $currencyEnum->format($pMax) }}</span>
            </div>
            <p class="text-center text-[11px] font-semibold uppercase tracking-widest text-intense-cocoa/40">
                {{ $currencyEnum->value }}
            </p>
        </div>
    </div>

    @once
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function initPriceSliders() {
                document.querySelectorAll('.price-slider').forEach(function (el) {
                    if (el._initialized) return;
                    el._initialized = true;

                    var floor = parseInt(el.dataset.min);
                    var ceil = parseInt(el.dataset.max);
                    var minInput = el.querySelector('.price-slider__input--min');
                    var maxInput = el.querySelector('.price-slider__input--max');
                    var fill = el.querySelector('.price-slider__fill');
                    var labelMin = el.querySelector('.price-slider__label--min');
                    var labelMax = el.querySelector('.price-slider__label--max');

                    function fmt(v) {
                        return new Intl.NumberFormat('de-DE').format(v);
                    }

                    function render() {
                        var lo = parseInt(minInput.value);
                        var hi = parseInt(maxInput.value);
                        var range = ceil - floor || 1;
                        var pctLo = ((lo - floor) / range) * 100;
                        var pctHi = ((hi - floor) / range) * 100;
                        fill.style.left = pctLo + '%';
                        fill.style.width = (pctHi - pctLo) + '%';
                        labelMin.textContent = fmt(lo);
                        labelMax.textContent = fmt(hi);
                    }

                    function sync() {
                        var lo = parseInt(minInput.value);
                        var hi = parseInt(maxInput.value);
                        try {
                            var id = el.closest('[wire\\:id]').getAttribute('wire:id');
                            var comp = Livewire.find(id);
                            comp.set('minPrice', lo === floor ? null : lo);
                            comp.set('maxPrice', hi === ceil ? null : hi);
                        } catch (e) {}
                    }

                    minInput.addEventListener('input', function () {
                        var lo = parseInt(minInput.value);
                        var hi = parseInt(maxInput.value);
                        if (lo > hi) minInput.value = hi;
                        render();
                    });

                    maxInput.addEventListener('input', function () {
                        var lo = parseInt(minInput.value);
                        var hi = parseInt(maxInput.value);
                        if (hi < lo) maxInput.value = lo;
                        render();
                    });

                    minInput.addEventListener('change', sync);
                    maxInput.addEventListener('change', sync);

                    render();
                });
            }

            initPriceSliders();
            document.addEventListener('livewire:load', initPriceSliders);
            document.addEventListener('livewire:navigated', initPriceSliders);
        });
    </script>
    @endonce
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
    <x-secondary-button
        type="button"
        wire:click="clearFilters"
        class="mt-2 w-full h-10"
    >
        {{ __('storefront.shop.clear_filters') }}
    </x-secondary-button>
@endif
