@props([
    'post',
    'showExcerpt' => true,
    'headingTag' => 'h2',
])

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

        <{{ $headingTag }} class="font-chillax text-headline-sm text-intense-cocoa font-medium leading-snug group-hover:text-soft-gold transition-colors duration-300 line-clamp-2">
            <a href="{{ route('blog.show', ['slug' => $post->slug]) }}">
                {{ $post->getLocalizedTitle() }}
            </a>
        </{{ $headingTag }}>

        @if ($showExcerpt && $post->getLocalizedExcerpt())
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
