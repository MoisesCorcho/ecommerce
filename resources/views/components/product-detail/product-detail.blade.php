{{--
  Product Detail — Two-column layout with gallery, info, and actions.
  Requirements: R1-R18
--}}
@php
    use App\Support\ColorMap;

    $breadcrumbItems = [
        ['label' => __('storefront.products.breadcrumb_home'), 'href' => route('home')],
        ['label' => __('storefront.products.breadcrumb_shop'), 'href' => route('products.index')],
    ];
    if ($product->category) {
        $breadcrumbItems[] = ['label' => $product->category->name, 'href' => route('products.index', ['category' => $product->category->slug])];
    }
    $breadcrumbItems[] = ['label' => $product->name];
@endphp

<x-partials.toast>
<div
    x-data="{
        activeImageIndex: @js($mainImageIndex),
        lightbox: false,
        lightboxIndex: 0,
        init() {
            this.$watch('$wire.mainImageIndex', (val) => {
                if (val !== undefined && val !== null) {
                    this.activeImageIndex = val;
                }
            });
        },
        openLightbox(index) {
            this.lightboxIndex = (index !== undefined) ? index : this.activeImageIndex;
            this.lightbox = true;
        },
        closeLightbox() {
            this.lightbox = false;
        },
        nextImage() {
            if (this.lightboxIndex < {{ $product->images->count() - 1 }}) {
                this.lightboxIndex++;
            }
        },
        prevImage() {
            if (this.lightboxIndex > 0) {
                this.lightboxIndex--;
            }
        }
    }"
    x-on:cart-updated.window="$dispatch('toast', { message: '{{ __('storefront.added_to_cart') }}' })"
    class="relative py-8 lg:py-12"
