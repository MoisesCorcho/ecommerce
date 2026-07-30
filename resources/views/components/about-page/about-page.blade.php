@php
    $pillars = __('about.pillars.items');
    $differential = __('about.differential');
    $galleryImages = [
        '/images/about/banner-9.jpg',
        '/images/about/05.jpeg',
        '/images/about/18.jpg',
        '/images/about/09.jpeg',
        '/images/about/14.jpeg',
    ];

    $pillarIcons = [
        'workspace_premium' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />',
        'hourglass_empty' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />',
        'diamond' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />',
        'front_hand' => '<path stroke-linecap="round" stroke-linejoin="round" d="M10.05 4.575a1.575 1.575 0 10-3.15 0v3m3.15-3v-1.5a1.575 1.575 0 013.15 0v1.5m-3.15 0l-.075 5.925m3.075-5.925v4.5m0-4.5l-.075 5.925m0 0a1.575 1.575 0 003.15 0m-3.15 0l.075 5.925m3.075-5.925v4.5m0-4.5l-.075 5.925m0 0a1.575 1.575 0 003.15 0m-6.3-3.15v4.5m0-4.5l-.075 5.925m0 0a1.575 1.575 0 003.15 0m-3.15 0l.075 5.925M6.75 7.5h.75v.75h-.75V7.5zm0 3h.75v.75h-.75v-.75zm0 3h.75v.75h-.75v-.75z" />',
    ];
@endphp

<div
    class="py-8 lg:py-12"
    x-data="{
        lightboxOpen: false,
        lightboxIndex: 0,
        images: {{ json_encode($galleryImages) }},
        openLightbox(index) {
            this.lightboxIndex = index;
            this.lightboxOpen = true;
            document.body.style.overflow = 'hidden';
        },
        closeLightbox() {
            this.lightboxOpen = false;
            document.body.style.overflow = '';
        },
        nextImage() {
            this.lightboxIndex = (this.lightboxIndex + 1) % this.images.length;
        },
        prevImage() {
            this.lightboxIndex = (this.lightboxIndex - 1 + this.images.length) % this.images.length;
        }
    }"
    x-on:keydown.escape.window="lightboxOpen && closeLightbox()"
    x-on:keydown.arrow-right.window="lightboxOpen && nextImage()"
    x-on:keydown.arrow-left.window="lightboxOpen && prevImage()"
