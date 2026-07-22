<div>
    <div class="mb-8">
        <h1 class="text-3xl font-semibold tracking-tight">Catálogo</h1>
        <p class="mt-1 text-sm text-stone-600">
            Precios en {{ $currencyEnum->label() }} ({{ $currencyEnum->value }}).
        </p>
    </div>

    @if ($products->isEmpty())
        <p class="rounded-lg border border-dashed border-stone-300 bg-white p-8 text-center text-stone-600">
            No hay productos publicados por el momento.
        </p>
    @else
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($products as $product)
                @php
                    $primary = $product->primaryImage();
                    $firstVariant = $product->variants->first();
                    $price = $firstVariant?->priceIn($currencyEnum);
                @endphp
                <a
                    href="{{ route('products.show', $product->slug) }}"
                    class="group overflow-hidden rounded-xl border border-stone-200 bg-white shadow-sm transition hover:border-stone-400"
                    wire:key="product-{{ $product->id }}"
                >
                    <div class="aspect-[4/3] bg-stone-100">
                        @if ($primary)
                            <img
                                src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($primary->path) }}"
                                alt="{{ $product->name }}"
                                class="h-full w-full object-cover"
                            >
                        @endif
                    </div>
                    <div class="space-y-1 p-4">
                        <h2 class="font-medium group-hover:underline">{{ $product->name }}</h2>
                        @if ($product->category)
                            <p class="text-xs text-stone-500">{{ $product->category->name }}</p>
                        @endif
                        @if ($price)
                            <p class="text-sm font-semibold tabular-nums" data-price="{{ $price->price }}">
                                {{ number_format($price->price, 0, ',', '.') }}
                                <span class="text-xs font-normal text-stone-500">{{ $currencyEnum->value }}</span>
                            </p>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $products->links() }}
        </div>
    @endif
</div>
