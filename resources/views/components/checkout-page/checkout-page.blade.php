<div class="py-8 lg:py-12">
    {{-- Breadcrumb (R1) --}}
    <x-breadcrumb.breadcrumb :items="[
        ['label' => __('orders.checkout.breadcrumb_home'), 'href' => route('home')],
        ['label' => __('orders.checkout.breadcrumb_cart'), 'href' => route('cart.page')],
        ['label' => __('orders.checkout.breadcrumb_checkout')],
    ]"></x-breadcrumb.breadcrumb>

    <div class="mx-auto max-w-storefront px-margin-mobile lg:px-margin-desktop">
        {{-- Page title + back-to-cart (R1, R9) --}}
        <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
            <h1 class="font-[family-name:var(--font-chillax)] text-3xl font-semibold tracking-tight text-intense-cocoa">
                {{ __('orders.checkout.title') }}
            </h1>
            <a href="{{ route('cart.page') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-intense-cocoa/70 transition-colors hover:text-intense-cocoa hover:underline">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4" aria-hidden="true">
                    <path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
                </svg>
                {{ __('orders.checkout.back_to_cart') }}
            </a>
        </div>

        {{-- Decorative stepper (R7) — non-interactive, hidden from assistive tech; real structure lives in section headings below --}}
        <ol aria-hidden="true" class="mb-8 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm">
            <li class="flex cursor-default items-center gap-2 text-intense-cocoa">
                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-intense-cocoa text-xs font-semibold text-silk-cream">1</span>
                <span class="font-medium">{{ __('orders.checkout.stepper.contact') }}</span>
            </li>
            <li class="flex cursor-default items-center gap-2 text-intense-cocoa">
                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-intense-cocoa text-xs font-semibold text-silk-cream">2</span>
                <span class="font-medium">{{ __('orders.checkout.stepper.address') }}</span>
            </li>
            <li class="flex cursor-default items-center gap-2 text-intense-cocoa">
                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-intense-cocoa text-xs font-semibold text-silk-cream">3</span>
                <span class="font-medium">{{ __('orders.checkout.stepper.shipping') }}</span>
            </li>
            <li class="flex cursor-default items-center gap-2 text-intense-cocoa/90">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-4 w-4" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
                <span class="flex flex-col leading-tight">
                    <span class="font-medium">{{ __('orders.checkout.stepper.payment') }}</span>
                    <span class="text-xs">{{ __('orders.checkout.stepper.payment_hint') }}</span>
                </span>
            </li>
        </ol>



        @if ($preview)
            <div class="grid gap-8 lg:grid-cols-[1.6fr_1fr] lg:gap-12">
                <form id="checkout-form" wire:submit="confirm" class="space-y-6" data-checkout-form>
                    {{-- Contact section (R2) --}}
                    <x-section-card.section-card tag="section">
                        <h2 class="mb-4 text-lg font-semibold text-intense-cocoa">{{ __('orders.checkout.contact') }}</h2>
                        <div class="grid gap-4 sm:grid-cols-2">
                            @include('components.checkout-page.partials.text-field', [
                                'field' => 'firstName',
                                'label' => 'orders.fields.first_name',
                                'wireModel' => 'wire:model="firstName"',
                                'placeholder' => 'orders.checkout.placeholders.first_name',
                            ])
                            @include('components.checkout-page.partials.text-field', [
                                'field' => 'lastName',
                                'label' => 'orders.fields.last_name',
                                'wireModel' => 'wire:model="lastName"',
                                'placeholder' => 'orders.checkout.placeholders.last_name',
                            ])
                            @include('components.checkout-page.partials.text-field', [
                                'field' => 'email',
                                'label' => 'orders.fields.email',
                                'wireModel' => 'wire:model="email"',
                                'type' => 'email',
                                'placeholder' => 'orders.checkout.placeholders.email',
                            ])
                            @include('components.checkout-page.partials.text-field', [
                                'field' => 'phone',
                                'label' => 'orders.fields.phone',
                                'wireModel' => 'wire:model="phone"',
                                'placeholder' => 'orders.checkout.placeholders.phone',
                            ])
                        </div>
                    </x-section-card.section-card>

                    {{-- Address section (R3, R4, R5, R12) --}}
                    <x-section-card.section-card tag="section">
                        <h2 class="mb-4 text-lg font-semibold text-intense-cocoa">{{ __('orders.checkout.address_title') }}</h2>

                        @auth
                            <div class="mb-5 grid gap-3 sm:grid-cols-2" role="radiogroup" aria-label="{{ __('orders.checkout.address_title') }}">
                                <label class="relative flex cursor-pointer">
                                    <input type="radio" wire:model.live="addressMode" value="saved" class="peer sr-only" />
                                    <span class="flex h-11 w-full items-center justify-center border border-intense-cocoa bg-transparent px-4 text-sm font-semibold text-intense-cocoa transition-all duration-200 hover:border-intense-cocoa hover:bg-intense-cocoa/15 peer-checked:border-intense-cocoa peer-checked:bg-intense-cocoa peer-checked:text-silk-cream peer-checked:[&_.radio-circle]:border-soft-gold peer-checked:[&_.radio-circle]:bg-soft-gold peer-checked:[&_.radio-icon]:opacity-100 focus:outline-none">
                                        <span class="radio-circle mr-2.5 flex h-4.5 w-4.5 shrink-0 items-center justify-center rounded-full border-2 border-intense-cocoa transition-all duration-200">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="radio-icon h-3 w-3 text-intense-cocoa opacity-0 transition-opacity duration-200" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                        <span>{{ __('orders.actions.use_saved_address') }}</span>
                                    </span>
                                </label>
                                <label class="relative flex cursor-pointer">
                                    <input type="radio" wire:model.live="addressMode" value="one_shot" class="peer sr-only" />
                                    <span class="flex h-11 w-full items-center justify-center border border-intense-cocoa bg-transparent px-4 text-sm font-semibold text-intense-cocoa transition-all duration-200 hover:border-intense-cocoa hover:bg-intense-cocoa/15 peer-checked:border-intense-cocoa peer-checked:bg-intense-cocoa peer-checked:text-silk-cream peer-checked:[&_.radio-circle]:border-soft-gold peer-checked:[&_.radio-circle]:bg-soft-gold peer-checked:[&_.radio-icon]:opacity-100 focus:outline-none">
                                        <span class="radio-circle mr-2.5 flex h-4.5 w-4.5 shrink-0 items-center justify-center rounded-full border-2 border-intense-cocoa transition-all duration-200">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="radio-icon h-3 w-3 text-intense-cocoa opacity-0 transition-opacity duration-200" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                        <span>{{ __('orders.actions.use_one_shot_address') }}</span>
                                    </span>
                                </label>
                            </div>

                            @if ($addressMode === 'saved')
                                <div class="mb-5 grid gap-4 sm:grid-cols-2" role="radiogroup" aria-label="{{ __('orders.fields.shipping_address_id') }}">
                                    @foreach (auth()->user()->addresses as $address)
                                        <label class="relative flex cursor-pointer" wire:key="address-option-{{ $address->id }}">
                                            <input type="radio" wire:model.live="shippingAddressId" value="{{ $address->id }}" class="peer sr-only" />
                                            <div class="flex w-full flex-col justify-between border border-intense-cocoa bg-soft-sand p-6 text-sm text-intense-cocoa shadow-ambient transition-all duration-200 hover:border-soft-gold peer-checked:border-soft-gold peer-checked:bg-soft-sand peer-checked:ring-1 peer-checked:ring-soft-gold">
                                                <div>
                                                    {{-- Top status row: Label badge + Default star --}}
                                                    <div class="mb-4 flex items-center justify-between gap-2 border-b border-intense-cocoa/30 pb-3">
                                                        <span class="inline-flex items-center border border-intense-cocoa bg-intense-cocoa px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-widest text-silk-cream">
                                                            {{ $address->label ? strtoupper($address->label) : __('orders.fields.shipping_address_id') }}
                                                        </span>
                                                        @if ($address->is_default)
                                                            <span class="inline-flex h-6 items-center gap-1 border border-soft-gold/60 bg-silk-cream px-2.5 text-[10px] font-semibold text-intense-cocoa">
                                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3 w-3 text-soft-gold" aria-hidden="true">
                                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.958a1 1 0 0 0 .95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.367 2.446a1 1 0 0 0-.363 1.118l1.287 3.957c.3.922-.755 1.688-1.538 1.118l-3.367-2.445a1 1 0 0 0-1.176 0l-3.367 2.445c-.783.57-1.838-.196-1.538-1.118l1.287-3.957a1 1 0 0 0-.363-1.118L2.63 9.385c-.783-.57-.38-1.81.588-1.81h4.163a1 1 0 0 0 .95-.69l1.286-3.958Z" />
                                                                </svg>
                                                                {{ __('account.addresses.default_badge') }}
                                                            </span>
                                                        @endif
                                                    </div>

                                                    {{-- Address info --}}
                                                    <div class="space-y-1.5">
                                                        <h3 class="font-[family-name:var(--font-chillax)] text-base font-semibold text-intense-cocoa">
                                                            {{ $address->full_name }}
                                                        </h3>
                                                        <p class="text-xs font-medium leading-relaxed text-intense-cocoa">
                                                            {{ $address->address_line_1 }}
                                                            @if ($address->address_line_2)
                                                                , {{ $address->address_line_2 }}
                                                            @endif
                                                        </p>
                                                    </div>
                                                </div>

                                                {{-- Bottom details chips --}}
                                                <div class="mt-4 flex flex-wrap gap-1.5 border-t border-intense-cocoa/30 pt-3">
                                                    <span class="inline-flex items-center gap-1 border border-intense-cocoa bg-silk-cream px-2.5 py-1 text-xs font-medium text-intense-cocoa">
                                                        <span>{{ $address->city }}, {{ $address->state }}</span>
                                                        @if ($address->postal_code)
                                                            <span class="text-intense-cocoa/70">({{ $address->postal_code }})</span>
                                                        @endif
                                                    </span>
                                                    @if ($address->phone)
                                                        <span class="inline-flex items-center gap-1 border border-intense-cocoa bg-silk-cream px-2.5 py-1 text-xs font-medium text-intense-cocoa">
                                                            <span>{{ $address->phone }}</span>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                                @error('shippingAddressId')
                                    <p class="mb-5 text-sm font-semibold text-error">{{ $message }}</p>
                                @enderror
                            @endif
                        @endauth

                        @if (! auth()->check() || $addressMode === 'one_shot')
                            <div class="grid gap-4 sm:grid-cols-2">
                                @include('components.checkout-page.partials.text-field', [
                                    'field' => 'shippingFullName',
                                    'label' => 'orders.fields.shipping_full_name',
                                    'wireModel' => 'wire:model="shippingFullName"',
                                    'colSpan' => 'sm:col-span-2',
                                ])
                                @include('components.checkout-page.partials.text-field', [
                                    'field' => 'shippingPhone',
                                    'label' => 'orders.fields.shipping_phone',
                                    'wireModel' => 'wire:model="shippingPhone"',
                                    'placeholder' => 'orders.checkout.placeholders.phone',
                                ])
                                @include('components.checkout-page.partials.text-field', [
                                    'field' => 'shippingCountry',
                                    'label' => 'orders.fields.shipping_country',
                                    'wireModel' => 'wire:model="shippingCountry"',
                                    'inputAttributes' => 'maxlength="2"',
                                ])
                                @include('components.checkout-page.partials.text-field', [
                                    'field' => 'shippingAddressLine1',
                                    'label' => 'orders.fields.shipping_address_line_1',
                                    'wireModel' => 'wire:model="shippingAddressLine1"',
                                    'placeholder' => 'orders.checkout.placeholders.address_line_1',
                                    'colSpan' => 'sm:col-span-2',
                                ])
                                @include('components.checkout-page.partials.text-field', [
                                    'field' => 'shippingAddressLine2',
                                    'label' => 'orders.fields.shipping_address_line_2',
                                    'wireModel' => 'wire:model="shippingAddressLine2"',
                                    'colSpan' => 'sm:col-span-2',
                                ])
                                @include('components.checkout-page.partials.text-field', [
                                    'field' => 'shippingCity',
                                    'label' => 'orders.fields.shipping_city',
                                    'wireModel' => 'wire:model="shippingCity"',
                                    'placeholder' => 'orders.checkout.placeholders.city',
                                ])
                                @include('components.checkout-page.partials.text-field', [
                                    'field' => 'shippingState',
                                    'label' => 'orders.fields.shipping_state',
                                    'wireModel' => 'wire:model="shippingState"',
                                ])
                                @include('components.checkout-page.partials.text-field', [
                                    'field' => 'shippingPostalCode',
                                    'label' => 'orders.fields.shipping_postal_code',
                                    'wireModel' => 'wire:model="shippingPostalCode"',
                                    'placeholder' => 'orders.checkout.placeholders.postal_code',
                                ])
                            </div>
                        @endif
                    </x-section-card.section-card>

                    {{-- Shipping method (R5, R12) — single pre-selected, non-interactive standard shipping card --}}
                    <x-section-card.section-card tag="section">
                        <h2 class="mb-4 text-lg font-semibold text-intense-cocoa">{{ __('orders.checkout.shipping_method') }}</h2>
                        <div class="flex items-center justify-between border border-intense-cocoa/40 bg-intense-cocoa/5 px-4 py-3 text-sm text-intense-cocoa">
                            <span class="flex items-center gap-2 font-medium">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-5 w-5" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                                </svg>
                                {{ __('orders.shipping.standard') }}
                            </span>
                            <span class="tabular-nums">{{ \App\Enums\Commerce\CurrencyEnum::tryFrom((string) ($preview['currency'] ?? ''))?->format($preview['shippingCost']) ?? number_format($preview['shippingCost']).' '.$preview['currency'] }}</span>
                        </div>
                    </x-section-card.section-card>

                    {{-- Coupon section --}}
                    <x-section-card.section-card tag="section">
                        <h2 class="mb-4 text-lg font-semibold text-intense-cocoa">{{ __('orders.fields.coupon_code') }}</h2>
                        <div class="flex flex-col gap-3 sm:flex-row">
                            <input
                                id="couponCode"
                                type="text"
                                wire:model="couponCode"
                                maxlength="32"
                                class="w-full border border-intense-cocoa/40 bg-silk-cream px-3 py-2 text-sm uppercase text-intense-cocoa placeholder:text-intense-cocoa/50 transition-colors focus:border-intense-cocoa focus:outline-none"
                                data-checkout-coupon
                                autocomplete="off"
                            />
                            <x-secondary-button
                                type="button"
                                wire:click="applyCoupon"
                                class="h-10 px-4 shrink-0"
                                data-checkout-apply-coupon
                            >
                                {{ __('orders.fields.coupon_code') }}
                            </x-secondary-button>
                        </div>
                        @error('couponCode')
                            <p class="mt-2 text-sm font-medium text-error">{{ $message }}</p>
                        @enderror
                        @if ($errorMessage)
                            <p class="mt-2 text-sm font-medium text-error" data-checkout-coupon-error>{{ $errorMessage }}</p>
                        @endif
                    </x-section-card.section-card>

                    {{-- Notes section (R12) --}}
                    <x-section-card.section-card tag="section">
                        <h2 class="mb-4 text-lg font-semibold text-intense-cocoa">{{ __('orders.sections.notes') }}</h2>
                        <textarea
                            wire:model="customerNotes"
                            rows="3"
                            class="w-full border border-intense-cocoa/40 bg-silk-cream px-3 py-2 text-sm text-intense-cocoa placeholder:text-intense-cocoa/80 transition-colors focus:border-intense-cocoa focus:outline-none"
                            data-checkout-notes
                        ></textarea>
                        @error('customerNotes')
                            <p class="mt-1 text-sm text-error">{{ $message }}</p>
                        @enderror
                    </x-section-card.section-card>
                </form>

                {{-- Sticky order summary (R6, R9, R12) --}}
                <aside class="lg:sticky lg:top-24 lg:h-fit">
                    <x-section-card.section-card data-checkout-summary>
                        <h2 class="mb-4 font-[family-name:var(--font-chillax)] text-xl font-semibold text-intense-cocoa">
                            {{ __('orders.checkout.summary_title') }}
                        </h2>
                        @php
                            $currencyEnum = \App\Enums\Commerce\CurrencyEnum::tryFrom((string) ($preview['currency'] ?? ''));
                        @endphp
                        <ul class="mb-4 space-y-3 text-sm text-intense-cocoa">
                            @foreach ($preview['lines'] as $line)
                                <li class="flex justify-between gap-3 border-b border-intense-cocoa/30 pb-2.5">
                                    <span class="flex flex-col gap-0.5">
                                        <span class="font-semibold text-intense-cocoa">{{ $line['productName'] }}</span>
                                        @if ($line['variantLabel'] || $line['quantity'])
                                            <span class="text-xs font-medium text-intense-cocoa">
                                                @if ($line['variantLabel'])
                                                    <span>{{ $line['variantLabel'] }}</span>
                                                @endif
                                                @if ($line['variantLabel'] && $line['quantity'])
                                                    <span class="mx-1.5 text-intense-cocoa/70">—</span>
                                                @endif
                                                @if ($line['quantity'])
                                                    <span class="font-semibold text-intense-cocoa">× {{ $line['quantity'] }}</span>
                                                @endif
                                            </span>
                                        @endif
                                    </span>
                                    <span class="shrink-0 font-semibold tabular-nums text-intense-cocoa">{{ $currencyEnum?->format($line['lineSubtotal']) ?? number_format($line['lineSubtotal']).' '.$preview['currency'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <dl class="space-y-2.5 text-sm text-intense-cocoa">
                            <div class="flex justify-between">
                                <dt class="font-medium text-intense-cocoa">{{ __('orders.fields.subtotal') }}</dt>
                                <dd class="font-semibold tabular-nums text-intense-cocoa">{{ $currencyEnum?->format($preview['subtotal']) ?? number_format($preview['subtotal']).' '.$preview['currency'] }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="font-medium text-intense-cocoa">{{ __('orders.shipping.standard') }}</dt>
                                <dd class="font-semibold tabular-nums text-intense-cocoa">{{ $currencyEnum?->format($preview['shippingCost']) ?? number_format($preview['shippingCost']).' '.$preview['currency'] }}</dd>
                            </div>
                            @if (($preview['thresholdDiscount'] ?? 0) > 0)
                                <div class="flex justify-between font-medium text-terracotta" data-checkout-threshold-discount>
                                    <dt>{{ __('orders.fields.threshold_discount') }}</dt>
                                    <dd class="font-semibold tabular-nums">−{{ $currencyEnum?->format($preview['thresholdDiscount']) ?? number_format($preview['thresholdDiscount']).' '.$preview['currency'] }}</dd>
                                </div>
                            @endif
                            @if (($preview['discount'] ?? 0) > 0)
                                <div class="flex justify-between font-medium text-success" data-checkout-discount>
                                    <dt>{{ __('orders.fields.discount') }}</dt>
                                    <dd class="font-semibold tabular-nums">−{{ $currencyEnum?->format($preview['discount']) ?? number_format($preview['discount']).' '.$preview['currency'] }}</dd>
                                </div>
                            @endif
                            <div class="flex justify-between border-t border-intense-cocoa/30 pt-3 text-base font-semibold text-intense-cocoa">
                                <dt>{{ __('orders.fields.total') }}</dt>
                                <dd class="text-xl font-bold tabular-nums text-intense-cocoa" data-checkout-total>{{ $currencyEnum?->format($preview['total']) ?? number_format($preview['total']).' '.$preview['currency'] }}</dd>
                            </div>
                        </dl>

                        <x-primary-button
                            type="submit"
                            form="checkout-form"
                            class="mt-6 w-full disabled:bg-intense-cocoa/40"
                            data-checkout-submit
                            wire:loading.attr="disabled"
                        >
                            {{ __('orders.actions.confirm') }}
                        </x-primary-button>

                        <p class="mt-3 flex items-center justify-center gap-1.5 text-xs text-intense-cocoa/90">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-3.5 w-3.5 flex-shrink-0" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                            </svg>
                            <span><span class="font-medium text-intense-cocoa">{{ __('orders.checkout.secure_badge') }}:</span> {{ __('orders.checkout.secure_note') }}</span>
                        </p>
                    </x-section-card.section-card>

                    {{-- Coupon success notification displayed immediately below order summary --}}
                    @if (($preview['discount'] ?? 0) > 0)
                        <div
                            class="mt-4 border border-success/40 bg-success/10 p-4 text-sm text-success shadow-sm"
                            role="status"
                            data-checkout-coupon-success
                        >
                            <div class="flex items-start gap-2.5">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5 shrink-0 text-success" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />
                                </svg>
                                <div class="flex-1 font-medium leading-snug">
                                    {{ __('coupons.ui.applied_successfully') }}
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Error message displayed immediately below order summary --}}
                    @if ($errorMessage)
                        <div
                            x-data="{ show: true }"
                            x-init="$el.scrollIntoView({ behavior: 'smooth', block: 'nearest' })"
                            class="mt-4 border border-error/40 bg-error/10 p-4 text-sm text-error shadow-sm"
                            role="alert"
                            data-checkout-summary-error
                        >
                            <div class="flex items-start gap-2.5">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5 shrink-0 text-error" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM8.28 7.22a.75.75 0 0 0-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 1 0 1.06 1.06L10 11.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L11.06 10l1.72-1.72a.75.75 0 0 0-1.06-1.06L10 8.94 8.28 7.22Z" clip-rule="evenodd" />
                                </svg>
                                <div class="flex-1 font-medium leading-snug">
                                    {{ $errorMessage }}
                                </div>
                            </div>
                        </div>
                    @endif
                </aside>
            </div>
        @endif
    </div>
</div>
