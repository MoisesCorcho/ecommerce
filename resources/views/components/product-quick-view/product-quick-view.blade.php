@php
    use App\Support\ColorMap;
@endphp

<div>
    @if ($showModal && $product)
        <div
            x-data="{ show: @js($showModal) }"
            x-show="show"
            x-cloak
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            x-on:keydown.escape.window="$wire.closeModal()"
            class="fixed inset-0 z-50 flex items-center justify-center bg-intense-cocoa/60 p-4 backdrop-blur-sm sm:p-6 lg:p-8"
            role="dialog"
            aria-modal="true"
            aria-labelledby="quick-view-title"
        >
            {{-- Modal Backdrop Click --}}
            <div class="fixed inset-0" wire:click="closeModal"></div>

            {{-- Modal Box --}}
            <div
                class="relative z-10 my-auto flex max-h-[90vh] w-full max-w-4xl flex-col overflow-y-auto border border-soft-sand bg-silk-cream p-6 shadow-2xl sm:p-8"
                x-on:click.outside="$wire.closeModal()"
            >
                {{-- Close Button --}}
                <button
                    type="button"
                    wire:click="closeModal"
                    aria-label="{{ __('storefront.products.close_lightbox') }}"
                    class="absolute top-4 right-4 z-20 flex h-9 w-9 items-center justify-center rounded-full bg-soft-sand/80 text-intense-cocoa transition-colors hover:bg-soft-gold hover:text-intense-cocoa focus:outline-none"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>

                {{-- Two-column Grid Layout --}}
                <div class="grid grid-cols-1 gap-8 md:grid-cols-2 md:gap-10">
                    {{-- LEFT: Image Gallery --}}
                    <div class="flex flex-col gap-4">
                        <div class="group relative flex aspect-[4/5] w-full items-center justify-center overflow-hidden bg-soft-sand">
                            @if ($product->images->count() > 0)
                                @php
                                    $currentImage = $product->images->get($mainImageIndex) ?? $product->images->first();
                                @endphp
                                <img
                                    src="/storage/{{ $currentImage->path }}"
                                    alt="{{ $product->name }}"
                                    class="h-full w-full object-cover transition-transform duration-500"
                                    wire:key="qv-img-{{ $mainImageIndex }}"
                                >
                            @else
                                <div class="flex aspect-[4/5] items-center justify-center bg-soft-sand text-intense-cocoa/40">
                                    <span class="text-label-caps">{{ __('storefront.no_image') }}</span>
                                </div>
                            @endif

                            @if ($product->is_preorder)
                                <div class="absolute top-3 left-3 z-10">
                                    <span class="bg-intense-cocoa px-3 py-1 text-xs font-semibold uppercase tracking-widest text-silk-cream">
                                        {{ __('storefront.products.preorder_badge') }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        {{-- Thumbnails Row --}}
                        @if ($product->images->count() > 1)
                            <div class="flex gap-2 overflow-x-auto pb-1" role="listbox" aria-label="Product images">
                                @foreach ($product->images as $index => $image)
                                    <button
                                        type="button"
                                        wire:click="$set('mainImageIndex', {{ $index }})"
                                        role="option"
                                        aria-selected="{{ $mainImageIndex === $index ? 'true' : 'false' }}"
                                        aria-label="Image {{ $loop->iteration }}"
                                        class="group/thumbnail relative flex-shrink-0 border-2 transition-all duration-200 {{ $mainImageIndex === $index ? 'border-intense-cocoa' : 'border-transparent hover:border-intense-cocoa/30' }}"
                                    >
                                        <img
                                            src="/storage/{{ $image->path }}"
                                            alt="{{ $product->name }} — {{ $loop->iteration }}"
                                            class="h-16 w-16 object-cover"
                                        >
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- RIGHT: Details & Actions --}}
                    <div class="flex flex-col gap-5">
                        {{-- Category & Title --}}
                        <div>
                            @if ($product->category)
                                <p class="mb-1 text-label-caps uppercase tracking-[0.2em] text-intense-cocoa/50">
                                    {{ $product->category->name }}
                                </p>
                            @endif
                            <h2 id="quick-view-title" class="font-chillax text-2xl font-semibold tracking-tight text-intense-cocoa sm:text-3xl">
                                {{ $product->name }}
                            </h2>
                        </div>

                        {{-- Price & Stock Status --}}
                        <div class="flex flex-wrap items-center gap-3">
                            @if ($selectedVariant && $selectedVariant->priceIn($currencyEnum))
                                @php
                                    $price = $selectedVariant->priceIn($currencyEnum);
                                @endphp
                                <span class="font-sans text-2xl font-semibold tabular-nums text-intense-cocoa">
                                    {{ $currencyEnum->format($price->price) }}
                                </span>
                            @endif

                            @if ($selectedVariant && $selectedVariant->stock <= 0 && ! $product->is_preorder)
                                <span class="inline-flex items-center justify-center bg-soft-gold px-2.5 py-1 text-label-caps font-semibold uppercase tracking-wider text-intense-cocoa">
                                    {{ __('storefront.out_of_stock') }}
                                </span>
                            @endif
                        </div>

                        {{-- Description --}}
                        @if ($product->description)
                            <p class="text-body-md text-intense-cocoa/80 line-clamp-3">
                                {{ Str::limit($product->description, 180) }}
                            </p>
                        @endif

                        {{-- Color Selector --}}
                        @if ($availableColors->count() > 0)
                            <div>
                                <p class="mb-2 text-sm font-medium text-intense-cocoa">
                                    {{ __('storefront.products.color_label') }}:
                                    <span class="font-normal text-intense-cocoa/60">{{ $selectedColor }}</span>
                                </p>
                                <div class="flex flex-wrap gap-2" role="radiogroup" aria-label="{{ __('storefront.products.color_label') }}">
                                    @foreach ($availableColors as $colorName)
                                        @php
                                            $hex = ColorMap::HEX[strtolower($colorName)] ?? '#8B8B8B';
                                            $isSelected = $selectedColor === $colorName;
                                        @endphp
                                        <button
                                            type="button"
                                            wire:click="$set('selectedColor', '{{ $colorName }}')"
                                            role="radio"
                                            aria-checked="{{ $isSelected ? 'true' : 'false' }}"
                                            aria-label="{{ $colorName }}"
                                            title="{{ $colorName }}"
                                            class="relative h-8 w-8 border border-intense-cocoa/20 transition-all hover:ring-2 hover:ring-soft-gold focus:outline-none {{ $isSelected ? 'border-intense-cocoa ring-2 ring-intense-cocoa ring-offset-2' : '' }}"
                                            style="background-color: {{ $hex }}"
                                        ></button>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Size Selector --}}
                        @if ($availableSizes->count() > 0)
                            <div>
                                <p class="mb-2 text-sm font-medium text-intense-cocoa">
                                    {{ __('storefront.products.size_label') }}:
                                    <span class="font-normal text-intense-cocoa/60">{{ $selectedSize }}</span>
                                </p>
                                <div class="flex flex-wrap gap-2" role="radiogroup" aria-label="{{ __('storefront.products.size_label') }}">
                                    @foreach ($availableSizes as $sizeName)
                                        @php
                                            $isSelected = $selectedSize === $sizeName;
                                        @endphp
                                        <button
                                            type="button"
                                            wire:click="$set('selectedSize', '{{ $sizeName }}')"
                                            role="radio"
                                            aria-checked="{{ $isSelected ? 'true' : 'false' }}"
                                            class="min-w-[40px] border px-3 py-1.5 text-xs font-semibold uppercase tracking-wider transition-all focus:outline-none {{ $isSelected ? 'border-intense-cocoa bg-intense-cocoa text-silk-cream' : 'border-intense-cocoa/20 bg-transparent text-intense-cocoa hover:border-intense-cocoa' }}"
                                        >
                                            {{ $sizeName }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Errors / Status Messages --}}
                        @if ($errorMessage)
                            <div class="border border-red-200 bg-red-50 p-3 text-xs text-red-700">
                                {{ $errorMessage }}
                            </div>
                        @endif

                        {{-- Quantity & Actions --}}
                        <div class="mt-2 flex flex-col gap-3">
                            <div class="flex items-center gap-3">
                                {{-- Quantity Counter --}}
                                <div class="flex items-center border border-intense-cocoa/20 bg-soft-sand">
                                    <button
                                        type="button"
                                        wire:click="$set('quantity', {{ max(1, $quantity - 1) }})"
                                        class="px-3 py-2 text-intense-cocoa transition-colors hover:bg-soft-gold"
                                        aria-label="Decrease quantity"
                                    >
                                        -
                                    </button>
                                    <span class="w-10 text-center font-sans text-sm font-semibold text-intense-cocoa">
                                        {{ $quantity }}
                                    </span>
                                    <button
                                        type="button"
                                        wire:click="$set('quantity', {{ min(99, $quantity + 1) }})"
                                        class="px-3 py-2 text-intense-cocoa transition-colors hover:bg-soft-gold"
                                        aria-label="Increase quantity"
                                    >
                                        +
                                    </button>
                                </div>

                                {{-- Wishlist Favorite Button --}}
                                <button
                                    type="button"
                                    wire:click="toggleFavorite"
                                    aria-label="{{ __('storefront.products.add_to_favorites_label') }}"
                                    class="flex h-10 w-10 items-center justify-center border border-intense-cocoa/20 bg-soft-sand text-intense-cocoa transition-colors hover:bg-soft-gold"
                                >
                                    <svg class="h-5 w-5 {{ $isFavorited ? 'fill-intense-cocoa text-intense-cocoa' : 'fill-none text-intense-cocoa' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                                    </svg>
                                </button>
                            </div>

                            {{-- Add to Cart CTA --}}
                            @php
                                $isDisabled = ! $selectedVariant || ($selectedVariant->stock <= 0 && ! $product->is_preorder);
                            @endphp

                            <button
                                type="button"
                                wire:click="addToCart"
                                wire:loading.attr="disabled"
                                @if ($isDisabled) disabled @endif
                                class="flex w-full items-center justify-center bg-intense-cocoa px-6 py-3.5 text-label-caps font-semibold uppercase tracking-widest text-silk-cream transition-all hover:bg-intense-cocoa/90 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <span wire:loading.remove wire:target="addToCart">
                                    @if ($product->is_preorder)
                                        {{ __('storefront.products.add_to_cart_preorder') }}
                                    @else
                                        {{ __('storefront.products.add_to_cart') }}
                                    @endif
                                </span>
                                <span wire:loading wire:target="addToCart" class="inline-flex items-center gap-2">
                                    <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ __('storefront.adding_to_cart') }}
                                </span>
                            </button>

                            {{-- View full details link --}}
                            <div class="mt-2 text-center">
                                <a
                                    href="{{ route('products.show', $product->slug) }}"
                                    class="text-xs font-semibold uppercase tracking-wider text-intense-cocoa/70 underline underline-offset-4 transition-colors hover:text-soft-gold"
                                >
                                    {{ __('storefront.products.view_full_details') }} &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
