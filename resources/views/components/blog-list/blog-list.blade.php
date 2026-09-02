<div class="bg-silk-cream min-h-screen py-12 lg:py-16">
    <div class="mx-auto max-w-storefront px-margin-mobile lg:px-margin-desktop">
        {{-- Breadcrumb --}}
        <nav class="mb-8 flex items-center gap-2 text-label-caps font-semibold uppercase tracking-widest text-intense-cocoa/60" aria-label="Breadcrumb">
            <a href="{{ url('/') }}" class="transition-colors hover:text-soft-gold">{{ __('storefront.nav.home') }}</a>
            <span class="text-intense-cocoa/30">/</span>
            <span class="text-intense-cocoa">{{ __('storefront.nav.blog') }}</span>
        </nav>

        {{-- Hero Editorial Header --}}
        <div class="mb-14 text-center">
            <h1 class="font-chillax text-display-lg text-intense-cocoa font-normal tracking-tight">
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
                    <article class="group flex flex-col bg-soft-sand/30 border border-intense-cocoa/10 overflow-hidden transition-all duration-300 hover:shadow-ambient hover:border-intense-cocoa/20">
                        {{-- Cover Image --}}
                        <a href="{{ route('blog.show', ['slug' => $post->slug]) }}" class="relative aspect-[16/10] overflow-hidden bg-soft-sand block">
                            @if ($post->cover_image_path)
                                <img
                                    src="{{ Storage::url($post->cover_image_path) }}"
                                    alt="{{ $post->getLocalizedTitle() }}"
                                    loading="lazy"
                                    class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 rounded-none"
                                >
                            @else
                                <div class="w-full h-full flex flex-col items-center justify-center p-6 bg-gradient-to-br from-soft-sand to-soft-sand/60">
                                    <img src="/images/logos/leen-brown.png" alt="Leen" class="h-10 w-auto opacity-30">
                                    <span class="font-labelle-aurore text-sm text-intense-cocoa/40 mt-2">Leen Journal</span>
                                </div>
                            @endif

                            @if ($post->category)
                                <div class="absolute top-3 left-3">
                                    <span class="bg-silk-cream/95 backdrop-blur-xs text-intense-cocoa px-3 py-1 text-label-caps font-semibold uppercase tracking-wider shadow-xs">
                                        {{ $post->category->getLocalizedName() }}
                                    </span>
                                </div>
                            @endif
                        </a>

                        {{-- Card Body --}}
                        <div class="p-6 sm:p-7 flex flex-col flex-1">
                            <div class="flex items-center gap-3 text-label-caps text-intense-cocoa/60 mb-3">
                                @if ($post->published_at)
                                    <time datetime="{{ $post->published_at->toIso8601String() }}">
                                        {{ $post->published_at->translatedFormat('d M, Y') }}
                                    </time>
                                    <span>•</span>
                                @endif
                                <span>{{ __('blog.storefront.reading_time', ['min' => $post->readingTime()]) }}</span>
                            </div>

                            <h2 class="font-chillax text-headline-sm text-intense-cocoa font-medium leading-snug group-hover:text-soft-gold transition-colors duration-300 line-clamp-2">
                                <a href="{{ route('blog.show', ['slug' => $post->slug]) }}">
                                    {{ $post->getLocalizedTitle() }}
                                </a>
                            </h2>

                            @if ($post->getLocalizedExcerpt())
                                <p class="font-sans text-body-md text-intense-cocoa/75 mt-3 line-clamp-3 leading-relaxed flex-1">
                                    {{ $post->getLocalizedExcerpt() }}
                                </p>
                            @endif

                            <div class="mt-auto pt-6 border-t border-intense-cocoa/10 flex items-center justify-between">
                                <a
                                    href="{{ route('blog.show', ['slug' => $post->slug]) }}"
                                    class="inline-flex items-center gap-2 text-label-caps font-semibold uppercase tracking-widest text-intense-cocoa group-hover:text-soft-gold transition-colors duration-300"
                                >
                                    <span>{{ __('blog.storefront.read_more') }}</span>
                                    <svg class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-14">
                {{ $posts->links() }}
            </div>
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
                    <button
                        type="button"
                        wire:click="selectCategory(null)"
                        class="mt-6 inline-block bg-intense-cocoa text-silk-cream px-6 py-2.5 text-label-caps font-semibold uppercase tracking-widest hover:bg-soft-gold transition-colors duration-300"
                    >
                        {{ __('blog.storefront.all_categories') }}
                    </button>
                @endif
            </div>
        @endif
    </div>
</div>
