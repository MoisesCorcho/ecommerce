<x-layouts::storefront :title="$title">
    <x-partials.account-shell active="orders" :order-number="$order->order_number">
        <div class="w-full max-w-2xl space-y-6">
            <div>
                <h1 class="font-[family-name:var(--font-chillax)] text-3xl font-semibold tracking-tight text-intense-cocoa">
                    {{ $order->order_number }}
                </h1>
                <p class="mt-2 text-sm text-intense-cocoa/70">
                    {{ __('orders.thank_you.status', ['status' => $order->status->label()]) }}
                </p>
            </div>

            {{-- Items --}}
            <div class="divide-y divide-intense-cocoa/10 bg-soft-sand p-6 shadow-ambient">
                @foreach ($order->items as $item)
                    <div class="flex items-center justify-between gap-4 py-5 first:pt-0 last:pb-0">
                        <div>
                            <p class="font-medium text-intense-cocoa">{{ $item->product_name }}</p>
                            @if ($item->variant_label)
                                <p class="text-sm text-intense-cocoa/60">{{ $item->variant_label }}</p>
                            @endif
                            <p class="text-sm text-intense-cocoa/60">{{ __('orders.fields.quantity') }}: {{ $item->quantity }}</p>
                        </div>
                        <p class="tabular-nums font-medium text-intense-cocoa">
                            {{ number_format($item->unit_price * $item->quantity) }} {{ $order->currency->value }}
                        </p>
                    </div>
                @endforeach
            </div>

            {{-- Totals --}}
            <dl class="space-y-2 bg-soft-sand p-6 text-sm text-intense-cocoa shadow-ambient">
                <div class="flex justify-between border-b border-intense-cocoa/10 py-2">
                    <dt class="text-intense-cocoa/60">{{ __('orders.fields.subtotal') }}</dt>
                    <dd class="tabular-nums">{{ number_format($order->subtotal) }} {{ $order->currency->value }}</dd>
                </div>
                @if ($order->discount > 0)
                    <div class="flex justify-between border-b border-intense-cocoa/10 py-2">
                        <dt class="text-intense-cocoa/60">{{ __('orders.fields.discount') }}</dt>
                        <dd class="tabular-nums">-{{ number_format($order->discount) }} {{ $order->currency->value }}</dd>
                    </div>
                @endif
                <div class="flex justify-between border-b border-intense-cocoa/10 py-2">
                    <dt class="text-intense-cocoa/60">{{ __('orders.fields.shipping_cost') }}</dt>
                    <dd class="tabular-nums">{{ number_format($order->shipping_cost) }} {{ $order->currency->value }}</dd>
                </div>
                <div class="flex justify-between py-2">
                    <dt class="font-medium">{{ __('orders.fields.total') }}</dt>
                    <dd class="tabular-nums font-medium">{{ number_format($order->total) }} {{ $order->currency->value }}</dd>
                </div>
            </dl>

            {{-- Shipping address (snapshot columns, not the live relation) --}}
            <div class="bg-soft-sand p-6 text-sm text-intense-cocoa shadow-ambient">
                <p class="mb-2 font-medium">{{ __('account.orders.shipping_address') }}</p>
                <p>{{ $order->shipping_full_name }} · {{ $order->shipping_phone }}</p>
                <p>
                    {{ $order->shipping_address_line_1 }}@if ($order->shipping_address_line_2), {{ $order->shipping_address_line_2 }}@endif
                </p>
                <p>{{ $order->shipping_city }}, {{ $order->shipping_state }}, {{ $order->shipping_country }} {{ $order->shipping_postal_code }}</p>
            </div>
        </div>
    </x-partials.account-shell>
</x-layouts::storefront>