>
    {{-- Breadcrumb (R12) --}}
    <x-breadcrumb.breadcrumb :items="$breadcrumbItems"></x-breadcrumb.breadcrumb>

    {{-- Two-column grid (R1: lg+ two columns, sm/md single column) --}}
    <div class="mx-auto grid max-w-storefront gap-8 px-margin-mobile lg:grid-cols-[1.2fr_1fr] lg:gap-12 lg:px-margin-desktop">

        {{-- LEFT: Gallery (R2, R3, R4) --}}
        <div>
            {{-- Main image — centered in container --}}
            <div
                class="group relative flex aspect-[4/5] cursor-zoom-in items-center justify-center overflow-hidden bg-soft-sand lg:max-h-[70vh]"
                @click="openLightbox(activeImageIndex)"
            >
                @if ($product->images->count() > 0)
                    @foreach ($product->images as $index => $image)
                        <img
                            x-show="activeImageIndex === {{ $index }}"
                            x-cloak
                            src="/storage/{{ $image->path }}"
                            alt="{{ $product->name }} — {{ $loop->iteration }}"
                            class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.02]"
                        >
                    @endforeach
                    {{-- Zoom hint --}}
                    <div class="absolute inset-0 flex items-center justify-center bg-intense-cocoa/0 transition-colors group-hover:bg-intense-cocoa/10">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-8 w-8 text-silk-cream opacity-0 transition-opacity group-hover:opacity-80" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607ZM10.5 7.5v6m3-3h-6" />
                        </svg>
                    </div>
                @else
                    <div class="flex aspect-[4/5] items-center justify-center bg-soft-sand text-intense-cocoa/40">
                        <span class="text-label-caps">{{ __('storefront.no_image') }}</span>
                    </div>
                @endif
            </div>

            {{-- Thumbnail row (R2) --}}
            @if ($product->images->count() > 1)
                <div class="mt-3 flex gap-2 overflow-x-auto pb-1" role="listbox" aria-label="Product images">
                    @foreach ($product->images as $index => $image)
                        <button
                            type="button"
                            @click="activeImageIndex = {{ $index }}"
                            role="option"
                            :aria-selected="activeImageIndex === {{ $index }} ? 'true' : 'false'"
                            aria-label="Image {{ $loop->iteration }}"
                            class="group/thumbnail relative flex-shrink-0 overflow-hidden border-2 transition-all duration-200"
                            :class="activeImageIndex === {{ $index }} ? 'border-intense-cocoa' : 'border-transparent hover:border-intense-cocoa/30'"
                        >
                            <img
                                src="/storage/{{ $image->path }}"
                                alt="{{ $product->name }} — {{ $loop->iteration }}"
                                class="h-16 w-16 object-cover sm:h-20 sm:w-20"
                            >
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- RIGHT: Purchase Info (R5, R6, R7, R8, R9, R10, R11) --}}
        <div class="flex flex-col gap-6">
            @php
                $availableStock = $selectedVariant ? max(0, $selectedVariant->stock - $cartQuantity) : 0;
            @endphp

            {{-- Product name (R5) --}}
            <div>
                @if ($product->category)
                    <p class="mb-2 text-label-caps uppercase tracking-[0.2em] text-intense-cocoa/50">
                        {{ $product->category->name }}
                    </p>
                @endif
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="font-[family-name:var(--font-chillax)] text-3xl font-semibold tracking-tight text-intense-cocoa sm:text-4xl">
                        {{ $product->name }}
                    </h1>
                    @if ($product->is_preorder)
                        <span class="inline-flex items-center bg-intense-cocoa px-3 py-1 text-xs font-semibold uppercase tracking-widest text-silk-cream">
                            {{ __('storefront.products.preorder_badge') }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- Price + Stock (R5, R18) --}}
            <div class="flex flex-wrap items-center gap-3">
                @if ($selectedVariant && $selectedVariant->priceIn($currencyEnum))
                    @php
                        $price = $selectedVariant->priceIn($currencyEnum);
                    @endphp
                    <span class="font-[family-name:var(--font-sans)] text-2xl font-semibold tabular-nums text-intense-cocoa">
                        {{ $currencyEnum->format($price->price) }}
                    </span>
                    @if ($price->hasDiscount())
                        <span class="font-[family-name:var(--font-sans)] text-xl font-normal line-through text-intense-cocoa/40">
                            {{ $currencyEnum->format($price->compare_at_price) }}
                        </span>
                        <span class="bg-soft-gold text-intense-cocoa border border-soft-gold/30 px-2.5 py-0.5 text-label-caps font-semibold uppercase tracking-wider">
                            -{{ $price->discountPercentage() }}%
                        </span>
                    @endif
                @elseif($pricedVariants->first()?->priceIn($currencyEnum))
                    @php
                        $price = $pricedVariants->first()->priceIn($currencyEnum);
                    @endphp
                    <span class="font-[family-name:var(--font-sans)] text-2xl font-semibold tabular-nums text-intense-cocoa">
                        {{ $currencyEnum->format($price->price) }}
                    </span>
                    @if ($price->hasDiscount())
                        <span class="font-[family-name:var(--font-sans)] text-xl font-normal line-through text-intense-cocoa/40">
                            {{ $currencyEnum->format($price->compare_at_price) }}
                        </span>
                        <span class="bg-soft-gold text-intense-cocoa border border-soft-gold/30 px-2.5 py-0.5 text-label-caps font-semibold uppercase tracking-wider">
                            -{{ $price->discountPercentage() }}%
                        </span>
                    @endif
                @endif

                {{-- Out of stock badge (R18) --}}
                @if ($selectedVariant && $selectedVariant->stock <= 0 && ! $product->is_preorder)
                    <span class="inline-flex items-center justify-center bg-soft-gold px-2.5 py-1 text-label-caps font-semibold uppercase tracking-wider text-intense-cocoa">
                        {{ __('storefront.out_of_stock') }}
                    </span>
                @endif
            </div>

            {{-- Stock status text (R5) --}}
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

            {{-- SKU (R5) --}}
            @if ($selectedVariant?->sku)
                <p class="text-label-caps text-intense-cocoa/40">
                    {{ __('storefront.products.sku_label') }}: {{ $selectedVariant->sku }}
                </p>
            @endif

            {{-- Brief description (R5) --}}
            @if ($product->description)
                <p class="text-body-md leading-relaxed text-intense-cocoa/80 lg:text-body-lg line-clamp-3">
                    {{ Str::limit($product->description, 200) }}
                </p>
            @endif

            {{-- Color selector (R6) --}}
            @if ($availableColors->count() > 0)
                <div>
                    <p class="mb-2.5 text-sm font-medium text-intense-cocoa">
                        {{ __('storefront.products.color_label') }}:
                        <span class="font-normal text-intense-cocoa/60">{{ $selectedColor }}</span>
                    </p>
                    <div class="flex flex-wrap gap-2.5" role="radiogroup" aria-label="{{ __('storefront.products.color_label') }}">
                        @foreach ($availableColors as $colorName)
                            @php
                                $hex = ColorMap::for($colorName);
                                $isSelected = $selectedColor === $colorName;
                            @endphp
                            <button
                                type="button"
                                wire:click="$set('selectedColor', '{{ $colorName }}')"
                                role="radio"
                                aria-checked="{{ $isSelected ? 'true' : 'false' }}"
                                aria-label="{{ $colorName }}"
                                title="{{ $colorName }}"
                                class="relative h-9 w-9 border border-intense-cocoa/20 transition-all hover:ring-2 hover:ring-soft-gold hover:ring-offset-2 focus:outline-none {{ $isSelected ? 'border-intense-cocoa ring-2 ring-intense-cocoa ring-offset-2' : '' }}"
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

            {{-- Size selector (R7) --}}
            @if ($availableSizes->count() > 0)
                <div>
                    <p class="mb-2.5 text-sm font-medium text-intense-cocoa">
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
                                role="radio"
                                aria-checked="{{ $isSelected ? 'true' : 'false' }}"
                                class="min-h-[44px] min-w-[44px] border px-4 py-2 text-sm font-medium transition-all duration-200 focus:outline-none {{ $isSelected ? 'border-intense-cocoa bg-intense-cocoa text-silk-cream' : 'border-transparent bg-soft-sand text-intense-cocoa hover:border-intense-cocoa' }}"
                            >
                                {{ \App\Enums\Products\SizeEnum::tryFrom($sizeName)?->label() ?? $sizeName }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

                {{-- Select variant hint (R18) --}}
                @if (! $selectedVariant)
                    <p class="bg-soft-sand px-4 py-2.5 text-body-md text-intense-cocoa/80 leading-relaxed lg:text-body-lg" data-select-variant-hint>
                        {{ __('storefront.products.select_variant') }}
                    </p>
                @elseif ($selectedVariant->stock > 0 && $availableStock <= 0)
                    <p class="bg-soft-gold/20 px-4 py-2.5 text-body-md text-intense-cocoa/80 leading-relaxed lg:text-body-lg">
                        {{ __('storefront.products.stock_in_cart') }}
                    </p>
                @endif

            {{-- Quantity selector (R8) --}}
            @if ($selectedVariant && $selectedVariant->stock > 0)
                <div class="flex flex-col items-start">
                    <label for="product-qty" class="mb-2 block text-sm font-medium text-intense-cocoa">
                        {{ __('storefront.products.quantity_label') }}
                    </label>

                    @if ($cartQuantity > 0)
                        <div class="mb-2.5 inline-flex items-center gap-1.5 rounded-none bg-soft-sand px-2.5 py-1 text-xs font-medium text-intense-cocoa">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5 text-intense-cocoa/70" aria-hidden="true">
                                <path fill-rule="evenodd" d="M6 5v1H4.667a1.75 1.75 0 0 0-1.741 1.575l-.834 8.5A1.75 1.75 0 0 0 3.834 18h12.332a1.75 1.75 0 0 0 1.742-1.925l-.834-8.5A1.75 1.75 0 0 0 15.333 6H14V5a4 4 0 0 0-8 0Zm4-2.5A2.5 2.5 0 0 0 7.5 5v1h5V5A2.5 2.5 0 0 0 10 2.5ZM4.333 7.5h11.334l.833 8.5a.25.25 0 0 1-.249.275H3.834a.25.25 0 0 1-.249-.275l.833-8.5Z" clip-rule="evenodd" />
                            </svg>
                            <span>{{ __('storefront.products.already_in_cart', ['count' => $cartQuantity]) }}</span>
                        </div>
                    @endif

                    <div class="inline-flex items-center overflow-hidden border border-intense-cocoa">
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
                            id="product-qty"
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
                </div>
            @endif

            {{-- Action buttons (R9, R10, R11) --}}
            <div class="flex flex-col gap-3 pt-2">
                @php
                    $canAddToCart = $selectedVariant && $selectedVariant->stock > 0 && $availableStock > 0;
                @endphp

                {{-- Add to cart (R9) --}}
                @if ($canAddToCart)
                    <x-primary-button
                        type="button"
                        wire:click="addToCart"
                        class="w-full focus:outline-none disabled:bg-intense-cocoa/40 disabled:hover:bg-intense-cocoa/40 disabled:hover:text-silk-cream"
                        data-add-to-cart
                        aria-label="{{ $product->is_preorder ? __('storefront.products.add_to_cart_preorder') : __('storefront.products.add_to_cart') }}"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="mr-2 h-5 w-5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.46 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM11.25 10.5h.008v.008h-.008V10.5Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                        </svg>
                        {{ $product->is_preorder ? __('storefront.products.add_to_cart_preorder') : __('storefront.products.add_to_cart') }}
                    </x-primary-button>
                @else
                    <x-primary-button
                        type="button"
                        disabled
                        class="w-full focus:outline-none disabled:bg-intense-cocoa/40 disabled:hover:bg-intense-cocoa/40 disabled:hover:text-silk-cream"
                        data-add-to-cart
                        aria-label="{{ __('storefront.products.add_to_cart') }}"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="mr-2 h-5 w-5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.46 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM11.25 10.5h.008v.008h-.008V10.5Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                        </svg>
                        {{ __('storefront.products.add_to_cart') }}
                    </x-primary-button>
                @endif
                @if ($canAddToCart)
                    <x-secondary-button
                        type="button"
                        wire:click="buyNow"
                        class="w-full"
                        aria-label="{{ __('storefront.products.buy_now') }}"
                    >
                        {{ __('storefront.products.buy_now') }}
                    </x-secondary-button>
                @else
                    <x-secondary-button
                        type="button"
                        disabled
                        class="w-full"
                        aria-label="{{ __('storefront.products.buy_now') }}"
                    >
                        {{ __('storefront.products.buy_now') }}
                    </x-secondary-button>
                @endif

                {{-- Favorites heart (R11) --}}
                <button
                    type="button"
                    wire:click="toggleFavorite"
                    class="flex h-12 w-full cursor-pointer items-center justify-center border border-soft-gold text-sm font-medium text-intense-cocoa transition-colors duration-200 hover:border-intense-cocoa hover:bg-intense-cocoa hover:text-silk-cream focus:outline-none"
                    aria-label="{{ $isFavorited ? __('storefront.products.remove_from_favorites_label') : __('storefront.products.add_to_favorites_label') }}"
                    data-favorite-button
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
            </div>

            {{-- Error message --}}
            @if ($errorMessage)
                <div class="rounded-none border border-error/20 bg-error/5 px-4 py-3 text-sm text-error" role="alert" data-add-error>
                    {{ $errorMessage }}
                </div>
            @endif
        </div>
    </div>

    {{-- Description section (R13) — full-width background, contained content --}}
    @if ($product->description)
        <section class="mt-16 bg-soft-sand" aria-labelledby="description-heading">
            <div class="mx-auto max-w-4xl px-margin-mobile py-12 sm:py-16 lg:px-margin-desktop">
                    <h2 id="description-heading" class="mb-6 font-[family-name:var(--font-chillax)] text-2xl font-semibold text-intense-cocoa lg:text-3xl">
                        {{ __('storefront.products.description_title') }}
                    </h2>
                    <div class="space-y-4 text-body-md leading-relaxed text-intense-cocoa/80 lg:text-body-lg">
                        @if ($product->material)
                            <div>
                                <span class="font-medium text-intense-cocoa">{{ __('storefront.products.material_label') }}:</span>
                                <span class="text-intense-cocoa/70">{{ $product->material }}</span>
                            </div>
                        @endif
                        @if ($selectedVariant?->dimensions)
                            <div>
                                <span class="font-medium text-intense-cocoa">{{ __('storefront.products.dimensions_label') }}:</span>
                                <span class="text-intense-cocoa/70">{{ $selectedVariant->dimensions }}</span>
                            </div>
                        @endif
                        <p class="whitespace-pre-line">{{ $product->description }}</p>
                    </div>
            </div>
        </section>
    @endif

    {{-- Reviews (F07) --}}
    <section
        class="mx-auto mt-10 max-w-storefront px-margin-mobile sm:mt-12 lg:px-margin-desktop"
        aria-labelledby="reviews-heading"
        data-reviews-section
    >
        <div class="mb-8 flex flex-wrap items-end justify-between gap-4 border-b border-intense-cocoa/10 pb-6">
            <div>
                <h2 id="reviews-heading" class="font-[family-name:var(--font-chillax)] text-2xl font-semibold text-intense-cocoa">
                    {{ __('reviews.ui.section_title') }}
                </h2>
                @if ($reviewsSummary->reviewsCount > 0)
                    <p class="mt-2 text-sm text-intense-cocoa/60">
                        <span class="font-medium text-intense-cocoa">
                            {{ number_format((float) $reviewsSummary->averageRating, 1) }}★
                        </span>
                        ·
                        {{ trans_choice('reviews.ui.count_label', $reviewsSummary->reviewsCount, ['count' => $reviewsSummary->reviewsCount]) }}
                    </p>
                @endif
            </div>
             <div class="grid items-start gap-10 lg:grid-cols-[1.2fr_1fr]">
            {{-- Public approved list (order-2 on mobile/tablet, order-1 on desktop) --}}
            <div class="order-2 space-y-6 lg:order-1" data-approved-reviews>
                @forelse ($approvedReviews as $review)
                    <article class="bg-surface-container p-5 shadow-sm" wire:key="review-{{ $review->id }}">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <span class="text-sm font-semibold text-soft-gold" aria-label="{{ __('reviews.ui.stars', ['rating' => $review->rating]) }}">
                                {{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}
                            </span>
                            <span class="text-sm font-medium text-intense-cocoa">{{ $review->user?->name }}</span>
                        </div>
                        @if ($review->purchased_variants && count($review->purchased_variants) > 0)
                            <div class="mb-2 flex flex-wrap gap-1.5">
                                @foreach ($review->purchased_variants as $variant)
                                    <span class="inline-flex items-center gap-1 border border-intense-cocoa/10 bg-soft-sand/60 px-2 py-0.5 text-xs text-intense-cocoa/70">
                                        @if ($variant['color'])
                                            <span class="font-medium">{{ $variant['color'] }}</span>
                                        @endif
                                        @if ($variant['size'])
                                            <span class="font-medium">{{ $variant['size'] }}</span>
                                        @endif
                                        @if ($variant['sku'])
                                            <span class="text-intense-cocoa/40">({{ $variant['sku'] }})</span>
                                        @endif
                                    </span>
                                @endforeach
                            </div>
                        @endif
                        @if ($review->comment)
                            <p class="text-body-md leading-relaxed text-intense-cocoa/80 lg:text-body-lg">{{ $review->comment }}</p>
                        @endif
                        <time class="mt-2 block text-xs text-intense-cocoa/40" datetime="{{ $review->created_at?->toIso8601String() }}">
                            {{ $review->created_at?->format('d/m/Y') }}
                        </time>
                    </article>
                @empty
                    <p class="text-sm text-intense-cocoa/60" data-reviews-empty>
                        {{ __('reviews.empty.no_reviews') }}
                    </p>
                @endforelse
                @if ($totalPages > 1)
                    <nav class="mt-6 flex items-center justify-center gap-1.5" aria-label="{{ __('reviews.ui.pagination') }}">
                        @if ($reviewsPage > 1)
                            <button
                                type="button"
                                wire:click="goToReviewsPage({{ $reviewsPage - 1 }})"
                                class="flex h-9 w-9 items-center justify-center border border-intense-cocoa bg-transparent text-xs font-semibold uppercase tracking-widest text-intense-cocoa transition-all duration-200 hover:border-soft-gold hover:text-soft-gold focus:outline-none"
                                aria-label="{{ __('reviews.ui.previous_page') }}"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 0 1-.02 1.06L8.832 10l3.938 3.71a.75.75 0 1 1-1.04 1.08l-4.5-4.25a.75.75 0 0 1 0-1.08l4.5-4.25a.75.75 0 0 1 1.06.02Z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        @endif

                        @for ($i = 1; $i <= $totalPages; $i++)
                            <button
                                type="button"
                                wire:click="goToReviewsPage({{ $i }})"
                                class="flex h-9 w-9 items-center justify-center border text-xs font-semibold uppercase tracking-widest transition-all duration-200 focus:outline-none {{ $reviewsPage === $i ? 'border-intense-cocoa bg-intense-cocoa text-silk-cream' : 'border-intense-cocoa bg-transparent text-intense-cocoa hover:border-soft-gold hover:text-soft-gold' }}"
                                aria-current="{{ $reviewsPage === $i ? 'page' : false }}"
                            >
                                {{ $i }}
                            </button>
                        @endfor

                        @if ($reviewsPage < $totalPages)
                            <button
                                type="button"
                                wire:click="goToReviewsPage({{ $reviewsPage + 1 }})"
                                class="flex h-9 w-9 items-center justify-center border border-intense-cocoa bg-transparent text-xs font-semibold uppercase tracking-widest text-intense-cocoa transition-all duration-200 hover:border-soft-gold hover:text-soft-gold focus:outline-none"
                                aria-label="{{ __('reviews.ui.next_page') }}"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        @endif
                    </nav>
                @endif
            </div>

            {{-- Viewer form / notices (order-1 on mobile/tablet, order-2 on desktop) --}}
            <div class="order-1 bg-surface-container p-6 shadow-sm lg:order-2" data-review-form>
                @auth
                    @if ($canCreateReview || $canEditReview)
                        <h3 class="mb-4 font-[family-name:var(--font-chillax)] text-lg font-semibold text-intense-cocoa">
                            {{ $canEditReview ? __('reviews.ui.edit_review') : __('reviews.ui.write_review') }}
                        </h3>

                        @if ($viewerReview && ! $viewerReview->is_approved)
                            <p class="mb-4 rounded-none border border-soft-gold/40 bg-soft-sand/60 px-3 py-2 text-sm text-intense-cocoa/80" data-review-pending>
                                {{ __('reviews.ui.pending_notice') }}
                            </p>
                        @endif

                        <form wire:submit="saveReview" class="space-y-4">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-intense-cocoa" for="review-rating">
                                    {{ __('reviews.fields.rating') }}
                                </label>
                                <select
                                    id="review-rating"
                                    wire:model="reviewRating"
                                    class="h-11 w-full border border-intense-cocoa/20 bg-silk-cream px-3 text-sm text-intense-cocoa focus:border-intense-cocoa focus:outline-none focus:ring-1 focus:ring-intense-cocoa"
                                >
                                    <option value="">{{ __('reviews.ui.rating_required') }}</option>
                                    @for ($star = 5; $star >= 1; $star--)
                                        <option value="{{ $star }}">{{ $star }} ★</option>
                                    @endfor
                                </select>
                                @error('reviewRating')
                                    <p class="mt-1 text-sm text-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-intense-cocoa" for="review-comment">
                                    {{ __('reviews.fields.comment') }}
                                </label>
                                <textarea
                                    id="review-comment"
                                    wire:model="reviewComment"
                                    rows="4"
                                    maxlength="2000"
                                    placeholder="{{ __('reviews.ui.comment_placeholder') }}"
                                    class="w-full border border-intense-cocoa/20 bg-silk-cream px-3 py-2 text-sm text-intense-cocoa placeholder:text-intense-cocoa/40 focus:border-intense-cocoa focus:outline-none focus:ring-1 focus:ring-intense-cocoa"
                                ></textarea>
                                @error('reviewComment')
                                    <p class="mt-1 text-sm text-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex flex-col items-center justify-center gap-3 pt-2 sm:flex-row">
                                <button
                                    type="submit"
                                    class="h-11 w-full min-w-[180px] bg-intense-cocoa px-5 text-xs font-semibold uppercase tracking-wider text-silk-cream transition-colors duration-200 hover:bg-soft-gold hover:text-intense-cocoa focus:outline-none sm:w-auto"
                                    wire:loading.attr="disabled"
                                >
                                    {{ $canEditReview ? __('reviews.actions.update') : __('reviews.actions.submit') }}
                                </button>

                                @if ($canEditReview)
                                    <button
                                        type="button"
                                        wire:click="deleteReview"
                                        wire:confirm="{{ __('reviews.ui.delete_confirm') }}"
                                        class="h-11 w-full min-w-[180px] border border-error/50 px-5 text-xs font-semibold uppercase tracking-wider text-error transition-colors duration-200 hover:border-error hover:bg-error hover:text-silk-cream focus:outline-none sm:w-auto"
                                    >
                                        {{ __('reviews.actions.delete_own') }}
                                    </button>
                                @endif
                            </div>
                        </form>
                    @else
                        <p class="text-sm text-intense-cocoa/70" data-review-not-eligible>
                            {{ __('reviews.ui.not_eligible') }}
                        </p>
                    @endif
                @else
                    <p class="text-sm text-intense-cocoa/70" data-review-login-required>
                        {{ __('reviews.ui.login_required') }}
                    </p>
                @endauth

                @if ($reviewStatusMessage)
                    <p class="mt-4 text-sm font-medium text-intense-cocoa" role="status" data-review-status>
                        {{ $reviewStatusMessage }}
                    </p>
                @endif
                @if ($reviewErrorMessage)
                    <p class="mt-4 rounded-none border border-error/20 bg-error/5 px-3 py-2 text-sm text-error" role="alert" data-review-error>
                        {{ $reviewErrorMessage }}
                    </p>
                @endif
            </div>
        </div>
    </section>

    {{-- Related products (R14) --}}
    @if ($relatedProducts->count() > 0)
        <section class="mx-auto mt-16 max-w-storefront px-margin-mobile sm:mt-20 lg:px-margin-desktop" aria-labelledby="related-heading">
            <div class="mb-8 flex items-end justify-between">
                <h2 id="related-heading" class="font-[family-name:var(--font-chillax)] text-2xl font-semibold text-intense-cocoa lg:text-3xl">
                    {{ __('storefront.products.related_title') }}
                </h2>
                <a href="{{ route('products.index', ['category' => $product->category?->slug]) }}" class="text-sm font-medium text-intense-cocoa/60 transition-colors hover:text-intense-cocoa hover:underline">
                    {{ __('storefront.home.view_all') }} →
                </a>
            </div>
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                @foreach ($relatedProducts as $relatedProduct)
                    @php
                        $relatedVariant = $relatedProduct->variants->first();
                    @endphp
                    <x-product-card
                        :product="$relatedProduct"
                        :currency="$currencyEnum->value"
                        :currencyEnum="$currencyEnum"
                        :primaryImage="$relatedProduct->primaryImage()"
                        :variant="$relatedVariant"
                        :price="$relatedVariant?->priceIn($currencyEnum)"
                        :detailUrl="route('products.show', $relatedProduct->slug)"
                        :isOutOfStock="$relatedProduct->isOutOfStock()"
                        :availableColors="$relatedProduct->availableColors()"
                    />
                @endforeach
            </div>
        </section>
    @endif

    {{-- Lightbox overlay (R3) --}}
    @if ($product->images->count() > 0)
        <div
            x-show="lightbox"
            x-cloak
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @keydown.escape.window="closeLightbox()"
            class="fixed inset-0 z-50 flex items-center justify-center bg-intense-cocoa/80 backdrop-blur-sm"
            role="dialog"
            aria-modal="true"
            aria-label="{{ __('storefront.products.close_lightbox') }}"
        >
            {{-- Backdrop click to close --}}
            <div class="absolute inset-0" @click="closeLightbox()"></div>

            {{-- Close button --}}
            <button
                type="button"
                @click="closeLightbox()"
                class="absolute right-4 top-4 z-10 flex h-10 w-10 items-center justify-center rounded-full bg-silk-cream/90 text-intense-cocoa transition-colors hover:bg-silk-cream focus:outline-none focus:ring-2 focus:ring-silk-cream/50"
                aria-label="{{ __('storefront.products.close_lightbox') }}"
            >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>

            {{-- Previous arrow --}}
            @if ($product->images->count() > 1)
                <button
                    type="button"
                    @click="prevImage()"
                    :disabled="lightboxIndex === 0"
                    class="absolute left-4 z-10 flex h-10 w-10 items-center justify-center rounded-full bg-silk-cream/90 text-intense-cocoa transition-colors hover:bg-silk-cream disabled:cursor-not-allowed disabled:opacity-30"
                    aria-label="Previous image"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5">
                        <path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
                    </svg>
                </button>
            @endif

            {{-- Next arrow --}}
            @if ($product->images->count() > 1)
                <button
                    type="button"
                    @click="nextImage()"
                    :disabled="lightboxIndex === {{ $product->images->count() - 1 }}"
                    class="absolute right-4 z-10 flex h-10 w-10 items-center justify-center rounded-full bg-silk-cream/90 text-intense-cocoa transition-colors hover:bg-silk-cream disabled:cursor-not-allowed disabled:opacity-30"
                    aria-label="Next image"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5">
                        <path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                    </svg>
                </button>
            @endif

            {{-- Lightbox image --}}
            <div class="relative z-10 max-h-[85vh] max-w-[90vw]">
                @foreach ($product->images as $index => $image)
                    <img
                        x-show="lightboxIndex === {{ $index }}"
                        src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image->path) }}"
                        alt="{{ $product->name }} — {{ $loop->iteration }}"
                        class="max-h-[85vh] max-w-[90vw] object-contain"
                    >
                @endforeach
            </div>
        </div>
    @endif
</div>
</x-partials.toast>
