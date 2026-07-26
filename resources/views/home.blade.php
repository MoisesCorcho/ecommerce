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
            <a
                href="{{ route('products.index') }}"
                class="mt-4 inline-block bg-intense-cocoa px-8 py-4 text-label-caps font-semibold uppercase tracking-widest text-silk-cream transition-colors duration-300 hover:bg-soft-gold hover:text-intense-cocoa"
            >
                {{ __('storefront.home.hero_cta') }}
            </a>
        </div>
    </section>

    {{-- 2. Categories grid (dynamic, R6/R16) --}}
    <div class="mx-auto max-w-storefront px-margin-mobile lg:px-margin-desktop">
        <livewire:categories-grid />
    </div>

    {{-- Subtle separator --}}
    <div class="mx-auto max-w-storefront px-margin-mobile lg:px-margin-desktop">
        <hr class="border-t border-intense-cocoa/10" aria-hidden="true">
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
                    class="mt-10 inline-block border border-soft-gold px-8 py-3 text-label-caps font-semibold uppercase tracking-widest text-intense-cocoa transition-colors duration-300 hover:border-intense-cocoa hover:bg-intense-cocoa hover:text-silk-cream"
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
                    ['title' => __('storefront.home.benefit_1_title'), 'desc' => __('storefront.home.benefit_1_desc')],
                    ['title' => __('storefront.home.benefit_2_title'), 'desc' => __('storefront.home.benefit_2_desc')],
                    ['title' => __('storefront.home.benefit_3_title'), 'desc' => __('storefront.home.benefit_3_desc')],
                    ['title' => __('storefront.home.benefit_4_title'), 'desc' => __('storefront.home.benefit_4_desc')],
                ] as $benefit)
                    <div class="text-center">
                        <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-full border border-intense-cocoa/15">
                            <svg class="h-6 w-6 text-intense-cocoa" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
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
    <section class="mx-auto max-w-storefront px-margin-mobile py-section-gap lg:px-margin-desktop" aria-labelledby="instagram-heading">
        <h2 id="instagram-heading" class="mb-12 text-center font-chillax text-headline-md text-intense-cocoa">
            {{ __('storefront.home.instagram_title') }}
        </h2>
        <div class="grid grid-cols-3 gap-2 md:grid-cols-6">
            @foreach (range(1, 6) as $i)
                <a
                    href="https://instagram.com/leenhandbags"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="aspect-square overflow-hidden rounded-none bg-soft-sand transition-opacity duration-300 hover:opacity-80"
                    aria-label="{{ __('storefront.home.instagram_cta') }}"
                >
                    <div class="flex h-full w-full items-center justify-center">
                        <span class="text-label-caps text-intense-cocoa/30" aria-hidden="true">@</span>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="mt-10 text-center">
            <a
                href="https://instagram.com/leenhandbags"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-block text-label-caps font-semibold uppercase tracking-widest text-intense-cocoa underline underline-offset-4 transition-colors duration-300 hover:text-soft-gold"
            >
                {{ __('storefront.home.instagram_cta') }}
            </a>
        </div>
    </section>
</x-layouts::storefront>
