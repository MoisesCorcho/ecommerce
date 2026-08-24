@php
    use App\Support\ColorMap;
@endphp

<div
    x-data="{ show: @js($showModal) }"
    x-effect="document.body.classList.toggle('overflow-hidden', @js($showModal))"
>
    @if ($showModal && $product)
        <div
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
                class="relative z-10 my-auto flex max-h-[94vh] w-full max-w-4xl short:max-h-[97vh] flex-col overflow-hidden border border-soft-sand bg-silk-cream p-5 shadow-2xl sm:p-6"
                x-on:click.outside="$wire.closeModal()"
            >
                {{-- Close Button (44px WCAG Touch Target) --}}
                <button
                    type="button"
                    wire:click="closeModal"
                    dusk="quick-view-close"
                    aria-label="{{ __('storefront.shop.close_filters') }}"
                    class="absolute top-3 right-3 z-20 flex h-9 w-9 cursor-pointer items-center justify-center bg-intense-cocoa text-silk-cream transition-colors hover:bg-error hover:text-white focus:outline-none"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>

                {{-- Two-column Grid Layout (Matching Product Detail 1-to-1) --}}
                {{-- Never the modal box: the close button is positioned against
                     it and would scroll out of reach. In one column the whole
                     body scrolls; in two, only the details do, so the photo
                     stays put instead of sliding away with the copy. --}}
                <div dusk="quick-view-scroll" class="no-scrollbar grid min-h-0 grid-cols-1 gap-6 overflow-y-auto md:grid-cols-2 md:gap-8 md:overflow-hidden md:[grid-template-rows:minmax(0,1fr)]">
                    {{-- LEFT: Image Gallery --}}
                    <div class="flex flex-col gap-3 md:min-h-0">
                        <div class="group relative flex aspect-[4/5] w-full items-center justify-center overflow-hidden bg-soft-sand md:aspect-auto md:min-h-0 md:flex-1">
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
                                    <span class="bg-intense-cocoa px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-widest text-silk-cream">
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
                                            class="h-12 w-12 object-cover"
                                        >
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- RIGHT: Details & Actions (Matching Product Detail 1-to-1) --}}
                    <div dusk="quick-view-details" class="quick-view-details no-scrollbar flex flex-col gap-3.5 md:-mx-1.5 md:min-h-0 md:gap-2 md:overflow-y-auto md:px-1.5 short:gap-1">
                        {{-- Category & Title --}}
                        <div>
                            @if ($product->category)
                                <p class="mb-1 text-label-caps uppercase tracking-[0.2em] text-intense-cocoa/50">
                                    {{ $product->category->name }}
                                </p>
                            @endif
                            <h2 id="quick-view-title" class="font-chillax text-2xl font-semibold tracking-tight text-intense-cocoa sm:text-3xl short:text-xl">
                                {{ $product->name }}
                            </h2>
                        </div>

                        {{-- Price & Stock Status --}}
                        <div class="flex flex-wrap items-center gap-3">
                            @if ($selectedVariant && $selectedVariant->priceIn($currencyEnum))
                                @php
                                    $price = $selectedVariant->priceIn($currencyEnum);
                                @endphp
                                <span class="font-sans text-2xl font-semibold tabular-nums text-intense-cocoa short:text-xl">
                                    {{ $currencyEnum->format($price->price) }}
                                </span>
                            @endif

                            @if ($selectedVariant && $selectedVariant->stock <= 0 && ! $product->is_preorder)
                                <span class="inline-flex items-center justify-center bg-soft-gold px-2.5 py-1 text-label-caps font-semibold uppercase tracking-wider text-intense-cocoa">
                                    {{ __('storefront.out_of_stock') }}
                                </span>
                            @endif
                        </div>

                        {{-- Stock status text --}}
                        @if ($selectedVariant)
                            @if ($selectedVariant->stock > 0 && $selectedVariant->stock <= 5)
                                <p class="text-sm text-intense-cocoa/70">
                                    {{ $selectedVariant->stock === 1 ? __('storefront.products.stock_low_one') : __('storefront.products.stock_low', ['count' => $selectedVariant->stock]) }}
                                </p>
                            @elseif ($selectedVariant->stock > 5)
                                <p class="text-sm text-intense-cocoa/70">
                                    {{ __('storefront.products.stock_available', ['count' => $selectedVariant->stock]) }}
                                </p>
                            @endif
                        @endif

                        {{-- Brief Description --}}
                        @if ($product->description)
                            <p class="text-body-md text-intense-cocoa/80 line-clamp-2">
                                {{ Str::limit($product->description, 140) }}
                            </p>
                        @endif

                        {{-- Variant Specifications (Matching Product Detail 1-to-1) --}}
                        @if ($availableColors->count() > 0 || $availableSizes->count() > 0)
                            <div class="flex flex-col gap-3">
                                {{-- Color Selector --}}
                                @if ($availableColors->count() > 0)
                                    <div>
                                        <p class="mb-1.5 text-sm font-medium text-intense-cocoa">
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
                                                    wire:loading.attr="disabled"
                                                    role="radio"
                                                    aria-checked="{{ $isSelected ? 'true' : 'false' }}"
                                                    aria-label="{{ $colorName }}"
                                                    title="{{ $colorName }}"
                                                    class="relative h-9 w-9 border border-intense-cocoa/20 short:h-8 short:w-8 transition-all hover:ring-2 hover:ring-soft-gold hover:ring-offset-2 focus:outline-none disabled:opacity-50 {{ $isSelected ? 'border-intense-cocoa ring-2 ring-intense-cocoa ring-offset-2' : '' }}"
                                                    style="background-color: {{ $hex }}"
                                                >
                                                    @if ($isSelected)
                                                        <span class="absolute inset-0 flex items-center justify-center">
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4 text-soft-gold">
                                                                <path fill-rule="evenodd" d="M19.916 4.626a.75.75 0 0 1 .208 1.04l-9 13.5a.75.75 0 0 1-1.154.114l-6-6a.75.75 0 0 1 1.06-1.06l5.353 5.353 8.493-12.74a.75.75 0 0 1 1.04-.207Z" clip-rule="evenodd" />
                                                            </svg>
                                                        </span>
                                                    @endif
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                {{-- Size Selector --}}
                                @if ($availableSizes->count() > 0)
                                    <div>
                                        <p class="mb-1.5 text-sm font-medium text-intense-cocoa">
                                            {{ __('storefront.products.size_label') }}
                                        </p>
                                        <div class="flex flex-wrap gap-2" role="radiogroup" aria-label="{{ __('storefront.products.size_label') }}">
                                            @foreach ($availableSizes as $sizeName)
                                                @php
                                                    $isSelected = $selectedSize === $sizeName;
                                                @endphp
                                                <button
                                                    type="button"
                                                    wire:click="$set('selectedSize', '{{ $sizeName }}')"
                                                    wire:loading.attr="disabled"
                                                    role="radio"
                                                    aria-checked="{{ $isSelected ? 'true' : 'false' }}"
                                                    class="min-h-[38px] min-w-[38px] border px-3 py-1.5 text-sm short:min-h-[32px] short:py-1 font-medium transition-all duration-200 focus:outline-none disabled:opacity-50 {{ $isSelected ? 'border-intense-cocoa bg-intense-cocoa text-silk-cream' : 'border-transparent bg-soft-sand text-intense-cocoa hover:border-intense-cocoa' }}"
                                                >
                                                    {{ \App\Enums\Products\SizeEnum::tryFrom($sizeName)?->label() ?? $sizeName }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- Select Variant / Stock Hints --}}
                        @if (! $selectedVariant)
                            <p class="bg-soft-sand px-4 py-2.5 text-body-md text-intense-cocoa/80 leading-relaxed">
                                {{ __('storefront.products.select_variant') }}
                            </p>
                        @elseif ($selectedVariant->stock > 0 && $availableStock <= 0)
                            <p class="bg-soft-gold/20 px-4 py-2.5 text-body-md text-intense-cocoa/80 leading-relaxed short:px-3 short:py-1.5 short:text-xs">
                                {{ __('storefront.products.stock_in_cart') }}
                            </p>
                        @endif

                        {{-- Errors / Status Messages (Brand Error Token) --}}
                        @if ($errorMessage)
                            <div class="border border-error/30 bg-error/10 p-3 text-xs font-medium text-error" role="alert">
                                {{ $errorMessage }}
                            </div>
                        @endif

                        {{-- Quantity Selector (Matching Product Detail) --}}
                        @if ($selectedVariant && $selectedVariant->stock > 0)
                            <div class="flex flex-col items-start">
                                {{-- On a short screen the badge sits beside the label instead of
                                     below it: stacked it costs about thirty-four pixels, and it
                                     only appears after adding to the cart, which made the modal
                                     grow past the viewport at the worst possible moment. --}}
                                <div class="flex flex-col items-start short:mb-2 short:flex-row short:items-center short:gap-2">
                                <label for="qv-product-qty" class="mb-2 block text-sm font-medium text-intense-cocoa short:mb-0">
                                    {{ __('storefront.products.quantity_label') }}
                                </label>

                                @if ($cartQuantity > 0)
                                    <div class="mb-2.5 inline-flex items-center gap-1.5 rounded-none bg-soft-sand px-2.5 py-1 text-xs font-medium text-intense-cocoa short:mb-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5 text-intense-cocoa/70" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M6 5v1H4.667a1.75 1.75 0 0 0-1.741 1.575l-.834 8.5A1.75 1.75 0 0 0 3.834 18h12.332a1.75 1.75 0 0 0 1.742-1.925l-.834-8.5A1.75 1.75 0 0 0 15.333 6H14V5a4 4 0 0 0-8 0Zm4-2.5A2.5 2.5 0 0 0 7.5 5v1h5V5A2.5 2.5 0 0 0 10 2.5ZM4.333 7.5h11.334l.833 8.5a.25.25 0 0 1-.249.275H3.834a.25.25 0 0 1-.249-.275l.833-8.5Z" clip-rule="evenodd" />
                                        </svg>
                                        <span>{{ __('storefront.products.already_in_cart', ['count' => $cartQuantity]) }}</span>
                                    </div>
                                @endif

                                </div>

                                {{-- A stepper for a quantity that cannot be added is dead
                                     controls: hidden once the cart already holds all the
                                     stock, while the label and badge stay to explain why. --}}
                                @if ($availableStock > 0)
                                <div class="inline-flex items-center overflow-hidden border border-intense-cocoa short:[&_button]:h-9 short:[&_span]:h-9">
                                    <button
                                        type="button"
                                        wire:click="$set('quantity', {{ max(1, $quantity - 1) }})"
                                        aria-label="Decrease quantity"
                                        class="flex h-11 w-11 items-center justify-center text-intense-cocoa transition-colors hover:bg-intense-cocoa hover:text-silk-cream disabled:cursor-not-allowed disabled:text-intense-cocoa/30 disabled:hover:bg-transparent"
                                        @if ($quantity <= 1) disabled @endif
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                                            <path fill-rule="evenodd" d="M4 10a.75.75 0 0 1 .75-.75h10.5a.75.75 0 0 1 0 1.5H4.75A.75.75 0 0 1 4 10Z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <input
                                        id="qv-product-qty"
                                        type="number"
                                        min="1"
                                        max="{{ max(1, $availableStock) }}"
                                        value="{{ $quantity }}"
                                        readonly
                                        class="h-11 w-14 border-x border-intense-cocoa bg-transparent text-center text-sm font-medium tabular-nums text-intense-cocoa focus:outline-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none [&::-moz-appearance:textfield]"
                                        aria-label="{{ __('storefront.products.quantity_label') }}"
                                    >
                                    <button
                                        type="button"
                                        wire:click="$set('quantity', {{ min(max(1, $availableStock), $quantity + 1) }})"
                                        aria-label="Increase quantity"
                                        class="flex h-11 w-11 items-center justify-center text-intense-cocoa transition-colors hover:bg-soft-gold disabled:cursor-not-allowed disabled:text-intense-cocoa/30 disabled:hover:bg-transparent"
                                        @if ($quantity >= $availableStock || $availableStock <= 0) disabled @endif
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                                            <path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z" />
                                        </svg>
                                    </button>
                                </div>
                                @endif
                            </div>
                        @endif

                        {{-- Action buttons (With Loading Feedback) --}}
                        <div class="flex flex-col gap-3 pt-2 md:gap-1.5 md:pt-0">
                            @php
                                $canAddToCart = $selectedVariant && $selectedVariant->stock > 0 && $availableStock > 0;
                            @endphp

                            {{-- Add to cart. Side by side from md: two stacked
                                 full-width buttons cost ~70px of height, which
                                 is the difference between fitting a laptop
                                 screen and not. --}}
                            <div class="flex flex-col gap-3 md:grid md:grid-cols-2 md:gap-2">
                            @if ($canAddToCart)
                                <x-primary-button
                                    type="button"
                                    wire:click="addToCart"
                                    wire:loading.attr="disabled"
                                    class="w-full whitespace-nowrap short:h-10 focus:outline-none disabled:bg-intense-cocoa/40 disabled:hover:bg-intense-cocoa/40 disabled:hover:text-silk-cream md:px-3"
                                    aria-label="{{ $product->is_preorder ? __('storefront.products.add_to_cart_preorder') : __('storefront.products.add_to_cart') }}"
                                >
                                    <span wire:loading.remove wire:target="addToCart" class="inline-flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="mr-2 h-5 w-5" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.46 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM11.25 10.5h.008v.008h-.008V10.5Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                        </svg>
                                        {{ $product->is_preorder ? __('storefront.products.add_to_cart_preorder') : __('storefront.products.add_to_cart') }}
                                    </span>
                                    <span wire:loading wire:target="addToCart" class="inline-flex items-center gap-2">
                                        <svg class="h-4 w-4 animate-spin text-silk-cream" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        {{ __('storefront.adding_to_cart') }}
                                    </span>
                                </x-primary-button>
                                <x-secondary-button
                                    type="button"
                                    wire:click="buyNow"
                                    wire:loading.attr="disabled"
                                    class="w-full whitespace-nowrap short:h-10 md:px-3"
                                    aria-label="{{ __('storefront.products.buy_now') }}"
                                >
                                    <span wire:loading.remove wire:target="buyNow">
                                        {{ __('storefront.products.buy_now') }}
                                    </span>
                                    <span wire:loading wire:target="buyNow" class="inline-flex items-center gap-2">
                                        <svg class="h-4 w-4 animate-spin text-intense-cocoa" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        {{ __('storefront.adding_to_cart') }}
                                    </span>
                                </x-secondary-button>
                            @else
                                <x-primary-button
                                    type="button"
                                    disabled
                                    class="w-full whitespace-nowrap short:h-10 focus:outline-none disabled:bg-intense-cocoa/40 disabled:hover:bg-intense-cocoa/40 disabled:hover:text-silk-cream md:px-3"
                                    aria-label="{{ __('storefront.products.add_to_cart') }}"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="mr-2 h-5 w-5" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.46 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM11.25 10.5h.008v.008h-.008V10.5Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                    </svg>
                                    {{ __('storefront.products.add_to_cart') }}
                                </x-primary-button>

                                {{-- Disabled rather than removed: a button that vanishes
                                     reflows everything under it and leaves the shopper
                                     without an explanation. --}}
                                <x-secondary-button
                                    type="button"
                                    disabled
                                    class="w-full whitespace-nowrap short:h-10 disabled:border-intense-cocoa/25 disabled:text-intense-cocoa/40 disabled:hover:bg-transparent disabled:hover:text-intense-cocoa/40 md:px-3"
                                    aria-label="{{ __('storefront.products.buy_now') }}"
                                >
                                    {{ __('storefront.products.buy_now') }}
                                </x-secondary-button>
                            @endif

                            </div>

                            {{-- Favorites heart button matching Product Detail --}}
                            <button
                                type="button"
                                wire:click="toggleFavorite"
                                dusk="quick-view-favorite"
                                wire:loading.attr="disabled"
                                class="flex h-12 w-full cursor-pointer items-center justify-center border border-soft-gold md:h-10 short:h-9 text-sm font-medium text-intense-cocoa transition-colors duration-200 hover:border-intense-cocoa hover:bg-intense-cocoa hover:text-silk-cream focus:outline-none disabled:opacity-50"
                                aria-label="{{ $isFavorited ? __('storefront.products.remove_from_favorites_label') : __('storefront.products.add_to_favorites_label') }}"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    @if ($isFavorited) fill="currentColor" @else fill="none" stroke="currentColor" stroke-width="1.5" @endif
                                    class="mr-2 h-5 w-5 {{ $isFavorited ? 'text-soft-gold' : '' }}"
                                    aria-hidden="true"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                                </svg>
                                {{ $isFavorited ? __('storefront.products.remove_from_favorites_label') : __('storefront.products.add_to_favorites_label') }}
                            </button>

                            {{-- View full details link with SVG Arrow --}}
                            <div class="mt-1 text-center">
                                <a
                                    href="{{ route('products.show', $product->slug) }}"
                                    class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-intense-cocoa/70 underline underline-offset-4 transition-colors hover:text-soft-gold"
                                >
                                    <span>{{ __('storefront.products.view_full_details') }}</span>
                                    <svg class="h-3.5 w-3.5 text-intense-cocoa/70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
