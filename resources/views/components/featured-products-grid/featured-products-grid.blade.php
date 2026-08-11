<div>
    @if ($products->isNotEmpty())
        @php
            $isCarousel = $products->count() > 3;
        @endphp

        <section class="py-16 lg:py-24" aria-labelledby="featured-heading">
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
                    <h2 id="featured-heading" class="font-chillax text-headline-md text-intense-cocoa">
                        {{ __('storefront.home.featured') }}
                    </h2>

                    <div class="flex items-center gap-4">
                        @if ($isCarousel)
                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    x-on:click="scrollPrev()"
                                    x-bind:disabled="!canScrollLeft"
                                    class="flex h-10 w-10 items-center justify-center rounded-none border border-intense-cocoa text-intense-cocoa transition-all hover:bg-intense-cocoa hover:text-silk-cream disabled:pointer-events-none disabled:opacity-20"
                                    aria-label="Productos anteriores"
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
                                    aria-label="Productos siguientes"
                                >
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                    </svg>
                                </button>
                            </div>
                        @endif

                        <a
                            href="{{ route('products.index') }}"
                            class="text-label-caps font-semibold uppercase tracking-wider text-intense-cocoa underline underline-offset-4 transition-colors hover:text-soft-gold"
                        >
                            {{ __('storefront.home.view_all') }}
                        </a>
                    </div>
                </div>

                @if ($isCarousel)
                    <div
                        x-ref="container"
                        x-on:scroll.debounce.50ms="checkScroll()"
                        class="no-scrollbar flex gap-gutter overflow-x-auto pb-4"
                        style="scrollbar-width: none; -ms-overflow-style: none;"
                    >
                        @foreach ($products as $product)
                            <div class="w-[calc(100%-1rem)] shrink-0 sm:w-[calc(50%-0.75rem)] lg:w-[calc(33.333%-1rem)]">
                                <livewire:product-card :product="$product" :currency="$currencyEnum->value" wire:key="pc-{{ $product->id }}" />
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="grid grid-cols-1 gap-gutter sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($products as $product)
                            <livewire:product-card :product="$product" :currency="$currencyEnum->value" wire:key="pc-{{ $product->id }}" />
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    @endif
</div>
