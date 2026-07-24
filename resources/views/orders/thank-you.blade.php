<x-layouts::storefront title="Leen Handbags | Order detail">
    <div class="py-8 lg:py-12">
        <div class="mx-auto max-w-storefront px-margin-mobile lg:px-margin-desktop">
            <div class="mx-auto max-w-xl text-center" data-order-thank-you>
                {{-- Title --}}
                <h1 class="font-[family-name:var(--font-chillax)] text-3xl font-semibold tracking-tight text-intense-cocoa">
                    {{ __('orders.thank_you.title') }}
                </h1>

                <p class="mt-4 text-intense-cocoa/80">
                    {{ __('orders.thank_you.body', ['number' => $order->order_number]) }}
                </p>

                <p class="mt-2 text-sm text-intense-cocoa/60" data-order-status>
                    {{ __('orders.thank_you.status', ['status' => $order->status->label()]) }}
                </p>

                {{-- Payment return sub-states (Pending + ?payment=) --}}
                @if (($paymentReturn ?? null) === 'processing' && $order->status === \App\Enums\Orders\OrderStatusEnum::Pending)
                    <p class="mt-4 border border-soft-gold/30 bg-soft-sand px-4 py-3 text-sm text-intense-cocoa" data-payment-processing role="status">
                        {{ __('payments.return.processing') }}
                    </p>
                @endif

                @if (($paymentReturn ?? null) === 'cancelled' && $order->status === \App\Enums\Orders\OrderStatusEnum::Pending)
                    <p class="mt-4 border border-intense-cocoa/20 bg-soft-sand px-4 py-3 text-sm text-intense-cocoa" data-payment-cancelled role="status">
                        {{ __('payments.return.cancelled') }}
                    </p>
                @endif

                {{-- Per-status banners --}}
                @if ($order->status === \App\Enums\Orders\OrderStatusEnum::Paid)
                    <p class="mt-4 border border-success/20 bg-success/5 px-4 py-3 text-sm text-success" data-payment-paid role="status">
                        {{ __('orders.thank_you.banner.paid') }}
                    </p>
                @endif

                @if ($order->status === \App\Enums\Orders\OrderStatusEnum::Processing)
                    <p class="mt-4 border border-soft-gold/30 bg-soft-sand px-4 py-3 text-sm text-intense-cocoa" data-order-processing role="status">
                        {{ __('orders.thank_you.banner.processing') }}
                    </p>
                @endif

                @if ($order->status === \App\Enums\Orders\OrderStatusEnum::Shipped)
                    <p class="mt-4 border border-success/20 bg-success/5 px-4 py-3 text-sm text-success" data-order-shipped role="status">
                        {{ __('orders.thank_you.banner.shipped') }}
                    </p>
                @endif

                @if ($order->status === \App\Enums\Orders\OrderStatusEnum::Delivered)
                    <p class="mt-4 border border-success/20 bg-success/5 px-4 py-3 text-sm text-success" data-order-delivered role="status">
                        {{ __('orders.thank_you.banner.delivered') }}
                    </p>
                @endif

                @if ($order->status === \App\Enums\Orders\OrderStatusEnum::Cancelled)
                    <p class="mt-4 border border-error/20 bg-error/5 px-4 py-3 text-sm text-error" data-order-cancelled role="alert">
                        {{ __('orders.thank_you.banner.cancelled') }}
                    </p>
                @endif

                @if ($order->status === \App\Enums\Orders\OrderStatusEnum::Refunded)
                    <p class="mt-4 border border-error/20 bg-error/5 px-4 py-3 text-sm text-error" data-order-refunded role="alert">
                        {{ __('orders.thank_you.banner.refunded') }}
                    </p>
                @endif

                {{-- Payment error flash --}}
                @if (session('payment_error'))
                    <p class="mt-4 border border-error/20 bg-error/5 px-4 py-3 text-sm text-error" data-payment-error role="alert">
                        {{ session('payment_error') }}
                    </p>
                @endif

                {{-- Order summary --}}
                <dl class="mt-6 space-y-2 border border-intense-cocoa/10 bg-soft-sand p-5 text-left text-sm text-intense-cocoa">
                    <div class="flex justify-between border-b border-intense-cocoa/10 py-2">
                        <dt class="text-intense-cocoa/60">{{ __('orders.fields.order_number') }}</dt>
                        <dd class="font-medium" data-order-number>{{ $order->order_number }}</dd>
                    </div>
                    <div class="flex justify-between border-b border-intense-cocoa/10 py-2">
                        <dt class="text-intense-cocoa/60">{{ __('orders.fields.total') }}</dt>
                        <dd class="font-medium tabular-nums">{{ number_format($order->total) }} {{ $order->currency->value }}</dd>
                    </div>
                    <div class="flex justify-between py-2">
                        <dt class="text-intense-cocoa/60">{{ __('orders.fields.email') }}</dt>
                        <dd>{{ $order->email }}</dd>
                    </div>
                </dl>

                {{-- Pay button (Pending only) --}}
                @if (! empty($payUrl) && $order->status === \App\Enums\Orders\OrderStatusEnum::Pending)
                    <form method="POST" action="{{ $payUrl }}" class="mt-6" data-pay-form>
                        @csrf
                        <button
                            type="submit"
                            class="flex h-12 w-full items-center justify-center bg-intense-cocoa text-sm font-semibold text-silk-cream transition-colors duration-200 hover:bg-soft-gold hover:text-intense-cocoa"
                            data-pay-button
                        >
                            {{ ($paymentReturn ?? null) === 'cancelled' ? __('payments.actions.retry') : __('payments.actions.pay') }}
                        </button>
                    </form>
                @endif

                {{-- Continue shopping --}}
                <a
                    href="{{ route('products.index') }}"
                    class="mt-6 inline-block text-sm font-medium text-intense-cocoa underline underline-offset-2 transition-colors hover:text-soft-gold"
                >
                    {{ __('orders.thank_you.continue_shopping') }}
                </a>
            </div>
        </div>
    </div>
</x-layouts::storefront>
