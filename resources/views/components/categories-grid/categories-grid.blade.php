<div>
    @if ($categories->isNotEmpty())
        @php
            $isCarousel = $categories->count() > 4;
        @endphp

        <section class="py-16 lg:py-24" aria-labelledby="categories-heading">
            <div 
                @if ($isCarousel)
                    x-data="{
                        scrollContainer: null,
                        canScrollLeft: false,
                        canScrollRight: true,
                        isAnimating: false,
                        init() {
                            this.scrollContainer = this.$refs.container;
                            this.checkScroll();
                        },
                        checkScroll() {
                            if (!this.scrollContainer) return;
                            const { scrollLeft, scrollWidth, clientWidth } = this.scrollContainer;
                            this.canScrollLeft = scrollLeft > 10;
                            this.canScrollRight = scrollLeft < (scrollWidth - clientWidth - 10);
                        },
                        smoothScroll(distance, duration = 450) {
                            if (!this.scrollContainer || this.isAnimating) return;
                            this.isAnimating = true;

                            const start = this.scrollContainer.scrollLeft;
                            const startTime = performance.now();

                            const easeInOutCubic = (t) => t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;

                            const animate = (currentTime) => {
                                const elapsed = currentTime - startTime;
                                const progress = Math.min(elapsed / duration, 1);
                                const easedProgress = easeInOutCubic(progress);

                                this.scrollContainer.scrollLeft = start + (distance * easedProgress);

                                if (progress < 1) {
                                    requestAnimationFrame(animate);
                                } else {
                                    this.isAnimating = false;
                                    this.checkScroll();
                                }
                            };

                            requestAnimationFrame(animate);
                        },
                        scrollNext() {
                            if (!this.scrollContainer) return;
                            const scrollAmount = this.scrollContainer.clientWidth * 0.75;
                            this.smoothScroll(scrollAmount);
                        },
                        scrollPrev() {
                            if (!this.scrollContainer) return;
                            const scrollAmount = this.scrollContainer.clientWidth * 0.75;
                            this.smoothScroll(-scrollAmount);
                        }
                    }"
                @endif
                class="relative"
            >
                <div class="mb-8 flex items-center justify-between">
                    <h2 id="categories-heading" class="font-chillax text-headline-md text-intense-cocoa">
                        {{ __('storefront.home.categories') }}
                    </h2>

                    @if ($isCarousel)
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                x-on:click="scrollPrev()"
                                x-bind:disabled="!canScrollLeft"
                                class="flex h-10 w-10 items-center justify-center rounded-none border border-intense-cocoa text-intense-cocoa transition-all hover:bg-intense-cocoa hover:text-silk-cream disabled:pointer-events-none disabled:opacity-20"
                                aria-label="Categorías anteriores"
                            >
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                                </svg>
                            </button>

                            <button
                                type="button"
                                x-on:click="scrollNext()"
                                x-bind:disabled="!canScrollRight"
                                class="flex h-10 w-10 items-center justify-center rounded-none border border-intense-cocoa text-intense-cocoa transition-all hover:bg-intense-cocoa hover:text-silk-cream disabled:pointer-events-none disabled:opacity-20"
                                aria-label="Categorías siguientes"
                            >
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                </svg>
                            </button>
                        </div>
                    @endif
                </div>

                @if ($isCarousel)
                    <div
                        x-ref="container"
                        x-on:scroll.debounce.50ms="checkScroll()"
                        class="no-scrollbar flex gap-gutter overflow-x-auto pb-4"
                        style="scrollbar-width: none; -ms-overflow-style: none;"
                    >
                        @foreach ($categories as $category)
                            <a
                                href="{{ route('products.index', ['category' => $category->slug]) }}"
                                class="group block w-[calc(50%-0.5rem)] shrink-0 lg:w-[calc(25%-0.9375rem)]"
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
                @else
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
                @endif
            </div>
        </section>
    @endif
</div>
