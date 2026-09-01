<div class="bg-silk-cream min-h-screen py-10 lg:py-16">
    {{-- OpenGraph and SEO Tags --}}
    @section('meta')
        <title>{{ $post->getLocalizedMetaTitle() }}</title>
        <meta name="description" content="{{ $post->getLocalizedMetaDescription() }}">
        <meta property="og:title" content="{{ $post->getLocalizedMetaTitle() }}">
        <meta property="og:description" content="{{ $post->getLocalizedMetaDescription() }}">
        <meta property="og:type" content="article">
        <meta property="og:url" content="{{ url()->current() }}">
        @if ($post->cover_image_path)
            <meta property="og:image" content="{{ Storage::url($post->cover_image_path) }}">
        @endif
    @endsection

    <div class="mx-auto max-w-storefront px-margin-mobile lg:px-margin-desktop">
        {{-- Breadcrumb --}}
        <nav class="mb-8 flex items-center gap-2 text-label-caps font-semibold uppercase tracking-widest text-intense-cocoa/60" aria-label="Breadcrumb">
            <a href="{{ url('/') }}" class="transition-colors hover:text-soft-gold">{{ __('storefront.nav.home') }}</a>
            <span class="text-intense-cocoa/30">/</span>
            <a href="{{ route('blog.index') }}" class="transition-colors hover:text-soft-gold">{{ __('storefront.nav.blog') }}</a>
            @if ($post->category)
                <span class="text-intense-cocoa/30">/</span>
                <a href="{{ route('blog.index', ['category' => $post->category->slug]) }}" class="transition-colors hover:text-soft-gold">
                    {{ $post->category->getLocalizedName() }}
                </a>
            @endif
            <span class="text-intense-cocoa/30">/</span>
            <span class="text-intense-cocoa truncate max-w-[200px] sm:max-w-xs">{{ $post->getLocalizedTitle() }}</span>
        </nav>

        {{-- Admin Preview Warning Banner --}}
        @if ($isPreview)
            <div class="mb-8 bg-terracotta/10 border border-terracotta text-terracotta px-4 py-3 text-sm flex items-center gap-3">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                <span><strong>Modo Previsualización:</strong> Este artículo está en borrador o programado a futuro y solo es visible para administradores.</span>
            </div>
        @endif

        {{-- Article Header --}}
        <header class="max-w-3xl mx-auto text-center mb-10 lg:mb-12">
            @if ($post->category)
                <div class="mb-4">
                    <a
                        href="{{ route('blog.index', ['category' => $post->category->slug]) }}"
                        class="inline-block bg-soft-sand px-3.5 py-1.5 text-label-caps font-semibold uppercase tracking-widest text-intense-cocoa hover:bg-soft-gold/20 transition-colors"
                    >
                        {{ $post->category->getLocalizedName() }}
                    </a>
                </div>
            @endif

            <h1 class="font-chillax text-display-lg text-intense-cocoa font-normal tracking-tight leading-tight">
                {{ $post->getLocalizedTitle() }}
            </h1>

            <div class="mt-6 flex flex-wrap items-center justify-center gap-3 text-label-caps font-semibold uppercase tracking-widest text-intense-cocoa/60">
                @if ($post->author)
                    <span>{{ __('blog.storefront.by_author', ['author' => $post->author->name]) }}</span>
                    <span>•</span>
                @endif
                @if ($post->published_at)
                    <time datetime="{{ $post->published_at->toIso8601String() }}">
                        {{ __('blog.storefront.published_on', ['date' => $post->published_at->translatedFormat('d M, Y')]) }}
                    </time>
                    <span>•</span>
                @endif
                <span>{{ __('blog.storefront.reading_time', ['min' => $post->readingTime()]) }}</span>
            </div>
        </header>

        {{-- Main Cover Image --}}
        @if ($post->cover_image_path)
            <div class="max-w-4xl mx-auto mb-12 lg:mb-16 aspect-[16/9] overflow-hidden bg-soft-sand shadow-ambient">
                <img
                    src="{{ Storage::url($post->cover_image_path) }}"
                    alt="{{ $post->getLocalizedTitle() }}"
                    class="w-full h-full object-cover rounded-none"
                >
            </div>
        @endif

        {{-- Article Content (Prose Styling) --}}
        <article class="max-w-3xl mx-auto">
            @if ($post->getLocalizedExcerpt())
                <div class="mb-10 text-xl md:text-2xl font-chillax text-intense-cocoa/90 leading-relaxed italic border-l-2 border-soft-gold pl-6 py-1 bg-soft-sand/20">
                    {{ $post->getLocalizedExcerpt() }}
                </div>
            @endif

            <div class="font-sans text-body-lg text-intense-cocoa/85 leading-relaxed prose prose-intense-cocoa max-w-none space-y-6">
                {!! $post->getLocalizedContent() !!}
            </div>

            {{-- Footer / Share / Back --}}
            <div class="mt-14 pt-8 border-t border-intense-cocoa/10 flex flex-wrap items-center justify-between gap-4">
                <a
                    href="{{ route('blog.index') }}"
                    class="inline-flex items-center gap-2 text-label-caps font-semibold uppercase tracking-widest text-intense-cocoa hover:text-soft-gold transition-colors"
                >
                    <svg class="h-4 w-4 rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                    <span>{{ __('blog.storefront.back_to_blog') }}</span>
                </a>

                <div class="flex items-center gap-3">
                    <span class="text-label-caps font-semibold uppercase tracking-widest text-intense-cocoa/60">{{ __('blog.storefront.share') }}:</span>
                    <a
                        href="https://api.whatsapp.com/send?text={{ urlencode($post->getLocalizedTitle() . ' ' . url()->current()) }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="p-2 text-intense-cocoa/70 hover:text-soft-gold transition-colors"
                        aria-label="WhatsApp"
                    >
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.969.54 1.861.855 2.796.856 3.183 0 5.769-2.587 5.769-5.766.001-3.181-2.585-5.767-5.769-5.767zm0 10.457c-.822 0-1.62-.218-2.316-.63l-.165-.098-1.719.451.459-1.674-.108-.172a4.67 4.67 0 0 1-.715-2.568c.001-2.58 2.099-4.677 4.68-4.677 2.581 0 4.679 2.097 4.679 4.677 0 2.581-2.099 4.678-4.68 4.678zm-6.222-14.73C3.606 4.098 2 7.828 2 11.938c0 2.115.549 4.148 1.587 5.952L2 23.477l5.748-1.508a9.92 9.92 0 0 0 4.283.97h.004c5.485 0 9.95-4.466 9.952-9.952 0-2.658-1.035-5.157-2.915-7.037A9.885 9.885 0 0 0 12.035 1.899z"/>
                        </svg>
                    </a>
                </div>
            </div>
        </article>

        {{-- Related Posts Section --}}
        @if ($relatedPosts->isNotEmpty())
            <section class="mt-20 lg:mt-24 pt-16 border-t border-intense-cocoa/10">
                <div class="text-center mb-12">
                    <h2 class="font-chillax text-headline-md text-intense-cocoa font-normal">
                        {{ __('blog.storefront.related_posts_heading') }}
                    </h2>
                    <p class="font-labelle-aurore text-accent-script text-soft-gold mt-1">
                        {{ __('blog.storefront.related_posts_subtitle') }}
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-gutter">
                    @foreach ($relatedPosts as $related)
                        <article class="group flex flex-col bg-soft-sand/30 border border-intense-cocoa/10 overflow-hidden transition-all duration-300 hover:shadow-ambient hover:border-intense-cocoa/20">
                            <a href="{{ route('blog.show', ['slug' => $related->slug]) }}" class="relative aspect-[16/10] overflow-hidden bg-soft-sand block">
                                @if ($related->cover_image_path)
                                    <img
                                        src="{{ Storage::url($related->cover_image_path) }}"
                                        alt="{{ $related->getLocalizedTitle() }}"
                                        loading="lazy"
                                        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 rounded-none"
                                    >
                                @else
                                    <div class="w-full h-full flex flex-col items-center justify-center p-6 bg-gradient-to-br from-soft-sand to-soft-sand/60">
                                        <img src="/images/logos/leen-brown.png" alt="Leen" class="h-8 w-auto opacity-30">
                                    </div>
                                @endif

                                @if ($related->category)
                                    <div class="absolute top-3 left-3">
                                        <span class="bg-silk-cream/95 backdrop-blur-xs text-intense-cocoa px-2.5 py-1 text-label-caps font-semibold uppercase tracking-wider shadow-xs">
                                            {{ $related->category->getLocalizedName() }}
                                        </span>
                                    </div>
                                @endif
                            </a>

                            <div class="p-6 flex flex-col flex-1">
                                <div class="flex items-center gap-2 text-label-caps text-intense-cocoa/60 mb-2">
                                    <span>{{ __('blog.storefront.reading_time', ['min' => $related->readingTime()]) }}</span>
                                </div>

                                <h3 class="font-chillax text-headline-sm text-intense-cocoa font-medium leading-snug group-hover:text-soft-gold transition-colors duration-300 line-clamp-2">
                                    <a href="{{ route('blog.show', ['slug' => $related->slug]) }}">
                                        {{ $related->getLocalizedTitle() }}
                                    </a>
                                </h3>

                                <div class="mt-4 pt-4 border-t border-intense-cocoa/10 flex items-center justify-between">
                                    <a
                                        href="{{ route('blog.show', ['slug' => $related->slug]) }}"
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
            </section>
        @endif
    </div>
</div>
