<div class="py-8 lg:py-12">
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

    {{-- Breadcrumb --}}
    <x-breadcrumb.breadcrumb :items="array_values(array_filter([
        ['label' => __('storefront.nav.home'), 'href' => url('/')],
        ['label' => __('storefront.nav.blog'), 'href' => route('blog.index')],
        $post->category ? ['label' => $post->category->getLocalizedName(), 'href' => route('blog.index', ['category' => $post->category->slug])] : null,
        ['label' => $post->getLocalizedTitle()],
    ]))" />

    <div class="mx-auto max-w-storefront px-margin-mobile lg:px-margin-desktop">
        {{-- Admin Preview Warning Banner --}}
        @if ($isPreview)
            <div class="mb-8 max-w-3xl mx-auto">
                <x-alert type="info">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 shrink-0 text-intense-cocoa" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                        </svg>
                        <span>{{ __('blog.storefront.preview_notice') }}</span>
                    </div>
                </x-alert>
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

            <h1 class="font-chillax text-display-lg-mobile md:text-display-lg text-intense-cocoa font-normal tracking-tight leading-tight">
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

            <div class="prose-editorial max-w-none">
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
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z" />
                            <path d="M12.001 2C6.478 2 2 6.477 2 12c0 1.865.51 3.649 1.475 5.209L2 22l4.938-1.446A9.947 9.947 0 0 0 12.001 22C17.523 22 22 17.523 22 12S17.523 2 12.001 2zm0 18.001a8.02 8.02 0 0 1-4.315-1.255l-.31-.184-3.226.944.955-3.146-.202-.322A7.996 7.996 0 0 1 4.001 12c0-4.412 3.588-8 8-8 4.411 0 8 3.588 8 8s-3.589 8.001-8 8.001z" />
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
                        <x-article-card :post="$related" :show-excerpt="false" heading-tag="h3" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</div>
