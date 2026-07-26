<div>
    <button
        type="button"
        wire:click="toggle"
        title="{{ auth()->check() ? ($isFavorited ? __('storefront.products.remove_from_favorites_label') : __('storefront.products.add_to_favorites_label')) : __('storefront.favorite_login_required') }}"
        aria-label="{{ auth()->check() ? ($isFavorited ? __('storefront.products.remove_from_favorites_label') : __('storefront.products.add_to_favorites_label')) : __('storefront.favorite_login_required') }}"
        class="flex h-10 w-10 items-center justify-center bg-soft-sand shadow-sm transition-colors hover:bg-soft-gold hover:text-intense-cocoa {{ auth()->check() ? 'text-intense-cocoa cursor-pointer' : 'text-intense-cocoa opacity-50 cursor-not-allowed' }} {{ $isFavorited ? 'text-soft-gold' : '' }}"
        data-favorite-button
        data-product-variant-id="{{ $productVariantId }}"
    >
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" @if ($isFavorited) fill="currentColor" @else fill="none" stroke="currentColor" stroke-width="1.5" @endif class="h-5 w-5" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
        </svg>
    </button>
</div>
