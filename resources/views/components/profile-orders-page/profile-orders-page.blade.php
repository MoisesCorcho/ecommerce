<div class="py-12 lg:py-16">
    <div class="mx-auto w-full max-w-3xl space-y-8 px-4">
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
                <a
                    wire:key="order-{{ $order->id }}"
                    href="{{ route('profile.orders.show', $order) }}"
                    class="flex items-center justify-between gap-4 bg-soft-sand p-6 shadow-ambient transition-colors hover:bg-soft-sand/70"
                >
                    <div>
                        <p class="font-semibold text-intense-cocoa">{{ $order->order_number }}</p>
                        <p class="text-sm text-intense-cocoa/70">{{ $order->created_at?->format('d/m/Y') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-label-caps font-semibold uppercase tracking-widest text-intense-cocoa">
                            {{ $order->status->label() }}
                        </p>
                        <p class="text-sm text-intense-cocoa/70">{{ number_format($order->total, 0) }} {{ $order->currency->value }}</p>
                    </div>
                </a>
            @empty
                <p class="text-sm text-intense-cocoa/70">{{ __('account.orders.empty') }}</p>
            @endforelse
        </div>

        {{ $orders->links() }}
    </div>
</div>
