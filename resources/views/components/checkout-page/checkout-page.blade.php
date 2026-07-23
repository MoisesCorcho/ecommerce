<div>
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight">{{ __('orders.checkout.title') }}</h1>
            <p class="mt-1 text-sm text-stone-600">
                Scaffold funcional (sin marca). Confirmá el pedido sin pago (F05).
            </p>
        </div>
        <a href="{{ route('cart.page') }}" class="text-sm text-stone-600 hover:text-stone-900 hover:underline">
            ← Volver al carrito
        </a>
    </div>

    @if ($errorMessage)
        <p class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" data-checkout-error>
            {{ $errorMessage }}
        </p>
    @endif

    @if ($preview)
        <div class="grid gap-8 lg:grid-cols-5">
            <form wire:submit="confirm" class="space-y-6 lg:col-span-3" data-checkout-form>
                <section class="rounded-xl border border-stone-200 bg-white p-5">
                    <h2 class="mb-4 text-lg font-semibold">{{ __('orders.checkout.contact') }}</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium" for="firstName">{{ __('orders.fields.first_name') }}</label>
                            <input id="firstName" type="text" wire:model="firstName" class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm" />
                            @error('firstName') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium" for="lastName">{{ __('orders.fields.last_name') }}</label>
                            <input id="lastName" type="text" wire:model="lastName" class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm" />
                            @error('lastName') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium" for="email">{{ __('orders.fields.email') }}</label>
                            <input id="email" type="email" wire:model="email" class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm" />
                            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium" for="phone">{{ __('orders.fields.phone') }}</label>
                            <input id="phone" type="text" wire:model="phone" class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm" />
                            @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </section>

                <section class="rounded-xl border border-stone-200 bg-white p-5">
                    <h2 class="mb-4 text-lg font-semibold">{{ __('orders.checkout.shipping') }}</h2>

                    @auth
                        <div class="mb-4 flex flex-wrap gap-4 text-sm">
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" wire:model.live="addressMode" value="saved" />
                                {{ __('orders.actions.use_saved_address') }}
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" wire:model.live="addressMode" value="one_shot" />
                                {{ __('orders.actions.use_one_shot_address') }}
                            </label>
                        </div>

                        @if ($addressMode === 'saved')
                            <div class="mb-4">
                                <label class="mb-1 block text-sm font-medium" for="shippingAddressId">{{ __('orders.fields.shipping_address_id') }}</label>
                                <select id="shippingAddressId" wire:model.live="shippingAddressId" class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm">
                                    <option value="">—</option>
                                    @foreach (auth()->user()->addresses as $address)
                                        <option value="{{ $address->id }}">
                                            {{ $address->label ? $address->label.' · ' : '' }}{{ $address->full_name }} — {{ $address->city }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('shippingAddressId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        @endif
                    @endauth

                    @if (! auth()->check() || $addressMode === 'one_shot')
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-sm font-medium" for="shippingFullName">{{ __('orders.fields.shipping_full_name') }}</label>
                                <input id="shippingFullName" type="text" wire:model="shippingFullName" class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm" />
                                @error('shippingFullName') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium" for="shippingPhone">{{ __('orders.fields.shipping_phone') }}</label>
                                <input id="shippingPhone" type="text" wire:model="shippingPhone" class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm" />
                                @error('shippingPhone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium" for="shippingCountry">{{ __('orders.fields.shipping_country') }}</label>
                                <input id="shippingCountry" type="text" wire:model="shippingCountry" maxlength="2" class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm uppercase" />
                                @error('shippingCountry') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-sm font-medium" for="shippingAddressLine1">{{ __('orders.fields.shipping_address_line_1') }}</label>
                                <input id="shippingAddressLine1" type="text" wire:model="shippingAddressLine1" class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm" />
                                @error('shippingAddressLine1') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-sm font-medium" for="shippingAddressLine2">{{ __('orders.fields.shipping_address_line_2') }}</label>
                                <input id="shippingAddressLine2" type="text" wire:model="shippingAddressLine2" class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm" />
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium" for="shippingCity">{{ __('orders.fields.shipping_city') }}</label>
                                <input id="shippingCity" type="text" wire:model="shippingCity" class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm" />
                                @error('shippingCity') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium" for="shippingState">{{ __('orders.fields.shipping_state') }}</label>
                                <input id="shippingState" type="text" wire:model="shippingState" class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm" />
                                @error('shippingState') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium" for="shippingPostalCode">{{ __('orders.fields.shipping_postal_code') }}</label>
                                <input id="shippingPostalCode" type="text" wire:model="shippingPostalCode" class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm" />
                            </div>
                        </div>
                    @endif
                </section>

                <section class="rounded-xl border border-stone-200 bg-white p-5">
                    <h2 class="mb-4 text-lg font-semibold">{{ __('orders.fields.coupon_code') }}</h2>
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <input
                            id="couponCode"
                            type="text"
                            wire:model="couponCode"
                            maxlength="32"
                            class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm uppercase"
                            data-checkout-coupon
                            autocomplete="off"
                        />
                        <button
                            type="button"
                            wire:click="applyCoupon"
                            class="rounded-md border border-stone-300 px-4 py-2 text-sm font-medium text-stone-800 hover:bg-stone-50"
                            data-checkout-apply-coupon
                        >
                            {{ __('orders.fields.coupon_code') }}
                        </button>
                    </div>
                    @error('couponCode') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </section>

                <section class="rounded-xl border border-stone-200 bg-white p-5">
                    <h2 class="mb-4 text-lg font-semibold">{{ __('orders.sections.notes') }}</h2>
                    <textarea wire:model="customerNotes" rows="3" class="w-full rounded-md border border-stone-300 px-3 py-2 text-sm" data-checkout-notes></textarea>
                    @error('customerNotes') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </section>

                <button
                    type="submit"
                    class="w-full rounded-lg bg-stone-900 px-4 py-3 text-sm font-semibold text-white hover:bg-stone-800"
                    data-checkout-submit
                    wire:loading.attr="disabled"
                >
                    {{ __('orders.actions.confirm') }}
                </button>
            </form>

            <aside class="lg:col-span-2">
                <div class="rounded-xl border border-stone-200 bg-white p-5" data-checkout-summary>
                    <h2 class="mb-4 text-lg font-semibold">{{ __('orders.sections.summary') }}</h2>
                    <ul class="mb-4 space-y-3 text-sm">
                        @foreach ($preview['lines'] as $line)
                            <li class="flex justify-between gap-3 border-b border-stone-100 pb-2">
                                <span>
                                    {{ $line['productName'] }}
                                    @if ($line['variantLabel'])
                                        <span class="text-stone-500">({{ $line['variantLabel'] }})</span>
                                    @endif
                                    × {{ $line['quantity'] }}
                                </span>
                                <span class="font-medium">{{ number_format($line['lineSubtotal']) }} {{ $preview['currency'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt>{{ __('orders.fields.subtotal') }}</dt>
                            <dd>{{ number_format($preview['subtotal']) }} {{ $preview['currency'] }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt>{{ __('orders.shipping.standard') }}</dt>
                            <dd>{{ number_format($preview['shippingCost']) }} {{ $preview['currency'] }}</dd>
                        </div>
                        @if (($preview['discount'] ?? 0) > 0)
                            <div class="flex justify-between text-emerald-700" data-checkout-discount>
                                <dt>{{ __('orders.fields.discount') }}</dt>
                                <dd>−{{ number_format($preview['discount']) }} {{ $preview['currency'] }}</dd>
                            </div>
                        @endif
                        <div class="flex justify-between text-base font-semibold">
                            <dt>{{ __('orders.fields.total') }}</dt>
                            <dd data-checkout-total>{{ number_format($preview['total']) }} {{ $preview['currency'] }}</dd>
                        </div>
                    </dl>
                </div>
            </aside>
        </div>
    @endif
</div>
