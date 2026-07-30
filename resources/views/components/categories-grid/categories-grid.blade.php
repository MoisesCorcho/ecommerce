<div>
    @if ($categories->isNotEmpty())
        <section class="py-16 lg:py-24" aria-labelledby="categories-heading">
            <h2 id="categories-heading" class="mb-8 font-chillax text-headline-md text-intense-cocoa">
                {{ __('storefront.home.categories') }}
            </h2>

            <div class="grid grid-cols-2 gap-gutter lg:grid-cols-4">
                @foreach ($categories as $category)
                    <a
                        href="{{ route('products.index', ['category' => $category->slug]) }}"
                        class="group block"
                        wire:key="cat-{{ $category->id }}"
                    >
                        <div class="aspect-[4/5] overflow-hidden rounded-none bg-soft-sand">
                            @if ($hasImageColumn && $category->image_path)
                                <img
                                    src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($category->image_path) }}"
                                    alt="{{ $category->name }}"
                                    class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                                >
                            @else
                                <div class="flex h-full w-full items-center justify-center">
                                    <span class="font-chillax text-headline-sm text-intense-cocoa/30">
                                        {{ strtoupper(mb_substr($category->name, 0, 1)) }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        <h3 class="mt-4 text-label-caps font-semibold uppercase tracking-wider text-intense-cocoa">
                            {{ $category->name }}
                        </h3>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
</div>
