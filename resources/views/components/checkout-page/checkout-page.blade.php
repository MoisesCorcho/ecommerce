<div class="py-8 lg:py-12">
    {{-- Breadcrumb (R1) --}}
    <nav aria-label="Breadcrumb" class="mx-auto mb-6 max-w-storefront px-margin-mobile text-sm text-intense-cocoa/60 lg:px-margin-desktop">
        <ol class="flex flex-wrap items-center gap-1.5">
            <li>
                <a href="{{ route('home') }}" class="transition-colors hover:text-intense-cocoa hover:underline">
                    {{ __('orders.checkout.breadcrumb_home') }}
                </a>
            </li>
            <li aria-hidden="true" class="text-intense-cocoa/30">/</li>
            <li>
                <a href="{{ route('cart.page') }}" class="transition-colors hover:text-intense-cocoa hover:underline">
                    {{ __('orders.checkout.breadcrumb_cart') }}
                </a>
            </li>
            <li aria-hidden="true" class="text-intense-cocoa/30">/</li>
            <li aria-current="page" class="font-medium text-intense-cocoa">
                {{ __('orders.checkout.breadcrumb_checkout') }}
            </li>
        </ol>
    </nav>

    <div class="mx-auto max-w-storefront px-margin-mobile lg:px-margin-desktop">
        {{-- Page title + back-to-cart (R1, R9) --}}
        <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
            <h1 class="font-[family-name:var(--font-chillax)] text-3xl font-semibold tracking-tight text-intense-cocoa">
                {{ __('orders.checkout.title') }}
            </h1>
            <a href="{{ route('cart.page') }}" class="text-sm font-medium text-intense-cocoa/70 transition-colors hover:text-intense-cocoa hover:underline">
                &larr; {{ __('orders.checkout.back_to_cart') }}
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

        {{-- Domain-error banner (R8) --}}
        @if ($errorMessage)
            <p class="mb-4 border border-error/20 bg-error/5 px-4 py-3 text-sm text-error" data-checkout-error role="alert">
                {{ $errorMessage }}
            </p>
        @endif

        @if ($preview)
            <div class="grid gap-8 lg:grid-cols-[1.6fr_1fr] lg:gap-12">
                <form id="checkout-form" wire:submit="confirm" class="space-y-6" data-checkout-form>
                    {{-- Contact section (R2) --}}
                    <section class="border border-intense-cocoa/30 bg-soft-sand p-5">
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
                    </section>

                    {{-- Address section (R3, R4, R5, R12) --}}
                    <section class="border border-intense-cocoa/30 bg-soft-sand p-5">
                        <h2 class="mb-4 text-lg font-semibold text-intense-cocoa">{{ __('orders.checkout.address_title') }}</h2>

                        @auth
                            <div class="mb-5 grid gap-3 sm:grid-cols-2" role="radiogroup" aria-label="{{ __('orders.checkout.address_title') }}">
                                <label class="relative flex cursor-pointer">
                                    <input type="radio" wire:model.live="addressMode" value="saved" class="peer sr-only" />
                                    <span class="flex w-full items-center justify-center border border-intense-cocoa/40 bg-silk-cream px-4 py-3 text-center text-sm font-medium text-intense-cocoa transition-colors peer-checked:border-intense-cocoa peer-checked:bg-intense-cocoa peer-checked:text-silk-cream">
                                        {{ __('orders.actions.use_saved_address') }}
                                    </span>
                                </label>
                                <label class="relative flex cursor-pointer">
                                    <input type="radio" wire:model.live="addressMode" value="one_shot" class="peer sr-only" />
                                    <span class="flex w-full items-center justify-center border border-intense-cocoa/40 bg-silk-cream px-4 py-3 text-center text-sm font-medium text-intense-cocoa transition-colors peer-checked:border-intense-cocoa peer-checked:bg-intense-cocoa peer-checked:text-silk-cream">
                                        {{ __('orders.actions.use_one_shot_address') }}
                                    </span>
                                </label>
                            </div>

                            @if ($addressMode === 'saved')
                                <div class="mb-5 grid gap-3 sm:grid-cols-2" role="radiogroup" aria-label="{{ __('orders.fields.shipping_address_id') }}">
                                    @foreach (auth()->user()->addresses as $address)
                                        <label class="relative flex cursor-pointer" wire:key="address-option-{{ $address->id }}">
                                            <input type="radio" wire:model.live="shippingAddressId" value="{{ $address->id }}" class="peer sr-only" />
                                            <span class="flex w-full flex-col gap-1 border border-intense-cocoa/40 bg-silk-cream px-4 py-3 text-sm text-intense-cocoa transition-colors peer-checked:border-intense-cocoa peer-checked:bg-intense-cocoa peer-checked:text-silk-cream">
                                                <span class="font-medium">{{ $address->label ? $address->label.' · ' : '' }}{{ $address->full_name }}</span>
                                                <span class="text-xs opacity-80">{{ $address->city }}</span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                                @error('shippingAddressId')
                                    <p class="mb-5 text-sm text-error">{{ $message }}</p>
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
                    </section>

                    {{-- Shipping method (R5, R12) — single pre-selected, non-interactive standard shipping card --}}
                    <section class="border border-intense-cocoa/30 bg-soft-sand p-5">
                        <h2 class="mb-4 text-lg font-semibold text-intense-cocoa">{{ __('orders.checkout.shipping_method') }}</h2>
                        <div class="flex items-center justify-between border border-intense-cocoa/40 bg-intense-cocoa/5 px-4 py-3 text-sm text-intense-cocoa">
                            <span class="flex items-center gap-2 font-medium">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-5 w-5" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                                </svg>
                                {{ __('orders.shipping.standard') }}
                            </span>
                            <span class="tabular-nums">{{ number_format($preview['shippingCost']) }} {{ $preview['currency'] }}</span>
                        </div>
                    </section>

                    {{-- Coupon section --}}
                    <section class="border border-intense-cocoa/30 bg-soft-sand p-5">
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
                            <button
                                type="button"
                                wire:click="applyCoupon"
                                class="border border-intense-cocoa px-4 py-2 text-sm font-semibold text-intense-cocoa transition-colors duration-200 hover:bg-intense-cocoa hover:text-silk-cream"
                                data-checkout-apply-coupon
                            >
                                {{ __('orders.fields.coupon_code') }}
                            </button>
                        </div>
                        @error('couponCode')
                            <p class="mt-1 text-sm text-error">{{ $message }}</p>
                        @enderror
                    </section>

                    {{-- Notes section (R12) --}}
                    <section class="border border-intense-cocoa/30 bg-soft-sand p-5">
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
                    </section>
                </form>

                {{-- Sticky order summary (R6, R9, R12) --}}
                <aside class="lg:sticky lg:top-24 lg:h-fit">
                    <div class="bg-soft-sand p-6" data-checkout-summary>
                        <h2 class="mb-4 font-[family-name:var(--font-chillax)] text-xl font-semibold text-intense-cocoa">
                            {{ __('orders.checkout.summary_title') }}
                        </h2>
                        @php
                            $currencyEnum = \App\Enums\Commerce\CurrencyEnum::tryFrom((string) ($preview['currency'] ?? ''));
                        @endphp
                        <ul class="mb-4 space-y-3 text-sm text-intense-cocoa">
                            @foreach ($preview['lines'] as $line)
                                <li class="flex justify-between gap-3 border-b border-intense-cocoa/10 pb-2">
                                    <span>
                                        {{ $line['productName'] }}
                                        @if ($line['variantLabel'])
                                            <span class="text-intense-cocoa/90">({{ $line['variantLabel'] }})</span>
                                        @endif
                                        × {{ $line['quantity'] }}
                                    </span>
                                    <span class="font-medium tabular-nums">{{ $currencyEnum?->format($line['lineSubtotal']) ?? number_format($line['lineSubtotal']).' '.$preview['currency'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <dl class="space-y-2 text-sm text-intense-cocoa">
                            <div class="flex justify-between">
                                <dt>{{ __('orders.fields.subtotal') }}</dt>
                                <dd class="tabular-nums">{{ $currencyEnum?->format($preview['subtotal']) ?? number_format($preview['subtotal']).' '.$preview['currency'] }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt>{{ __('orders.shipping.standard') }}</dt>
                                <dd class="tabular-nums">{{ $currencyEnum?->format($preview['shippingCost']) ?? number_format($preview['shippingCost']).' '.$preview['currency'] }}</dd>
                            </div>
                            @if (($preview['discount'] ?? 0) > 0)
                                <div class="flex justify-between text-success" data-checkout-discount>
                                    <dt>{{ __('orders.fields.discount') }}</dt>
                                    <dd>−{{ $currencyEnum?->format($preview['discount']) ?? number_format($preview['discount']).' '.$preview['currency'] }}</dd>
                                </div>
                            @endif
                            <div class="flex justify-between border-t border-intense-cocoa/10 pt-3 text-base font-semibold">
                                <dt>{{ __('orders.fields.total') }}</dt>
                                <dd class="text-xl tabular-nums" data-checkout-total>{{ $currencyEnum?->format($preview['total']) ?? number_format($preview['total']).' '.$preview['currency'] }}</dd>
                            </div>
                        </dl>

                        <button
                            type="submit"
                            form="checkout-form"
                            class="mt-6 flex h-12 w-full items-center justify-center bg-intense-cocoa text-sm font-semibold text-silk-cream transition-colors duration-200 hover:bg-soft-gold hover:text-intense-cocoa disabled:cursor-not-allowed disabled:bg-intense-cocoa/40"
                            data-checkout-submit
                            wire:loading.attr="disabled"
                        >
                            {{ __('orders.actions.confirm') }}
                        </button>

                        <p class="mt-3 flex items-center justify-center gap-1.5 text-xs text-intense-cocoa/90">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-3.5 w-3.5 flex-shrink-0" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                            </svg>
                            <span><span class="font-medium text-intense-cocoa">{{ __('orders.checkout.secure_badge') }}:</span> {{ __('orders.checkout.secure_note') }}</span>
                        </p>
                    </div>
                </aside>
            </div>
        @endif
    </div>
</div>
