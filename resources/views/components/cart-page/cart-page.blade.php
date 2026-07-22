<div>
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight">Carrito</h1>
            <p class="mt-1 text-sm text-stone-600">
                Precios live en {{ $cartView->currency->value }}. Scaffold de prueba (sin marca).
            </p>
        </div>
        <a href="{{ route('products.index') }}" class="text-sm text-stone-600 hover:text-stone-900 hover:underline">
            ← Seguir comprando
        </a>
    </div>

    @if ($statusMessage)
        <p class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" data-cart-status>
            {{ $statusMessage }}
        </p>
    @endif

    @if ($errorMessage)
        <p class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" data-cart-error>
            {{ $errorMessage }}
        </p>
    @endif

    <div class="mb-6 flex flex-wrap items-center gap-3 rounded-xl border border-stone-200 bg-white p-4">
        <label class="text-sm font-medium text-stone-700" for="cart-currency">Moneda</label>
        <select
            id="cart-currency"
            wire:model="currency"
            wire:change="changeCurrency($event.target.value)"
            class="rounded-md border border-stone-300 bg-white px-3 py-2 text-sm"
            data-cart-currency
        >
            <option value="COP">COP</option>
            <option value="EUR">EUR</option>
        </select>
        @error('currency')
            <span class="text-sm text-red-600">{{ $message }}</span>
        @enderror
    </div>

    @if (count($cartView->lines) === 0)
        <div class="rounded-xl border border-dashed border-stone-300 bg-white p-10 text-center" data-cart-empty>
            <p class="text-stone-600">El carrito está vacío.</p>
            <a
                href="{{ route('products.index') }}"
                class="mt-4 inline-block text-sm font-medium text-stone-900 underline"
            >
                Ver catálogo
            </a>
        </div>
    @else
        <div class="overflow-hidden rounded-xl border border-stone-200 bg-white">
            <ul class="divide-y divide-stone-200" data-cart-lines>
                @foreach ($cartView->lines as $line)
                    <li
                        class="flex flex-col gap-3 px-4 py-4 sm:flex-row sm:items-center sm:justify-between"
                        wire:key="cart-line-{{ $line->productVariantId }}"
                        data-cart-line="{{ $line->productVariantId }}"
                    >
                        <div class="min-w-0 flex-1">
                            <p class="font-medium">{{ $line->productName ?? 'Producto' }}</p>
                            <p class="text-xs text-stone-500">SKU {{ $line->sku }} · variante #{{ $line->productVariantId }}</p>
                            <p class="mt-1 text-sm tabular-nums text-stone-700">
                                {{ number_format($line->unitPrice, 0, ',', '.') }}
                                {{ $cartView->currency->value }} c/u
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <label class="sr-only" for="qty-{{ $line->productVariantId }}">Cantidad</label>
                            <input
                                id="qty-{{ $line->productVariantId }}"
                                type="number"
                                min="0"
                                max="99"
                                wire:model="quantities.{{ $line->productVariantId }}"
                                class="w-20 rounded-md border border-stone-300 px-2 py-1.5 text-sm tabular-nums"
                                data-cart-qty="{{ $line->productVariantId }}"
                            >
                            <button
                                type="button"
                                wire:click="updateLine({{ $line->productVariantId }})"
                                class="rounded-md border border-stone-300 bg-stone-50 px-3 py-1.5 text-sm hover:bg-stone-100"
                                data-cart-update="{{ $line->productVariantId }}"
                            >
                                Actualizar
                            </button>
                            <button
                                type="button"
                                wire:click="removeLine({{ $line->productVariantId }})"
                                class="rounded-md border border-red-200 px-3 py-1.5 text-sm text-red-700 hover:bg-red-50"
                                data-cart-remove="{{ $line->productVariantId }}"
                            >
                                Quitar
                            </button>
                        </div>

                        <p class="text-sm font-semibold tabular-nums sm:w-28 sm:text-right" data-cart-line-subtotal="{{ $line->productVariantId }}">
                            {{ number_format($line->lineSubtotal, 0, ',', '.') }}
                            <span class="text-xs font-normal text-stone-500">{{ $cartView->currency->value }}</span>
                        </p>
                    </li>
                @endforeach
            </ul>

            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-stone-200 bg-stone-50 px-4 py-4">
                <button
                    type="button"
                    wire:click="clearCart"
                    wire:confirm="¿Vaciar todo el carrito?"
                    class="text-sm text-stone-600 underline hover:text-stone-900"
                    data-cart-clear
                >
                    Vaciar carrito
                </button>
                <div class="flex flex-wrap items-center gap-4">
                    <p class="text-lg font-semibold tabular-nums" data-cart-total>
                        Total: {{ number_format($cartView->total, 0, ',', '.') }}
                        <span class="text-sm font-normal text-stone-500">{{ $cartView->currency->value }}</span>
                    </p>
                    <a
                        href="{{ route('checkout.show') }}"
                        class="rounded-md bg-stone-900 px-4 py-2 text-sm font-semibold text-white hover:bg-stone-800"
                        data-cart-checkout
                    >
                        Checkout
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
