<x-layouts::storefront>
    <div class="py-8 lg:py-12">
        <div class="mx-auto max-w-storefront px-margin-mobile lg:px-margin-desktop">
            <div class="mx-auto max-w-2xl text-center" data-order-thank-you>
                {{-- Title --}}
                <h1 class="font-[family-name:var(--font-chillax)] text-3xl font-semibold tracking-tight text-intense-cocoa">
                    {{ __('orders.thank_you.title') }}
                </h1>

                <p class="mt-4 text-base font-medium text-intense-cocoa">
                    @if ($order->status === \App\Enums\Orders\OrderStatusEnum::Pending)
                        {{ __('orders.thank_you.body', ['number' => $order->order_number]) }}
                    @else
                        {{ __('orders.thank_you.body_confirmed', ['number' => $order->order_number]) }}
                    @endif
                </p>

                <p class="mt-2 text-sm font-medium text-intense-cocoa" data-order-status>
                    {{ __('orders.thank_you.status', ['status' => $order->status->label()]) }}
                </p>

                {{-- Payment return sub-states (Pending + ?payment=) --}}
                @if (($paymentReturn ?? null) === 'processing' && $order->status === \App\Enums\Orders\OrderStatusEnum::Pending)
                    <p class="mt-4 border border-soft-gold/60 bg-silk-cream px-4 py-3 text-sm font-medium text-intense-cocoa" data-payment-processing role="status">
                        {{ __('payments.return.processing') }}
                    </p>
                @endif

                @if (($paymentReturn ?? null) === 'cancelled' && $order->status === \App\Enums\Orders\OrderStatusEnum::Pending)
                    <p class="mt-4 border border-intense-cocoa bg-silk-cream px-4 py-3 text-sm font-medium text-intense-cocoa" data-payment-cancelled role="status">
                        {{ __('payments.return.cancelled') }}
                    </p>
                @endif

                {{-- Per-status banners --}}
                @if ($order->status === \App\Enums\Orders\OrderStatusEnum::Paid)
                    <p class="mt-4 border border-success/40 bg-success/10 px-4 py-3 text-sm font-medium text-success" data-payment-paid role="status">
                        {{ __('orders.thank_you.banner.paid') }}
                    </p>
                @endif

                @if ($order->status === \App\Enums\Orders\OrderStatusEnum::Processing)
                    <p class="mt-4 border border-soft-gold/60 bg-silk-cream px-4 py-3 text-sm font-medium text-intense-cocoa" data-order-processing role="status">
                        {{ __('orders.thank_you.banner.processing') }}
                    </p>
                @endif

                @if ($order->status === \App\Enums\Orders\OrderStatusEnum::Shipped)
                    <p class="mt-4 border border-success/40 bg-success/10 px-4 py-3 text-sm font-medium text-success" data-order-shipped role="status">
                        {{ __('orders.thank_you.banner.shipped') }}
                    </p>
                @endif

                @if ($order->status === \App\Enums\Orders\OrderStatusEnum::Delivered)
                    <p class="mt-4 border border-success/40 bg-success/10 px-4 py-3 text-sm font-medium text-success" data-order-delivered role="status">
                        {{ __('orders.thank_you.banner.delivered') }}
                    </p>
                @endif

                @if ($order->status === \App\Enums\Orders\OrderStatusEnum::Cancelled)
                    <p class="mt-4 border border-error/40 bg-error/10 px-4 py-3 text-sm font-medium text-error" data-order-cancelled role="alert">
                        {{ __('orders.thank_you.banner.cancelled') }}
                    </p>
                @endif

                @if ($order->status === \App\Enums\Orders\OrderStatusEnum::Refunded)
                    <p class="mt-4 border border-error/40 bg-error/10 px-4 py-3 text-sm font-medium text-error" data-order-refunded role="alert">
                        {{ __('orders.thank_you.banner.refunded') }}
                    </p>
                @endif

                {{-- Payment error flash --}}
                @if (session('payment_error'))
                    <p class="mt-4 border border-error/40 bg-error/10 px-4 py-3 text-sm font-medium text-error" data-payment-error role="alert">
                        {{ session('payment_error') }}
                    </p>
                @endif

                {{-- Pay button (Pending only) --}}
                @if (! empty($payUrl) && $order->status === \App\Enums\Orders\OrderStatusEnum::Pending)
                    <form method="POST" action="{{ $payUrl }}" class="mt-6" data-pay-form>
                        @csrf
                        <x-primary-button type="submit" class="w-full" data-pay-button>
                            {{ ($paymentReturn ?? null) === 'cancelled' ? __('payments.actions.retry') : __('payments.actions.pay') }}
                        </x-primary-button>
                    </form>
                @endif

                <div class="mt-6 space-y-6 text-left">
                    {{-- Order Items Summary card --}}
                    <div class="bg-soft-sand p-6 text-sm text-intense-cocoa shadow-ambient">
                        <div class="mb-4 flex items-center justify-between border-b border-intense-cocoa/30 pb-3">
                            <h2 class="font-[family-name:var(--font-chillax)] text-lg font-semibold text-intense-cocoa">
                                {{ __('orders.sections.summary') }}
                            </h2>
                            <span class="font-semibold text-intense-cocoa" data-order-number>
                                {{ $order->order_number }}
                            </span>
                        </div>

                        {{-- Product lines list --}}
                        <ul class="space-y-3">
                            @foreach ($order->items as $item)
                                <li class="flex items-start justify-between gap-4 border-b border-intense-cocoa/30 pb-3 last:border-b-0 last:pb-0">
                                    <div class="flex flex-col gap-1">
                                        <span class="font-semibold text-intense-cocoa">{{ $item->product_name }}</span>
                                        <div class="flex flex-wrap items-center gap-2">
                                            @if ($item->variant_label)
                                                <span class="inline-flex items-center border border-intense-cocoa bg-silk-cream px-2 py-0.5 text-xs font-medium text-intense-cocoa">
                                                    {{ $item->variant_label }}
                                                </span>
                                            @endif
                                            <span class="text-xs font-semibold text-intense-cocoa">
                                                × {{ $item->quantity }}
                                            </span>
                                            @if ($item->sku)
                                                <span class="text-xs font-medium text-intense-cocoa/70">
                                                    SKU: {{ $item->sku }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <span class="shrink-0 font-semibold tabular-nums text-intense-cocoa">
                                        {{ $order->currency->format($item->unit_price * $item->quantity) }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>

                        {{-- Totals breakdown --}}
                        <dl class="mt-5 space-y-2 border-t border-intense-cocoa/30 pt-4">
                            <div class="flex justify-between">
                                <dt class="font-medium text-intense-cocoa">{{ __('orders.fields.subtotal') }}</dt>
                                <dd class="font-semibold tabular-nums text-intense-cocoa">{{ $order->currency->format($order->subtotal) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="font-medium text-intense-cocoa">{{ __('orders.fields.shipping_cost') }}</dt>
                                <dd class="font-semibold tabular-nums text-intense-cocoa">{{ $order->currency->format($order->shipping_cost) }}</dd>
                            </div>
                            @if ($order->threshold_discount > 0)
                                <div class="flex justify-between font-medium text-success" data-order-threshold-discount>
                                    <dt>{{ __('orders.fields.threshold_discount') }}</dt>
                                    <dd class="font-semibold tabular-nums">-{{ $order->currency->format($order->threshold_discount) }}</dd>
                                </div>
                            @endif
                            @if ($order->discount > 0)
                                <div class="flex justify-between font-medium text-success" data-order-discount>
                                    <dt>{{ __('orders.fields.discount') }}</dt>
                                    <dd class="font-semibold tabular-nums">-{{ $order->currency->format($order->discount) }}</dd>
                                </div>
                            @endif
                            <div class="flex justify-between border-t border-intense-cocoa/30 pt-3 text-base font-semibold text-intense-cocoa">
                                <dt>{{ __('orders.fields.total') }}</dt>
                                <dd class="text-xl font-bold tabular-nums text-intense-cocoa">{{ $order->currency->format($order->total) }}</dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Shipping & Delivery details card --}}
                    @if ($order->shipping_full_name || $order->shipping_address_line_1)
                        <div class="bg-soft-sand p-6 text-sm text-intense-cocoa shadow-ambient">
                            <div class="mb-4 border-b border-intense-cocoa/30 pb-3">
                                <h2 class="font-[family-name:var(--font-chillax)] text-lg font-semibold text-intense-cocoa">
                                    {{ __('orders.sections.shipping') }}
                                </h2>
                            </div>

                            <div class="space-y-1.5">
                                @if ($order->shipping_full_name)
                                    <h3 class="font-semibold text-base text-intense-cocoa">
                                        {{ $order->shipping_full_name }}
                                    </h3>
                                @endif
                                <p class="text-sm font-medium leading-relaxed text-intense-cocoa">
                                    {{ $order->shipping_address_line_1 }}
                                    @if ($order->shipping_address_line_2)
                                        , {{ $order->shipping_address_line_2 }}
                                    @endif
                                </p>
                            </div>

                            {{-- Chips with city, state, postal code, phone, email --}}
                            <div class="mt-4 flex flex-wrap gap-2 border-t border-intense-cocoa/30 pt-3">
                                @if ($order->shipping_city || $order->shipping_state)
                                    <span class="inline-flex items-center gap-1 border border-intense-cocoa bg-silk-cream px-2.5 py-1 text-xs font-medium text-intense-cocoa">
                                        <span>{{ $order->shipping_city }}@if ($order->shipping_city && $order->shipping_state), @endif{{ $order->shipping_state }}</span>
                                        @if ($order->shipping_postal_code)
                                            <span class="text-intense-cocoa/70">({{ $order->shipping_postal_code }})</span>
                                        @endif
                                    </span>
                                @endif
                                @if ($order->shipping_phone)
                                    <span class="inline-flex items-center gap-1 border border-intense-cocoa bg-silk-cream px-2.5 py-1 text-xs font-medium text-intense-cocoa">
                                        <span>{{ $order->shipping_phone }}</span>
                                    </span>
                                @endif
                                @if ($order->email)
                                    <span class="inline-flex items-center gap-1 border border-intense-cocoa bg-silk-cream px-2.5 py-1 text-xs font-medium text-intense-cocoa">
                                        <span>{{ $order->email }}</span>
                                    </span>
                                @endif
                            </div>

                            @if ($order->customer_notes)
                                <div class="mt-4 border-t border-intense-cocoa/30 pt-3">
                                    <span class="text-xs font-medium text-intense-cocoa/80">{{ __('orders.fields.customer_notes') }}:</span>
                                    <p class="mt-1 text-xs font-medium italic text-intense-cocoa">{{ $order->customer_notes }}</p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Continue shopping --}}
                <div class="mt-8">
                    <a
                        href="{{ route('products.index') }}"
                        class="inline-block cursor-pointer text-sm font-semibold text-intense-cocoa underline underline-offset-2 transition-colors hover:text-soft-gold"
                    >
                        {{ __('orders.thank_you.continue_shopping') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layouts::storefront>
