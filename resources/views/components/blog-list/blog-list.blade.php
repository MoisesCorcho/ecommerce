<div class="py-8 lg:py-12">
    {{-- Breadcrumb --}}
    <x-breadcrumb.breadcrumb :items="[
        ['label' => __('storefront.nav.home'), 'href' => url('/')],
        ['label' => __('storefront.nav.blog')],
    ]" />

    <div class="mx-auto max-w-storefront px-margin-mobile lg:px-margin-desktop">
        {{-- Hero Editorial Header --}}
        <div class="mb-14 text-center">
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

        {{-- Categories Filter Pills --}}
        @if ($categories->isNotEmpty())
            <div class="mb-12 flex items-center justify-center">
                <div class="flex items-center gap-3 overflow-x-auto no-scrollbar py-2 px-1 max-w-full">
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
                    {{ __('blog.storefront.empty_description') }}
                </p>
                @if ($activeCategory)
                    <div class="mt-6 flex justify-center">
                        <x-primary-button
                            type="button"
                            wire:click="selectCategory(null)"
                        >
                            {{ __('blog.storefront.all_categories') }}
                        </x-primary-button>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
