<x-layouts::storefront>
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
            <x-section-card.section-card class="divide-y divide-intense-cocoa/30">
                @foreach ($order->items as $item)
                    <div class="flex items-center justify-between gap-4 py-5 first:pt-0 last:pb-0">
                        <div>
                            <p class="font-semibold text-intense-cocoa">{{ $item->product_name }}</p>
                            @if ($item->variant_label)
                                <p class="text-sm font-medium text-intense-cocoa/90">{{ $item->variant_label }}</p>
                            @endif
                            @if ($item->sku)
                                <p class="text-xs font-medium text-intense-cocoa/70">{{ __('account.orders.sku_label') }}: {{ $item->sku }}</p>
                            @endif
                            <p class="text-sm font-medium text-intense-cocoa/90">{{ __('orders.fields.quantity') }}: {{ $item->quantity }}</p>

                            @if ($order->status->isEligibleForReview() && $item->productVariant?->product)
                                <a
                                    href="{{ route('products.show', $item->productVariant->product->slug).'#reviews-heading' }}"
                                    class="mt-2 inline-block cursor-pointer text-sm font-semibold text-intense-cocoa underline decoration-soft-gold decoration-2 underline-offset-2 transition-colors hover:text-soft-gold"
                                    data-leave-review="{{ $item->id }}"
                                >
                                    {{ __('account.orders.leave_review') }}
                                </a>
                            @endif
                        </div>
                        <p class="tabular-nums font-semibold text-intense-cocoa">
                            {{ $order->currency->format($item->unit_price * $item->quantity) }}
                        </p>
                    </div>
                @endforeach
            </x-section-card.section-card>

            {{-- Totals --}}
            <x-section-card.section-card tag="dl" class="space-y-2.5 text-sm text-intense-cocoa">
                <div class="flex justify-between border-b border-intense-cocoa/30 pb-2">
                    <dt class="font-medium text-intense-cocoa">{{ __('orders.fields.subtotal') }}</dt>
                    <dd class="font-semibold tabular-nums text-intense-cocoa">{{ $order->currency->format($order->subtotal) }}</dd>
                </div>
                @if ($order->discount > 0)
                    <div class="flex justify-between border-b border-intense-cocoa/30 pb-2 text-success">
                        <dt class="font-medium">{{ __('orders.fields.discount') }}</dt>
                        <dd class="font-semibold tabular-nums">-{{ $order->currency->format($order->discount) }}</dd>
                    </div>
                @endif
                <div class="flex justify-between border-b border-intense-cocoa/30 pb-2">
                    <dt class="font-medium text-intense-cocoa">{{ __('orders.fields.shipping_cost') }}</dt>
                    <dd class="font-semibold tabular-nums text-intense-cocoa">{{ $order->currency->format($order->shipping_cost) }}</dd>
                </div>
                <div class="flex justify-between pt-1">
                    <dt class="font-semibold text-intense-cocoa">{{ __('orders.fields.total') }}</dt>
                    <dd class="text-base font-bold tabular-nums text-intense-cocoa">{{ $order->currency->format($order->total) }}</dd>
                </div>
            </x-section-card.section-card>

            {{-- Shipping address (snapshot columns, not the live relation) --}}
            <x-section-card.section-card class="text-sm text-intense-cocoa">
                <p class="mb-2 font-medium">{{ __('account.orders.shipping_address') }}</p>
                <p>{{ $order->shipping_full_name }} · {{ $order->shipping_phone }}</p>
                <p>
                    {{ $order->shipping_address_line_1 }}@if ($order->shipping_address_line_2), {{ $order->shipping_address_line_2 }}@endif
                </p>
                <p>{{ $order->shipping_city }}, {{ $order->shipping_state }}, {{ $order->shipping_country }} {{ $order->shipping_postal_code }}</p>
            </x-section-card.section-card>
        </div>
    </x-partials.account-shell>
</x-layouts::storefront>
