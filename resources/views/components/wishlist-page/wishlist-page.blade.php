@php
    use App\Support\ColorMap;

    $itemCount = $items->total();
@endphp

<x-partials.toast>
<div class="py-8 lg:py-12">
    {{-- Breadcrumb --}}
    <x-breadcrumb.breadcrumb :items="[
        ['label' => __('storefront.wishlist.breadcrumb_home'), 'href' => route('home')],
        ['label' => __('storefront.wishlist.breadcrumb_wishlist')],
    ]"></x-breadcrumb.breadcrumb>

    <div class="mx-auto max-w-storefront px-margin-mobile lg:px-margin-desktop">
        <x-page-header title="{{ __('storefront.wishlist.title') }}">
            <span class="ml-2 text-lg font-normal text-intense-cocoa/60" data-wishlist-count>({{ $itemCount }})</span>
        </x-page-header>

        @if ($errorMessage)
            <x-alert type="error" data-wishlist-error class="mb-4">
                {{ $errorMessage }}
            </x-alert>
        @endif

        @if ($itemCount === 0)
            {{-- Empty state (R7) --}}
            <div class="flex flex-col items-center justify-center gap-4 py-20 text-center" data-wishlist-empty>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-16 w-16 text-intense-cocoa/40" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                </svg>
                <h2 class="font-[family-name:var(--font-chillax)] text-2xl font-semibold text-intense-cocoa">
                    {{ __('storefront.wishlist.empty_title') }}
                </h2>
                <p class="max-w-sm text-intense-cocoa/70">
                    {{ __('storefront.wishlist.empty_message') }}
                </p>
                <x-secondary-button
                    tag="a"
                    href="{{ route('products.index') }}"
                    class="mt-2 h-11 px-6"
                    data-wishlist-empty-cta
                >
                    {{ __('storefront.wishlist.empty_cta') }}
                </x-secondary-button>
            </div>
        @else
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4" data-wishlist-grid>
                @foreach ($items as $item)
                    @php
                        $variant = $item['variant'];
                        $product = $variant->product;
                        $isAvailable = $item['isAvailable'];
                        $isOutOfStock = $item['isOutOfStock'];
                        $canAddToCart = $isAvailable && ! $isOutOfStock;
                        $price = $variant->priceIn($currencyEnum);
                        $image = $variant->images->first() ?? $product->primaryImage();
                        $detailUrl = route('products.show', $product->slug);
                    @endphp
                    <x-product-card
                        :product="$product"
                        :currency-enum="$currencyEnum"
                        :primary-image="$image"
                        :variant="$variant"
                        :price="$price"
                        :detail-url="$detailUrl"
                        :is-out-of-stock="$isOutOfStock"
                        :is-available="$isAvailable"
                        :show-hover-actions="false"
                        wire:key="wishlist-item-{{ $variant->id }}"
                        data-wishlist-item="{{ $variant->id }}"
                    >
                        <x-slot:variantInfo>
                            @if ($variant->color || $variant->size)
                                <p class="text-sm text-intense-cocoa/60" data-wishlist-variant-attributes>
                                    @if ($variant->color)
                                        <span class="inline-block h-3 w-3 border border-intense-cocoa/10 align-middle" style="background-color: {{ ColorMap::HEX[strtolower($variant->color)] ?? '#8B8B8B' }}"></span>
                                        {{ __('storefront.products.color_label') }}: {{ $variant->color }}
                                    @endif
                                    @if ($variant->color && $variant->size)
                                        &middot;
                                    @endif
                                    @if ($variant->size)
                                        {{ __('storefront.products.size_label') }}: {{ $variant->size }}
                                    @endif
                                </p>
                            @elseif (! $isAvailable)
                                <p class="text-xs text-intense-cocoa/50">
                                    {{ __('storefront.wishlist.unavailable_message') }}
                                    <a href="{{ route('products.index') }}" class="underline underline-offset-2 hover:text-intense-cocoa">{{ __('storefront.wishlist.explore_similar') }}</a>
                                </p>
                            @endif
                        </x-slot:variantInfo>

                        <x-slot:actions>
                            <div class="flex items-center gap-2">
                                <x-primary-button
                                    type="button"
                                    :disabled="! $canAddToCart"
                                    wire:click="addToCart({{ $variant->id }})"
                                    class="flex-1 px-3 disabled:opacity-50"
                                    data-wishlist-add-to-cart="{{ $variant->id }}"
                                >
                                    {{ __('storefront.add_to_cart') }}
                                </x-primary-button>

                                <button
                                    type="button"
                                    wire:click="removeFromWishlist({{ $variant->id }})"
                                    aria-label="{{ __('storefront.wishlist.remove') }}"
                                    title="{{ __('storefront.wishlist.remove') }}"
                                    class="flex h-12 w-12 shrink-0 items-center justify-center border border-intense-cocoa text-intense-cocoa transition-colors hover:border-error hover:bg-error hover:text-silk-cream"
                                    data-wishlist-remove="{{ $variant->id }}"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-5 w-5" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                        </x-slot:actions>
                    </x-product-card>
                @endforeach
            </div>

            @if ($items->hasPages())
                <div class="mt-12">
                    {{ $items->links('vendor.pagination.custom') }}
                </div>
            @endif
        @endif
    </div>
</div>
</x-partials.toast>
