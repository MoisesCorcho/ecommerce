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

    <x-primary-button
        type="button"
        wire:click="addToCart"
        wire:loading.attr="disabled"
        class="rounded-none px-4 py-2 text-sm font-medium disabled:opacity-50"
        data-add-to-cart
    >
        <span wire:loading.remove>{{ __('storefront.add_to_cart') }}</span>
        <span wire:loading>{{ __('storefront.adding_to_cart') }}</span>
    </x-primary-button>
</div>
