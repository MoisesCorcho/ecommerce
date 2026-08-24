{{-- Shared filter panel — included in desktop sidebar and mobile drawer --}}

@php
    use App\Support\ColorMap;
@endphp

{{-- Category filter --}}
<div class="flex flex-col gap-stack-sm">
    <x-filter-heading>
        {{ __('storefront.shop.filter_category') }}
    </x-filter-heading>
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
        <x-filter-heading>
            {{ __('storefront.shop.filter_color') }}
        </x-filter-heading>
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

{{-- Size filter --}}
@if ($sizes !== [])
    <div class="flex flex-col gap-stack-sm">
        <x-filter-heading>
            {{ __('storefront.shop.filter_size') }}
        </x-filter-heading>
        <ul class="flex flex-col gap-3 text-body-md text-intense-cocoa/80">
            @foreach ($sizes as $sizeValue)
                @php
                    $sizeLabel = \App\Enums\Products\SizeEnum::tryFrom($sizeValue)?->label() ?? $sizeValue;
                @endphp
                <li>
                    <x-checkbox
                        align="center"
                        label-class=""
                        wrapper-class="cursor-pointer transition-colors hover:text-soft-gold"
                        wire:model.live="size"
                        value="{{ $sizeValue }}"
                    >
                        {{ $sizeLabel }}
                    </x-checkbox>
                </li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Price filter --}}
@if ($globalMinPrice !== null && $globalMaxPrice !== null)
    <div class="flex flex-col gap-stack-sm">
        <x-filter-heading>
            {{ __('storefront.shop.filter_price') }}
        </x-filter-heading>
        <div
            x-data="{
                floor: {{ $globalMinPrice }},
                ceil: {{ $globalMaxPrice }},
                minorUnits: {{ $currencyEnum->minorUnits() }},
                symbol: '{{ $currencyEnum->symbol() }}',
                decimals: {{ $currencyEnum->minorUnits() === 1 ? 0 : 2 }},
                step: {{ $currencyEnum->minorUnits() === 1 ? 1000 : 100 }},
                wireMin: $wire.entangle('minPrice').live,
                wireMax: $wire.entangle('maxPrice').live,
                localMin: {{ $globalMinPrice }},
                localMax: {{ $globalMaxPrice }},
                fmt(v) {
                    var val = Number(v) / this.minorUnits;
                    var formatted = new Intl.NumberFormat('es-CO', {
                        minimumFractionDigits: this.decimals,
                        maximumFractionDigits: this.decimals,
                    }).format(val);
                    return this.symbol + ' ' + formatted;
                },
                updateFill() {
                    var lo = parseInt(this.localMin);
                    var hi = parseInt(this.localMax);
                    var range = this.ceil - this.floor || 1;
                    var pctLo = Math.max(0, Math.min(100, ((lo - this.floor) / range) * 100));
                    var pctHi = Math.max(0, Math.min(100, ((hi - this.floor) / range) * 100));
                    if (this.$refs.fill) {
                        this.$refs.fill.style.left = pctLo + '%';
                        this.$refs.fill.style.width = Math.max(0, pctHi - pctLo) + '%';
                    }
                },
                init() {
                    this.resetLocalFromWire();
                    this.$watch('wireMin', () => this.resetLocalFromWire());
                    this.$watch('wireMax', () => this.resetLocalFromWire());
                },
                resetLocalFromWire() {
                    this.localMin = (this.wireMin !== null && this.wireMin !== undefined) ? parseInt(this.wireMin) : this.floor;
                    this.localMax = (this.wireMax !== null && this.wireMax !== undefined) ? parseInt(this.wireMax) : this.ceil;
                    this.updateFill();
                },
                onMinInput(val) {
                    var v = parseInt(val);
                    if (v > parseInt(this.localMax)) {
                        v = parseInt(this.localMax);
                    }
                    this.localMin = v;
                    this.updateFill();
                },
                onMaxInput(val) {
                    var v = parseInt(val);
                    if (v < parseInt(this.localMin)) {
                        v = parseInt(this.localMin);
                    }
                    this.localMax = v;
                    this.updateFill();
                },
                sync() {
                    var lo = parseInt(this.localMin);
                    var hi = parseInt(this.localMax);
                    var targetMin = (lo === this.floor) ? null : lo;
                    var targetMax = (hi === this.ceil) ? null : hi;

                    $wire.setPriceFilter(targetMin, targetMax);
                }
            }"
            class="price-slider pt-1"
        >
            <div class="price-slider__track">
                <div class="price-slider__bg-track"></div>
                <div x-ref="fill" class="price-slider__fill"></div>
                <input
                    type="range"
                    class="price-slider__input price-slider__input--min"
                    :min="floor"
                    :max="ceil"
                    :step="step"
                    :value="localMin"
                    @input="onMinInput($event.target.value)"
                    @change="sync()"
                >
                <input
                    type="range"
                    class="price-slider__input price-slider__input--max"
                    :min="floor"
                    :max="ceil"
                    :step="step"
                    :value="localMax"
                    @input="onMaxInput($event.target.value)"
                    @change="sync()"
                >
            </div>
            <div class="price-slider__labels">
                <span class="price-slider__label price-slider__label--min" x-text="fmt(localMin)">{{ $currencyEnum->format($globalMinPrice) }}</span>
                <span class="price-slider__label price-slider__label--max" x-text="fmt(localMax)">{{ $currencyEnum->format($globalMaxPrice) }}</span>
            </div>
            <p class="text-center text-[11px] font-semibold uppercase tracking-widest text-intense-cocoa/40">
                {{ $currencyEnum->value }}
            </p>
        </div>
    </div>
@endif

{{-- Availability filter --}}
<div class="flex flex-col gap-stack-sm">
    <x-filter-heading>
        {{ __('storefront.shop.filter_availability') }}
    </x-filter-heading>
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
@if ($category !== [] || $color !== [] || $size !== [] || $minPrice !== null || $maxPrice !== null || $inStock)
    <x-secondary-button
        type="button"
        wire:click="clearFilters"
        class="mt-2 w-full h-10"
    >
        {{ __('storefront.shop.clear_filters') }}
    </x-secondary-button>
@endif
