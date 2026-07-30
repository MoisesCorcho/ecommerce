<x-partials.account-shell active="orders">
    <div class="w-full max-w-3xl space-y-8">
        <div>
            <h1 class="font-[family-name:var(--font-chillax)] text-2xl font-semibold text-intense-cocoa">
                {{ __('account.orders.title') }}
            </h1>
            <p class="mt-2 text-sm text-intense-cocoa/70">
                {{ __('account.orders.subtitle') }}
            </p>
        </div>

        <div class="space-y-4">
            @forelse ($orders as $order)
                <x-section-card.section-card
                    wire:key="order-{{ $order->id }}"
                    tag="a"
                    href="{{ route('profile.orders.show', $order) }}"
                    data-order-card="{{ $order->id }}"
                    class="flex items-center justify-between gap-4 transition-colors hover:bg-soft-sand/70"
                >
                    <div>
                        <p class="font-semibold text-intense-cocoa">{{ $order->order_number }}</p>
                        <p class="text-sm text-intense-cocoa/70">{{ $order->created_at?->format('d/m/Y') }}</p>
                    </div>
                    <div class="flex flex-col items-end gap-2">
                        <span class="inline-flex items-center border border-intense-cocoa/40 px-3 py-1 text-label-caps font-semibold uppercase tracking-widest text-intense-cocoa">
                            {{ $order->status->label() }}
                        </span>
                        <p class="text-sm text-intense-cocoa/70">{{ $order->currency->format($order->total) }}</p>
                    </div>
                </x-section-card.section-card>
            @empty
                <x-partials.account-empty-state
                    :title="__('account.orders.empty_title')"
                    :message="__('account.orders.empty')"
                    :cta-label="__('account.orders.empty_cta')"
                    :cta-href="route('products.index')"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-16 w-16 text-intense-cocoa/40" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m8.25 3.75v6.375m0-6.375a3.75 3.75 0 1 0-7.5 0m7.5 0a3.75 3.75 0 1 1 7.5 0M12 3.75c-1.036 0-1.875.84-1.875 1.875v1.875h3.75V5.625c0-1.036-.84-1.875-1.875-1.875Z" />
                    </svg>
                </x-partials.account-empty-state>
            @endforelse
        </div>

        {{ $orders->links() }}
    </div>
</x-partials.account-shell>