>
    {{-- Breadcrumb --}}
    <x-breadcrumb.breadcrumb :items="[
        ['label' => __('about.breadcrumb.home'), 'href' => route('home')],
        ['label' => __('about.breadcrumb.about')],
    ]"></x-breadcrumb.breadcrumb>

    {{-- 1. Hero Section --}}
    <section class="relative isolate flex h-[50vh] min-h-[400px] w-full flex-col items-center justify-center overflow-hidden" aria-label="{{ __('about.hero.title') }}">
        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('/images/about/17.jpg')">
            <div class="absolute inset-0 bg-intense-cocoa/40" style="mix-blend-mode: multiply"></div>
        </div>
        <div class="reveal relative z-10 mx-auto max-w-4xl px-6 text-center">
            <h1 class="font-chillax text-display-lg-mobile md:text-display-lg text-silk-cream">
                {{ __('about.hero.title') }}
            </h1>
            <p class="mt-4 font-labelle-aurore text-accent-script text-soft-gold">
                {{ __('about.hero.subtitle') }}
            </p>
        </div>
    </section>

    <div class="mx-auto max-w-storefront px-margin-mobile lg:px-margin-desktop">

        {{-- 2. Story Section --}}
        <section class="grid grid-cols-1 items-center gap-6 py-section-gap md:grid-cols-2 lg:gap-gutter">
            <div class="reveal">
                <h2 class="font-chillax text-headline-md text-intense-cocoa">
                    {{ __('about.story.title') }}
                </h2>
                @foreach (__('about.story.paragraphs') as $paragraph)
                    <p class="mt-6 text-body-md leading-relaxed text-intense-cocoa/70">
                        {{ $paragraph }}
                    </p>
                @endforeach
            </div>
            <div class="reveal delay-100 relative h-[400px] bg-soft-sand md:h-[500px] lg:h-[600px]">
                <img
                    src="/images/about/banner-2.png"
                    alt="{{ __('about.story.title') }}"
                    class="h-full w-full object-contain p-4"
                    loading="lazy"
                >
            </div>
        </section>

        {{-- 3. Pillars Section --}}
        <section class="reveal bg-soft-sand px-8 py-section-gap md:px-16">
            <h2 class="font-chillax text-headline-md text-intense-cocoa text-center">
                {{ __('about.pillars.title') }}
            </h2>
            <div class="mt-16 grid grid-cols-1 gap-12 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($pillars as $pillar)
                    <div class="flex flex-col items-center text-center">
                        <div class="flex h-16 w-16 items-center justify-center bg-silk-cream text-intense-cocoa transition-colors duration-300 hover:bg-soft-gold/20">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                {!! $pillarIcons[$pillar['icon']] ?? '' !!}
                            </svg>
                        </div>
                        <h3 class="mt-6 text-label-caps font-semibold uppercase tracking-wider text-intense-cocoa">
                            {{ $pillar['title'] }}
                        </h3>
                        <p class="mt-3 text-sm text-intense-cocoa/60">
                            {{ $pillar['description'] }}
                        </p>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- 4. Differential Section --}}
        <section class="grid grid-cols-1 items-center gap-6 py-section-gap md:grid-cols-2 lg:gap-gutter">
            <div class="reveal relative h-[400px] md:h-[500px] lg:h-[600px]">
                <img
                    src="/images/about/banner-8.jpg"
                    alt="{{ __('about.differential.title') }}"
                    class="h-full w-full object-contain"
                    loading="lazy"
                >
            </div>
            <div class="reveal delay-100">
                <h2 class="font-chillax text-headline-md text-intense-cocoa">
                    {{ __('about.differential.title') }}
                </h2>
                <p class="mt-6 text-body-md leading-relaxed text-intense-cocoa/70">
                    {{ __('about.differential.description') }}
                </p>
                <ul class="mt-6 space-y-3">
                    @foreach (__('about.differential.bullets') as $bullet)
                        <li class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-soft-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-body-md text-intense-cocoa/70">{{ $bullet }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>

        {{-- 5. Gallery Section --}}
        @if(count($galleryImages) > 0)
        <section class="py-section-gap">
            <h2 class="reveal font-chillax text-headline-md text-intense-cocoa text-center">
                {{ __('about.gallery.title') }}
            </h2>
            <div class="mt-16 grid grid-cols-2 gap-4 md:grid-cols-3">
                @foreach ($galleryImages as $i => $image)
                    <div
                        class="reveal group relative aspect-square cursor-pointer overflow-hidden"
                        x-on:click="openLightbox({{ $i }})"
                        role="button"
                        tabindex="0"
                        x-on:keydown.enter="openLightbox({{ $i }})"
                        aria-label="{{ __('about.gallery.title') }} {{ $i + 1 }}"
                    >
                        <img
                            src="{{ $image }}"
                            alt="{{ __('about.gallery.title') }} {{ $i + 1 }}"
                            loading="lazy"
                            class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                        >
                        <div class="absolute inset-0 flex items-center justify-center bg-intense-cocoa/0 transition-colors duration-300 group-hover:bg-intense-cocoa/20">
                            <svg class="h-8 w-8 text-silk-cream opacity-0 transition-opacity duration-300 group-hover:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                            </svg>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
        @endif

    </div>

    {{-- 6. CTA Section (full-width, outside container) --}}
    <section class="bg-intense-cocoa py-20 text-center">
        <h2 class="font-chillax text-headline-md text-silk-cream">
            {{ __('about.cta.heading') }}
        </h2>
        <a
            href="{{ route('products.index') }}"
            class="mt-8 inline-block bg-silk-cream px-10 py-4 text-label-caps font-semibold text-intense-cocoa transition-colors duration-300 hover:bg-soft-gold"
        >
            {{ __('about.cta.button') }}
        </a>
    </section>

    {{-- Lightbox overlay --}}
    <div
        x-show="lightboxOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-intense-cocoa/80"
        role="dialog"
        aria-label="{{ __('about.gallery.title') }}"
        aria-modal="true"
    >
        {{-- Image counter --}}
        <div class="absolute top-6 left-1/2 -translate-x-1/2 text-silk-cream text-sm font-medium">
            <span x-text="(lightboxIndex + 1) + ' / ' + images.length"></span>
        </div>

        {{-- Close button --}}
        <button
            x-on:click="closeLightbox()"
            class="absolute top-4 right-4 text-silk-cream transition-colors hover:text-soft-gold sm:top-6 sm:right-6"
            aria-label="Close"
        >
            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        {{-- Previous button --}}
        <button
            x-on:click="prevImage()"
            class="absolute left-2 text-silk-cream transition-colors hover:text-soft-gold sm:left-6"
            aria-label="Previous"
        >
            <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
            </svg>
        </button>

        {{-- Image --}}
        <img
            :src="images[lightboxIndex]"
            :alt="'{{ __('about.gallery.title') }} ' + (lightboxIndex + 1)"
            class="max-h-[80vh] max-w-[90vw] object-contain"
        >

        {{-- Next button --}}
        <button
            x-on:click="nextImage()"
            class="absolute right-2 text-silk-cream transition-colors hover:text-soft-gold sm:right-6"
            aria-label="Next"
        >
            <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
            </svg>
        </button>
    </div>
</div>
