@php
    $hasStockIssue = collect($cartView->lines)->contains(fn ($line) => $line->quantity > $line->stock || ! $line->isAvailable);
    $itemCount = collect($cartView->lines)->sum('quantity');
@endphp

<div class="py-8 lg:py-12" x-data="{ confirmingClear: false }">
    {{-- Breadcrumb --}}
    <x-breadcrumb.breadcrumb :items="[
        ['label' => __('cart.page.breadcrumb_home'), 'href' => route('home')],
        ['label' => __('cart.page.breadcrumb_cart')],
    ]"></x-breadcrumb.breadcrumb>

    <div class="mx-auto max-w-storefront px-margin-mobile lg:px-margin-desktop">
        <h1 class="mb-6 font-[family-name:var(--font-chillax)] text-3xl font-semibold tracking-tight text-intense-cocoa">
            {{ __('cart.page.title') }}
        </h1>

        @if ($statusMessage)
            <p class="mb-4 border border-success/20 bg-success/5 px-4 py-3 text-sm text-success" data-cart-status role="status">
                {{ $statusMessage }}
            </p>
        @endif

        @if ($errorMessage)
            <p class="mb-4 border border-error/20 bg-error/5 px-4 py-3 text-sm text-error" data-cart-error role="alert">
                {{ $errorMessage }}
            </p>
        @endif

        @if (count($cartView->lines) === 0)
            {{-- Empty state (R7) --}}
            <div class="flex flex-col items-center justify-center gap-4 py-20 text-center" data-cart-empty>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-16 w-16 text-intense-cocoa/40" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.46 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z" />
                </svg>
                <h2 class="font-[family-name:var(--font-chillax)] text-2xl font-semibold text-intense-cocoa">
                    {{ __('cart.empty.title') }}
                </h2>
                <p class="max-w-sm text-intense-cocoa/70">
                    {{ __('cart.empty.message') }}
                </p>
                <x-secondary-button
                    tag="a"
                    href="{{ route('products.index') }}"
                    class="mt-2 h-11 px-6"
                >
                    {{ __('cart.empty.cta') }}
                </x-secondary-button>
            </div>
        @else
            <div class="grid gap-8 lg:grid-cols-[1.6fr_1fr] lg:gap-12">
                {{-- LEFT: Line items --}}
                <div>
                    {{-- Currency selector --}}
                    <div class="mb-6 flex flex-wrap items-center gap-3">
                        <label class="text-sm font-medium text-intense-cocoa" for="cart-currency">{{ __('cart.page.currency_label') }}</label>
                        <div class="relative">
                            <select
                                id="cart-currency"
                                wire:model="currency"
                                wire:change="changeCurrency($event.target.value)"
                                class="peer cursor-pointer appearance-none border border-intense-cocoa bg-transparent px-3 py-2 pr-10 text-sm text-intense-cocoa transition-colors duration-200 hover:bg-intense-cocoa hover:text-silk-cream focus:bg-intense-cocoa focus:text-silk-cream focus:outline-none"
                                data-cart-currency
                            >
                                <option value="COP" class="bg-silk-cream text-intense-cocoa hover:bg-soft-gold hover:text-intense-cocoa">COP</option>
                                <option value="EUR" class="bg-silk-cream text-intense-cocoa hover:bg-soft-gold hover:text-intense-cocoa">EUR</option>
                            </select>
                            <svg class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-intense-cocoa transition-all duration-200 peer-hover:text-silk-cream peer-focus:text-silk-cream peer-focus:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </div>
                        @error('currency')
                            <span class="text-sm text-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Stock-changed banner (R6) --}}
                    @if ($hasStockIssue)
                        <div class="mb-6 border border-soft-gold bg-soft-sand px-4 py-3 text-sm text-intense-cocoa" data-cart-stock-banner role="alert">
                            {{ __('cart.stock_banner.message') }}
                        </div>
                    @endif

                    <ul class="divide-y divide-soft-sand" data-cart-lines>
                        @foreach ($cartView->lines as $line)
                            @php
                                $isOutOfStock = $line->stock <= 0;
                                $variantAttributes = collect([$line->color, $line->size, $line->material])->filter()->implode(' · ');
                            @endphp
                            <li
                                class="flex flex-col gap-4 bg-silk-cream py-6 text-intense-cocoa sm:flex-row sm:items-start"
                                wire:key="cart-line-{{ $line->productVariantId }}"
                                data-cart-line="{{ $line->productVariantId }}"
                            >
                                {{-- Thumbnail --}}
                                <div class="h-20 w-20 flex-shrink-0 bg-silk-cream">
                                    @if ($line->imagePath)
                                        <img
                                            src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($line->imagePath) }}"
                                            alt="{{ $line->productName }}"
                                            class="h-full w-full object-cover"
                                        >
                                    @endif
                                </div>

                                {{-- Info --}}
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-start justify-between gap-2">
                                        <div>
                                            @if ($line->productSlug)
                                                <a href="{{ route('products.show', $line->productSlug) }}" class="font-medium text-intense-cocoa hover:underline">
                                                    {{ $line->productName ?? '' }}
                                                </a>
                                            @else
                                                <p class="font-medium text-intense-cocoa">{{ $line->productName ?? '' }}</p>
                                            @endif

                                            @if ($variantAttributes !== '')
                                                <p class="mt-1 text-sm text-intense-cocoa/90">{{ $variantAttributes }}</p>
                                            @endif

                                            <p class="mt-1 text-label-caps text-intense-cocoa/90">
                                                {{ __('cart.line.sku_label') }} {{ $line->sku }}
                                            </p>

                                            @if ($isOutOfStock)
                                                <span class="mt-2 inline-block bg-soft-gold px-2.5 py-1 text-label-caps font-semibold uppercase tracking-wider text-intense-cocoa">
                                                    {{ __('cart.line.out_of_stock') }}
                                                </span>
                                            @endif
                                        </div>

                                        <p class="whitespace-nowrap text-sm font-semibold tabular-nums text-intense-cocoa" data-cart-line-subtotal="{{ $line->productVariantId }}">
                                            {{ $cartView->currency->format($line->lineSubtotal) }}
                                        </p>
                                    </div>

                                    <p class="mt-1 text-sm tabular-nums text-intense-cocoa/90">
                                        {{ $cartView->currency->format($line->unitPrice) }} {{ __('cart.line.unit_price_suffix') }}
                                    </p>

                                    {{-- Stepper + remove --}}
                                    <div class="mt-3 flex items-center gap-3">
                                        <div class="inline-flex items-center overflow-hidden border border-intense-cocoa">
                                            <button
                                                type="button"
                                                wire:click="changeQuantity({{ $line->productVariantId }}, {{ max(1, $line->quantity - 1) }})"
                                                aria-label="{{ __('cart.line.decrease_quantity') }}"
                                                class="flex h-9 w-9 items-center justify-center text-intense-cocoa transition-colors hover:bg-intense-cocoa hover:text-silk-cream disabled:cursor-not-allowed disabled:text-intense-cocoa/30 disabled:hover:bg-transparent"
                                                data-cart-decrease="{{ $line->productVariantId }}"
                                                @if ($line->quantity <= 1) disabled @endif
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5">
                                                    <path fill-rule="evenodd" d="M4 10a.75.75 0 0 1 .75-.75h10.5a.75.75 0 0 1 0 1.5H4.75A.75.75 0 0 1 4 10Z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                            <span class="flex h-9 w-10 items-center justify-center border-x border-intense-cocoa text-sm font-medium tabular-nums text-intense-cocoa" data-cart-qty="{{ $line->productVariantId }}">
                                                {{ $line->quantity }}
                                            </span>
                                            <button
                                                type="button"
                                                wire:click="changeQuantity({{ $line->productVariantId }}, {{ $line->quantity + 1 }})"
                                                aria-label="{{ __('cart.line.increase_quantity') }}"
                                                class="flex h-9 w-9 items-center justify-center text-intense-cocoa transition-colors hover:bg-soft-gold disabled:cursor-not-allowed disabled:text-intense-cocoa/30 disabled:hover:bg-transparent"
                                                data-cart-increase="{{ $line->productVariantId }}"
                                                @if ($line->quantity >= $line->stock) disabled @endif
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5">
                                                    <path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z" />
                                                </svg>
                                            </button>
                                        </div>

                                        <button
                                            type="button"
                                            wire:click="removeLine({{ $line->productVariantId }})"
                                            aria-label="{{ __('cart.line.remove') }}"
                                            class="flex h-10 w-10 items-center justify-center border border-error bg-transparent text-error transition-colors hover:bg-error hover:text-silk-cream"
                                            data-cart-remove="{{ $line->productVariantId }}"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-4 w-4" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-6">
                        <x-danger-button
                            type="button"
                            x-on:click="confirmingClear = true"
                            class="h-10 px-4"
                            data-cart-clear
                        >
                            {{ __('cart.page.clear_cart') }}
                        </x-danger-button>
                    </div>
                </div>

                {{-- RIGHT: Sticky order summary (R8) --}}
                <div class="lg:sticky lg:top-24 lg:h-fit">
                    <div class="bg-soft-sand p-6">
                        <h2 class="mb-4 font-[family-name:var(--font-chillax)] text-xl font-semibold text-intense-cocoa">
                            {{ __('cart.summary.title') }}
                        </h2>

                        <div class="space-y-2 text-sm text-intense-cocoa">
                            <div class="flex items-center justify-between">
                                <span data-cart-item-count>{{ trans_choice('cart.summary.items_count', $itemCount, ['count' => $itemCount]) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>{{ __('cart.summary.subtotal') }}</span>
                                <span class="tabular-nums" data-cart-subtotal>
                                    {{ $cartView->currency->format($cartView->total) }}
                                </span>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center justify-between border-t border-intense-cocoa/10 pt-4">
                            <span class="text-base font-semibold text-intense-cocoa">{{ __('cart.summary.total') }}</span>
                            <span class="text-xl font-semibold tabular-nums text-intense-cocoa" data-cart-total>
                                {{ $cartView->currency->format($cartView->total) }}
                            </span>
                        </div>

                        <x-primary-button
                            tag="a"
                            href="{{ route('checkout.show') }}"
                            class="mt-6 w-full"
                            data-cart-checkout
                        >
                            {{ __('cart.summary.checkout') }}
                        </x-primary-button>

                        <a
                            href="{{ route('products.index') }}"
                            class="mt-3 block text-center text-sm font-medium text-intense-cocoa underline underline-offset-2 hover:text-soft-gold"
                        >
                            {{ __('cart.summary.continue_shopping') }}
                        </a>
                    </div>
                </div>
            </div>

            {{-- Clear cart confirmation modal --}}
            <div
                x-show="confirmingClear"
                x-cloak
                x-transition.opacity
                x-on:keydown.escape.window="confirmingClear = false"
                class="fixed inset-0 z-[60] flex items-center justify-center bg-intense-cocoa/50 px-4"
            >
                <div
                    x-show="confirmingClear"
                    x-transition
                    x-on:click.outside="confirmingClear = false"
                    class="w-full max-w-sm bg-silk-cream p-6 shadow-xl"
                    role="alertdialog"
                    aria-modal="true"
                    aria-labelledby="clear-cart-modal-title"
                    data-cart-clear-modal
                >
                    <h2 id="clear-cart-modal-title" class="text-center font-[family-name:var(--font-chillax)] text-xl font-semibold text-intense-cocoa">
                        {{ __('cart.page.clear_cart') }}
                    </h2>
                    <p class="mt-2 text-center text-sm text-intense-cocoa/90">
                        {{ __('cart.page.clear_cart_confirm') }}
                    </p>
                    <div class="mt-6 flex justify-center gap-3">
                        <x-primary-button
                            type="button"
                            x-on:click="confirmingClear = false"
                            class="h-10 px-4"
                        >
                            {{ __('cart.page.clear_cart_cancel') }}
                        </x-primary-button>
                        <x-primary-button
                            type="button"
                            wire:click="clearCart"
                            x-on:click="confirmingClear = false"
                            class="h-10 px-4"
                            data-cart-clear-confirm
                        >
                            {{ __('cart.page.clear_cart') }}
                        </x-primary-button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
