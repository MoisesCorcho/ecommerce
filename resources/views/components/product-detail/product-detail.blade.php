<div>
    <p class="mb-4 text-sm">
        <a href="{{ route('products.index') }}" class="text-stone-600 hover:text-stone-900 hover:underline">
            ← Volver al catálogo
        </a>
    </p>

    <div class="grid gap-8 lg:grid-cols-2">
        <div class="space-y-3">
            @forelse ($product->images as $image)
                <div class="overflow-hidden rounded-xl border border-stone-200 bg-stone-100" wire:key="image-{{ $image->id }}">
                    <img
                        src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image->path) }}"
                        alt="{{ $product->name }}"
                        class="w-full object-cover {{ $loop->first ? 'aspect-[4/3]' : 'aspect-video' }}"
                    >
                </div>
            @empty
                <div class="flex aspect-[4/3] items-center justify-center rounded-xl border border-dashed border-stone-300 bg-white text-stone-400">
                    Sin imagen
                </div>
            @endforelse
        </div>

        <div class="space-y-6">
            <div>
                @if ($product->category)
                    <p class="text-xs uppercase tracking-wide text-stone-500">{{ $product->category->name }}</p>
                @endif
                <h1 class="mt-1 text-3xl font-semibold tracking-tight">{{ $product->name }}</h1>
                @if ($product->description)
                    <p class="mt-3 text-stone-700 whitespace-pre-line">{{ $product->description }}</p>
                @endif
            </div>

            <dl class="grid grid-cols-2 gap-3 text-sm">
                @if ($product->material)
                    <div>
                        <dt class="text-stone-500">Material</dt>
                        <dd class="font-medium">{{ $product->material }}</dd>
                    </div>
                @endif
                @if ($product->dimensions)
                    <div>
                        <dt class="text-stone-500">Dimensiones</dt>
                        <dd class="font-medium">{{ $product->dimensions }}</dd>
                    </div>
                @endif
                @if ($product->is_preorder)
                    <div>
                        <dt class="text-stone-500">Disponibilidad</dt>
                        <dd class="font-medium">Preventa</dd>
                    </div>
                @endif
            </dl>

            <div>
                <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-stone-500">
                    Opciones ({{ $currencyEnum->value }})
                </h2>

                @if ($statusMessage)
                    <p class="mb-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800" data-add-status>
                        {{ $statusMessage }}
                        <a href="{{ route('cart.page') }}" class="ml-2 font-medium underline">Ver carrito</a>
                    </p>
                @endif

                @if ($errorMessage)
                    <p class="mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800" data-add-error>
                        {{ $errorMessage }}
                    </p>
                @endif

                <div class="space-y-3 rounded-xl border border-stone-200 bg-white p-4">
                    <fieldset class="space-y-2" data-variant-options>
                        <legend class="sr-only">Variante</legend>
                        @foreach ($pricedVariants as $variant)
                            @php
                                $price = $variant->priceIn($currencyEnum);
                            @endphp
                            @if ($price)
                                <label
                                    class="flex cursor-pointer items-center justify-between gap-4 rounded-lg border border-stone-200 px-3 py-2 has-[:checked]:border-stone-900 has-[:checked]:bg-stone-50"
                                    wire:key="variant-{{ $variant->id }}"
                                    data-variant-option="{{ $variant->id }}"
                                >
                                    <span class="flex items-start gap-3">
                                        <input
                                            type="radio"
                                            wire:model="selectedVariantId"
                                            value="{{ $variant->id }}"
                                            class="mt-1"
                                            name="selectedVariantId"
                                        >
                                        <span>
                                            <span class="block font-medium">
                                                {{ $variant->color ?? $variant->sku }}
                                                @if ($variant->size)
                                                    <span class="text-stone-500">· {{ $variant->size }}</span>
                                                @endif
                                            </span>
                                            <span class="block text-xs text-stone-500">
                                                SKU {{ $variant->sku }} · stock {{ $variant->stock }}
                                            </span>
                                        </span>
                                    </span>
                                    <span class="text-right text-sm font-semibold tabular-nums" data-price="{{ $price->price }}" data-currency="{{ $currencyEnum->value }}">
                                        {{ number_format($price->price, 0, ',', '.') }}
                                        <span class="block text-xs font-normal text-stone-500">{{ $currencyEnum->value }}</span>
                                    </span>
                                </label>
                            @endif
                        @endforeach
                    </fieldset>

                    @error('selectedVariantId')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <div class="flex flex-wrap items-end gap-3 border-t border-stone-100 pt-3">
                        <div>
                            <label for="add-qty" class="mb-1 block text-xs font-medium text-stone-600">Cantidad</label>
                            <input
                                id="add-qty"
                                type="number"
                                min="1"
                                max="99"
                                wire:model="quantity"
                                class="w-24 rounded-md border border-stone-300 px-2 py-2 text-sm tabular-nums"
                                data-add-qty
                            >
                            @error('quantity')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <button
                            type="button"
                            wire:click="addToCart"
                            class="rounded-md bg-stone-900 px-4 py-2 text-sm font-medium text-white hover:bg-stone-800"
                            data-add-to-cart
                        >
                            Agregar al carrito
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
