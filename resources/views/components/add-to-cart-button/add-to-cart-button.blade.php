<div>
    @if ($statusMessage)
        <p class="mt-2 text-sm text-success" data-add-status>
            {{ $statusMessage }}
            <a href="{{ route('cart.page') }}" class="ml-1 font-medium underline">{{ __('storefront.view_cart') }}</a>
        </p>
    @endif

    @if ($errorMessage)
        <p class="mt-2 text-sm text-error" data-add-error>
            {{ $errorMessage }}
        </p>
    @endif

    <button
        type="button"
        wire:click="addToCart"
        wire:loading.attr="disabled"
        class="rounded bg-intense-cocoa px-4 py-2 text-sm font-medium text-silk-cream transition-colors hover:bg-soft-gold disabled:opacity-50"
        data-add-to-cart
    >
        <span wire:loading.remove>{{ __('storefront.add_to_cart') }}</span>
        <span wire:loading>{{ __('storefront.adding_to_cart') }}</span>
    </button>
</div>
