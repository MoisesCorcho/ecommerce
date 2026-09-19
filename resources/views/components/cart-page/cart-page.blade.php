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
        <x-page-header title="{{ __('cart.page.title') }}" />

        @if ($statusMessage)
            <x-alert type="success" data-cart-status class="mb-4">
                {{ $statusMessage }}
            </x-alert>
        @endif

        @if ($errorMessage)
            <x-alert type="error" data-cart-error class="mb-4">
                {{ $errorMessage }}
            </x-alert>
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

                    {{-- Stock-changed banner (R6) --}}
                    @if ($hasStockIssue)
                        <div class="mb-6 border border-soft-gold bg-soft-sand px-4 py-3 text-sm text-intense-cocoa" data-cart-stock-banner role="alert">
                            {{ __('cart.stock_banner.message') }}
                        </div>
                    @endif

                    {{-- Threshold discount progress banner (F17) --}}
                    @if ($cartView->thresholdMinAmount > 0)
                        @php
                            $progressPercent = min(100, (int) round(($cartView->subtotal / $cartView->thresholdMinAmount) * 100));
                        @endphp
                        <div class="mb-6 bg-soft-sand p-4 text-intense-cocoa" data-cart-threshold-banner>
                            <div class="mb-2 flex items-center justify-between text-xs font-medium sm:text-sm">
                                @if ($cartView->thresholdReached)
                                    <span class="flex items-center gap-1.5 font-bold text-intense-cocoa">
                                        <svg class="h-4 w-4 text-soft-gold" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        {{ __('cart.threshold.unlocked') }}
                                    </span>
                                    <span class="text-xs font-bold uppercase tracking-wider text-soft-gold">-10%</span>
                                @else
                                    <span>
                                        {{ __('cart.threshold.progress', ['amount' => $cartView->currency->format($cartView->remainingForThreshold)]) }}
                                    </span>
                                    <span class="tabular-nums font-semibold">{{ $progressPercent }}%</span>
                                @endif
                            </div>

                            {{-- Progress Bar --}}
                            <div class="h-2 w-full overflow-hidden bg-soft-sand">
                                <div
                                    class="h-full bg-soft-gold transition-all duration-500 ease-out"
                                    style="width: {{ $progressPercent }}%"
                                ></div>
                            </div>
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
                                            src="/storage/{{ $line->imagePath }}"
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
                                    {{ $cartView->currency->format($cartView->subtotal) }}
                                </span>
                            </div>
                            @if ($cartView->thresholdDiscountAmount > 0)
                                <div class="flex items-center justify-between font-medium text-success" data-cart-threshold-discount>
                                    <span>{{ __('cart.summary.threshold_discount') }}</span>
                                    <span class="tabular-nums">
                                        −{{ $cartView->currency->format($cartView->thresholdDiscountAmount) }}
                                    </span>
                                </div>
                            @endif
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
            <x-modal
                name="confirmingClear"
                title=""
                title-id="clear-cart-modal-title"
                max-width="sm"
                data-cart-clear-modal
            >
                <div class="space-y-5 text-center">
                    {{-- Warning Icon Badge (Square/rounded-none border) --}}
                    <div class="mx-auto flex h-12 w-12 items-center justify-center border border-error/30 bg-error/10 text-error">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-6 w-6" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                    </div>

                    {{-- Title & Body --}}
                    <div class="space-y-2">
                        <h2 id="clear-cart-modal-title" class="font-[family-name:var(--font-chillax)] text-xl font-semibold text-intense-cocoa">
                            {{ __('cart.page.clear_cart') }}
                        </h2>
                        <p class="text-sm leading-relaxed text-intense-cocoa/80">
                            {{ __('cart.page.clear_cart_confirm') }}
                        </p>
                    </div>

                    {{-- Symmetrical Action Buttons Grid --}}
                    <div class="grid grid-cols-2 gap-3 pt-2">
                        <button
                            type="button"
                            x-on:click="confirmingClear = false"
                            class="inline-flex h-10 cursor-pointer items-center justify-center border border-intense-cocoa text-xs font-semibold uppercase tracking-wider text-intense-cocoa transition-all duration-200 hover:bg-intense-cocoa hover:text-silk-cream"
                        >
                            {{ __('cart.page.clear_cart_cancel') }}
                        </button>
                        <button
                            type="button"
                            wire:click="clearCart"
                            x-on:click="confirmingClear = false"
                            class="inline-flex h-10 cursor-pointer items-center justify-center border border-error bg-transparent text-xs font-semibold uppercase tracking-wider text-error transition-all duration-200 hover:bg-error hover:text-silk-cream"
                            data-cart-clear-confirm
                        >
                            {{ __('cart.page.clear_cart') }}
                        </button>
                    </div>
                </div>
            </x-modal>
        @endif
    </div>
</div>
