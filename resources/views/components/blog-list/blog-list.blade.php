<div class="py-8 lg:py-12">
    {{-- Breadcrumb --}}
    <x-breadcrumb.breadcrumb :items="[
        ['label' => __('storefront.nav.home'), 'href' => url('/')],
        ['label' => __('storefront.nav.blog')],
    ]" />

    <div class="mx-auto max-w-storefront px-margin-mobile lg:px-margin-desktop">
        {{-- Hero Editorial Header --}}
        <div class="mb-10 text-center">
            <h1 class="font-chillax text-display-lg-mobile md:text-display-lg text-intense-cocoa font-normal tracking-tight">
                {{ __('blog.storefront.hero_title') }}
            </h1>
            <p class="font-labelle-aurore text-accent-script text-soft-gold mt-2">
                {{ __('blog.storefront.hero_subtitle') }}
            </p>
            <p class="font-sans text-body-md text-intense-cocoa/75 max-w-2xl mx-auto mt-4 leading-relaxed">
                {{ __('blog.storefront.hero_description') }}
            </p>
        </div>

        {{-- Search Input Bar --}}
        <div class="mb-10 max-w-md mx-auto">
            <div class="relative flex items-center">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-intense-cocoa/40">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </div>
                <input
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="{{ __('blog.storefront.search_placeholder') }}"
                    class="w-full bg-soft-sand/40 border border-intense-cocoa/20 pl-10 pr-10 py-2.5 text-body-sm text-intense-cocoa placeholder:text-intense-cocoa/40 focus:border-soft-gold focus:ring-1 focus:ring-soft-gold focus:outline-hidden transition-colors [&::-webkit-search-cancel-button]:hidden [&::-webkit-search-decoration]:hidden [&::-ms-clear]:hidden"
                />
                @if (filled($search))
                    <button
                        type="button"
                        wire:click="$set('search', '')"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-intense-cocoa/40 hover:text-intense-cocoa transition-colors"
                        aria-label="{{ __('blog.storefront.clear_search') }}"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                @endif
            </div>
        </div>

        {{-- Categories Filter Pills --}}
        @if ($categories->isNotEmpty())
            @php
                $desktopLimit = 7;
                $hasOverflowCategories = $categories->count() > $desktopLimit;
                $activeCategoryIsHidden = $hasOverflowCategories && $categories->slice($desktopLimit)->contains('slug', $activeCategory);
            @endphp
            <div
                x-data="{
                    expanded: {{ $activeCategoryIsHidden ? 'true' : 'false' }},
                    canScrollLeft: false,
                    canScrollRight: false,
                    checkScroll() {
                        if (!this.$refs.scrollContainer) return;
                        const el = this.$refs.scrollContainer;
                        this.canScrollLeft = el.scrollLeft > 4;
                        this.canScrollRight = el.scrollLeft < (el.scrollWidth - el.clientWidth - 4);
                    },
                    scrollLeft() {
                        this.$refs.scrollContainer.scrollBy({ left: -160, behavior: 'smooth' });
                    },
                    scrollRight() {
                        this.$refs.scrollContainer.scrollBy({ left: 160, behavior: 'smooth' });
                    }
                }"
                x-init="$nextTick(() => { checkScroll(); }); window.addEventListener('resize', () => checkScroll())"
                class="mb-12"
            >
                {{-- Mobile View: Touch Carousel with Navigation Arrows and Edge Fades (md:hidden) --}}
                <div class="md:hidden relative flex items-center w-full">
                    {{-- Left Arrow --}}
                    <button
                        type="button"
                        x-show="canScrollLeft"
                        x-cloak
                        x-transition:enter="transition-opacity duration-200"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="transition-opacity duration-200"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        x-on:click="scrollLeft()"
                        class="absolute left-0 z-20 flex h-8 w-8 items-center justify-center bg-silk-cream/95 border border-intense-cocoa/20 text-intense-cocoa shadow-sm hover:bg-soft-sand transition-colors"
                        aria-label="{{ __('blog.storefront.previous_categories') }}"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                        </svg>
                    </button>

                    {{-- Left Fade Gradient --}}
                    <div
                        x-show="canScrollLeft"
                        x-cloak
                        x-transition.opacity
                        class="pointer-events-none absolute left-0 top-0 bottom-0 w-8 bg-gradient-to-r from-silk-cream to-transparent z-10"
                    ></div>

                    {{-- Scrollable Container --}}
                    <div
                        x-ref="scrollContainer"
                        x-on:scroll.passive="checkScroll()"
                        class="flex items-center gap-3 overflow-x-auto no-scrollbar py-2 px-1 w-full"
                    >
                        <button
                            type="button"
                            wire:click="selectCategory(null)"
                            class="shrink-0 px-5 py-2 text-label-caps font-semibold uppercase tracking-widest transition-all duration-300 rounded-none {{ empty($activeCategory) ? 'bg-intense-cocoa text-silk-cream shadow-sm' : 'border border-intense-cocoa/20 text-intense-cocoa hover:bg-soft-sand hover:border-intense-cocoa/40' }}"
                        >
                            {{ __('blog.storefront.all_categories') }}
                        </button>

                        @foreach ($categories as $cat)
                            <button
                                type="button"
                                wire:click="selectCategory('{{ $cat->slug }}')"
                                class="shrink-0 px-5 py-2 text-label-caps font-semibold uppercase tracking-widest transition-all duration-300 rounded-none {{ $activeCategory === $cat->slug ? 'bg-intense-cocoa text-silk-cream shadow-sm' : 'border border-intense-cocoa/20 text-intense-cocoa hover:bg-soft-sand hover:border-intense-cocoa/40' }}"
                            >
                                {{ $cat->getLocalizedName() }}
                            </button>
                        @endforeach
                    </div>

                    {{-- Right Fade Gradient --}}
                    <div
                        x-show="canScrollRight"
                        x-cloak
                        x-transition.opacity
                        class="pointer-events-none absolute right-0 top-0 bottom-0 w-8 bg-gradient-to-l from-silk-cream to-transparent z-10"
                    ></div>

                    {{-- Right Arrow --}}
                    <button
                        type="button"
                        x-show="canScrollRight"
                        x-cloak
                        x-transition:enter="transition-opacity duration-200"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="transition-opacity duration-200"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        x-on:click="scrollRight()"
                        class="absolute right-0 z-20 flex h-8 w-8 items-center justify-center bg-silk-cream/95 border border-intense-cocoa/20 text-intense-cocoa shadow-sm hover:bg-soft-sand transition-colors"
                        aria-label="{{ __('blog.storefront.next_categories') }}"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                        </svg>
                    </button>
                </div>

                {{-- Desktop View: Centered flex-wrap with 'Show More' accordion if > 7 categories (hidden md:flex) --}}
                <div class="hidden md:flex md:flex-wrap md:justify-center md:items-center gap-3 max-w-4xl mx-auto">
                    <button
                        type="button"
                        wire:click="selectCategory(null)"
                        class="shrink-0 px-5 py-2 text-label-caps font-semibold uppercase tracking-widest transition-all duration-300 rounded-none {{ empty($activeCategory) ? 'bg-intense-cocoa text-silk-cream shadow-sm' : 'border border-intense-cocoa/20 text-intense-cocoa hover:bg-soft-sand hover:border-intense-cocoa/40' }}"
                    >
                        {{ __('blog.storefront.all_categories') }}
                    </button>

                    @foreach ($categories as $cat)
                        @if ($loop->iteration <= $desktopLimit)
                            <button
                                type="button"
                                wire:click="selectCategory('{{ $cat->slug }}')"
                                class="shrink-0 px-5 py-2 text-label-caps font-semibold uppercase tracking-widest transition-all duration-300 rounded-none {{ $activeCategory === $cat->slug ? 'bg-intense-cocoa text-silk-cream shadow-sm' : 'border border-intense-cocoa/20 text-intense-cocoa hover:bg-soft-sand hover:border-intense-cocoa/40' }}"
                            >
                                {{ $cat->getLocalizedName() }}
                            </button>
                        @else
                            <button
                                type="button"
                                x-show="expanded"
                                x-cloak
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 -translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                wire:click="selectCategory('{{ $cat->slug }}')"
                                class="shrink-0 px-5 py-2 text-label-caps font-semibold uppercase tracking-widest transition-all duration-300 rounded-none {{ $activeCategory === $cat->slug ? 'bg-intense-cocoa text-silk-cream shadow-sm' : 'border border-intense-cocoa/20 text-intense-cocoa hover:bg-soft-sand hover:border-intense-cocoa/40' }}"
                            >
                                {{ $cat->getLocalizedName() }}
                            </button>
                        @endif
                    @endforeach

                    @if ($hasOverflowCategories)
                        <button
                            type="button"
                            x-on:click="expanded = !expanded"
                            class="shrink-0 px-4 py-2 text-label-caps font-semibold uppercase tracking-widest text-soft-gold border border-soft-gold/40 hover:bg-soft-gold/10 hover:border-soft-gold transition-all duration-300 inline-flex items-center gap-1.5 cursor-pointer"
                            :aria-expanded="expanded"
                        >
                            <span x-text="expanded ? '{{ __('blog.storefront.show_less') }}' : '{{ __('blog.storefront.show_more') }} (+{{ $categories->count() - $desktopLimit }})'"></span>
                            <svg class="h-3.5 w-3.5 transition-transform duration-300" :class="expanded ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                    @endif
                </div>
            </div>
        @endif

        {{-- Articles Grid --}}
        @if ($posts->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-gutter">
                @foreach ($posts as $post)
                    <x-article-card :post="$post" heading-tag="h2" />
                @endforeach
            </div>

            {{-- Pagination --}}
            <x-pagination :paginator="$posts" class="mt-14" />
        @else
            {{-- Empty State --}}
            <div class="bg-soft-sand/40 border border-intense-cocoa/10 py-16 px-6 text-center max-w-xl mx-auto my-12">
                <svg class="mx-auto h-12 w-12 text-intense-cocoa/40 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                </svg>
                <h3 class="font-chillax text-headline-sm text-intense-cocoa mb-2">
                    {{ __('blog.storefront.empty_heading') }}
                </h3>
                <p class="font-sans text-body-md text-intense-cocoa/70">
                    @if (filled($search))
                        {{ __('blog.storefront.no_search_results', ['term' => $search]) }}
                    @else
                        {{ __('blog.storefront.empty_description') }}
                    @endif
                </p>
                @if ($activeCategory || filled($search))
                    <div class="mt-6 flex justify-center">
                        <x-primary-button
                            type="button"
                            wire:click="resetFilters"
                        >
                            {{ __('blog.storefront.reset_filters') }}
                        </x-primary-button>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
