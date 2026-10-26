<x-layouts::storefront>
    {{-- 1. Hero — static Blade (D1), ~70vh, CTA → products.index (PD-S4) --}}
    <section
        class="relative flex min-h-[70vh] items-center justify-center overflow-hidden bg-soft-sand bg-cover bg-center px-margin-mobile py-24 text-center lg:px-margin-desktop"
        style="background-image: url('{{ asset('images/banners/banner-10.jpg') }}')"
        aria-label="{{ __('storefront.home.hero_title') }}"
    >
        <div class="absolute inset-0 bg-silk-cream/30" aria-hidden="true"></div>
        <div class="relative z-10 flex max-w-3xl flex-col items-center gap-6">
            <p class="font-labelle-aurore text-accent-script text-intense-cocoa">
                {{ __('storefront.home.hero_subtitle') }}
            </p>
            <h1 class="font-chillax text-display-lg-mobile text-intense-cocoa md:text-display-lg">
                {{ __('storefront.home.hero_title') }}
            </h1>
            <x-primary-button tag="a" href="{{ route('products.index') }}" class="mt-4 inline-block px-8 py-4 text-xs font-semibold tracking-widest">
                {{ __('storefront.home.hero_cta') }}
            </x-primary-button>
        </div>
    </section>

    {{-- 2. Categories grid (dynamic, R6/R16) --}}
    <div class="mx-auto max-w-storefront px-margin-mobile lg:px-margin-desktop">
        <livewire:categories-grid />
    </div>

    {{-- Subtle separator --}}
    <div class="mx-auto max-w-storefront px-margin-mobile lg:px-margin-desktop">
        <hr class="border-t border-intense-cocoa/20" aria-hidden="true">
    </div>

    {{-- 3. Featured products (dynamic, R7/R18) --}}
    <div class="mx-auto max-w-storefront px-margin-mobile lg:px-margin-desktop">
        <livewire:featured-products-grid />
    </div>

    {{-- 4. Brand story — static Blade (D1), CTA → /about-us (R8a) --}}
    <section class="mx-auto max-w-storefront px-margin-mobile pt-16 pb-section-gap lg:px-margin-desktop lg:pt-24" aria-labelledby="story-heading">
        <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-24">
            <div>
                <h2 id="story-heading" class="font-chillax text-headline-md text-intense-cocoa">
                    {{ __('storefront.home.story_title') }}
                </h2>
                <p class="mt-6 text-body-lg leading-relaxed text-intense-cocoa/80">
                    {{ __('storefront.home.story_body') }}
                </p>
                <a
                    href="/about-us"
                    class="mt-10 inline-block border border-soft-gold px-8 py-3 text-label-caps font-semibold text-intense-cocoa transition-colors duration-300 hover:border-intense-cocoa hover:bg-intense-cocoa hover:text-silk-cream"
                >
                    {{ __('storefront.home.story_cta') }}
                </a>
            </div>
            <div class="aspect-[4/5] overflow-hidden rounded-none bg-soft-sand">
                <img
                    src="{{ asset('images/banners/banner-4.jpeg') }}"
                    alt="{{ __('storefront.home.story_title') }}"
                    class="h-full w-full object-cover"
                    loading="lazy"
                >
            </div>
        </div>
    </section>

    {{-- 5. Benefits — static Blade (D1), bg-soft-sand, 4-column grid --}}
    <section
        class="bg-soft-sand"
        aria-labelledby="benefits-heading"
    >
        <div class="mx-auto max-w-storefront px-margin-mobile py-section-gap lg:px-margin-desktop">
            <h2 id="benefits-heading" class="mb-16 text-center font-chillax text-headline-md text-intense-cocoa">
                {{ __('storefront.home.benefits_title') }}
            </h2>
            <div class="grid grid-cols-2 gap-10 lg:grid-cols-4 lg:gap-8">
                @foreach ([
                    [
                        'title' => __('storefront.home.benefit_1_title'),
                        'desc' => __('storefront.home.benefit_1_desc'),
                        'icon' => 'needle',
                    ],
                    [
                        'title' => __('storefront.home.benefit_2_title'),
                        'desc' => __('storefront.home.benefit_2_desc'),
                        'icon' => 'diamond',
                    ],
                    [
                        'title' => __('storefront.home.benefit_3_title'),
                        'desc' => __('storefront.home.benefit_3_desc'),
                        'icon' => 'sparkle-clock',
                    ],
                    [
                        'title' => __('storefront.home.benefit_4_title'),
                        'desc' => __('storefront.home.benefit_4_desc'),
                        'icon' => 'colombia',
                    ],
                ] as $benefit)
                    <div class="text-center">
                        <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-full border border-intense-cocoa/15">
                            @if ($benefit['icon'] === 'needle')
                                {{-- Needle with thread --}}
                                <svg class="h-6 w-6 text-intense-cocoa" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M12 21a9 9 0 0 1-9-9c0-4.97 3.58-9 8-9 4.42 0 8 3.58 8 9 0 3.2-1.7 6.02-4.25 7.5" />
                                    <path d="M15 9.5 8 16.5" />
                                    <path d="m17.5 7 1.5 1.5" />
                                    <path d="M20 4.5 16.5 8" />
                                    <circle cx="18.5" cy="6" r="0.75" fill="currentColor" />
                                </svg>
                            @elseif ($benefit['icon'] === 'diamond')
                                {{-- Heroicons: Sparkles (Lujo consciente / Destellos de magia) --}}
                                <svg class="h-6 w-6 text-intense-cocoa" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
                                </svg>
                            @elseif ($benefit['icon'] === 'sparkle-clock')
                                {{-- Heroicons: Clock (Slow fashion / Tiempo paciente) --}}
                                <svg class="h-6 w-6 text-intense-cocoa" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            @elseif ($benefit['icon'] === 'colombia')
                                {{-- Heroicons: Map Pin (Origen Colombia / Taller local) --}}
                                <svg class="h-6 w-6 text-intense-cocoa" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                </svg>
                            @endif
                        </div>
                        <h3 class="text-label-caps font-semibold uppercase tracking-widest text-intense-cocoa">
                            {{ $benefit['title'] }}
                        </h3>
                        <p class="mt-3 text-body-md text-intense-cocoa/70">
                            {{ $benefit['desc'] }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 6. Instagram — static Blade (D1), external link (R8a) --}}
    @php
        $instagramPosts = [
            ['image' => 'post-1.webp', 'position' => '[object-position:center_65%]'],
            ['image' => 'post-2.webp', 'position' => '[object-position:center_65%]'],
            ['image' => 'post-3.webp', 'position' => '[object-position:center_66%]'],
            ['image' => 'post-4.webp', 'position' => '[object-position:center_20%]', 'scale' => 'scale-105 origin-[center_20%]', 'hover' => 'group-hover:scale-110'],
            ['image' => 'post-5.webp', 'position' => '[object-position:center_35%]'],
            ['image' => 'post-6.webp', 'position' => '[object-position:center_32%]', 'scale' => 'scale-[1.20] origin-[center_32%]', 'hover' => 'group-hover:scale-[1.25]'],
        ];
    @endphp
    <section class="mx-auto max-w-storefront px-margin-mobile py-section-gap lg:px-margin-desktop" aria-labelledby="instagram-heading">
        <h2 id="instagram-heading" class="mb-12 text-center font-chillax text-headline-md text-intense-cocoa">
            {{ __('storefront.home.instagram_title') }}
        </h2>
        <div class="grid grid-cols-3 gap-2 md:grid-cols-6">
            @foreach ($instagramPosts as $index => $post)
                <a
                    href="{{ config('ecommerce.contact.social.instagram') }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="group relative aspect-[4/5] overflow-hidden bg-soft-sand"
                    aria-label="{{ __('storefront.home.instagram_cta') }} - {{ $index + 1 }}"
                >
                    <img
                        src="{{ asset('images/instagram/' . $post['image']) }}"
                        alt="{{ __('storefront.home.instagram_title') }} {{ $index + 1 }}"
                        loading="lazy"
                        decoding="async"
                        class="h-full w-full object-cover {{ $post['position'] }} {{ $post['scale'] ?? '' }} transition-transform duration-500 ease-out {{ $post['hover'] ?? 'group-hover:scale-105' }}"
                    >
                    <div class="absolute inset-0 flex items-center justify-center bg-intense-cocoa/20 opacity-0 transition-opacity duration-300 group-hover:opacity-100" aria-hidden="true">
                        <svg class="h-6 w-6 text-white drop-shadow-sm" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                        </svg>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="mt-10 text-center">
            <a
                href="{{ config('ecommerce.contact.social.instagram') }}"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-block text-label-caps font-semibold uppercase tracking-widest text-intense-cocoa underline underline-offset-4 transition-colors duration-300 hover:text-soft-gold"
            >
                {{ __('storefront.home.instagram_cta') }}
            </a>
        </div>
    </section>
</x-layouts::storefront>
