<x-layouts::storefront>
    {{-- 1. Hero — static Blade (D1), ~70vh, CTA → products.index (PD-S4) --}}
    <section
        class="relative -mt-8 flex min-h-[70vh] items-center justify-center overflow-hidden bg-soft-sand px-margin-mobile py-20 text-center lg:px-margin-desktop"
        aria-label="{{ __('storefront.home.hero_title') }}"
    >
        <div class="relative z-10 max-w-2xl">
            <p class="font-labelle-aurore text-accent-script text-soft-gold">
                {{ __('storefront.home.hero_subtitle') }}
            </p>
            <h1 class="mt-4 font-chillax text-display-lg text-intense-cocoa md:text-[4rem] md:leading-[1.1]">
                {{ __('storefront.home.hero_title') }}
            </h1>
            <a
                href="{{ route('products.index') }}"
                class="mt-8 inline-block bg-intense-cocoa px-8 py-3 text-label-caps font-semibold uppercase tracking-wider text-silk-cream transition-colors hover:bg-soft-gold hover:text-intense-cocoa"
            >
                {{ __('storefront.home.hero_cta') }}
            </a>
        </div>
    </section>

    {{-- 2. Categories grid (dynamic, R6/R16) --}}
    <livewire:categories-grid />

    {{-- 3. Featured products (dynamic, R7/R18) --}}
    <livewire:featured-products-grid />

    {{-- 4. Brand story — static Blade (D1), CTA → /about-us (R8a) --}}
    <section class="py-16 lg:py-24" aria-labelledby="story-heading">
        <div class="grid items-center gap-8 lg:grid-cols-2 lg:gap-16">
            <div>
                <h2 id="story-heading" class="font-chillax text-headline-md text-intense-cocoa">
                    {{ __('storefront.home.story_title') }}
                </h2>
                <p class="mt-6 text-body-lg text-intense-cocoa/80">
                    {{ __('storefront.home.story_body') }}
                </p>
                <a
                    href="/about-us"
                    class="mt-8 inline-block text-label-caps font-semibold uppercase tracking-wider text-intense-cocoa underline underline-offset-4 transition-colors hover:text-soft-gold"
                >
                    {{ __('storefront.home.story_cta') }}
                </a>
            </div>
            <div class="aspect-[4/5] overflow-hidden rounded-none bg-soft-sand">
                <div class="flex h-full w-full items-center justify-center">
                    <span class="font-labelle-aurore text-accent-script text-intense-cocoa/30">Leen</span>
                </div>
            </div>
        </div>
    </section>

    {{-- 5. Benefits — static Blade (D1), bg-soft-sand, 4-column grid --}}
    <section
        class="-mx-margin-mobile bg-soft-sand px-margin-mobile py-16 lg:-mx-margin-desktop lg:px-margin-desktop"
        aria-labelledby="benefits-heading"
    >
        <h2 id="benefits-heading" class="mb-10 text-center font-chillax text-headline-md text-intense-cocoa">
            {{ __('storefront.home.benefits_title') }}
        </h2>
        <div class="grid grid-cols-2 gap-8 lg:grid-cols-4">
            @foreach ([
                ['title' => __('storefront.home.benefit_1_title'), 'desc' => __('storefront.home.benefit_1_desc')],
                ['title' => __('storefront.home.benefit_2_title'), 'desc' => __('storefront.home.benefit_2_desc')],
                ['title' => __('storefront.home.benefit_3_title'), 'desc' => __('storefront.home.benefit_3_desc')],
                ['title' => __('storefront.home.benefit_4_title'), 'desc' => __('storefront.home.benefit_4_desc')],
            ] as $benefit)
                <div class="text-center">
                    <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-soft-gold/20">
                        <span class="font-chillax text-headline-sm text-soft-gold" aria-hidden="true">&bull;</span>
                    </div>
                    <h3 class="text-label-caps font-semibold uppercase tracking-wider text-intense-cocoa">
                        {{ $benefit['title'] }}
                    </h3>
                    <p class="mt-2 text-body-md text-intense-cocoa/70">
                        {{ $benefit['desc'] }}
                    </p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- 6. Instagram — static Blade (D1), external link (R8a) --}}
    <section class="py-16 lg:py-24" aria-labelledby="instagram-heading">
        <h2 id="instagram-heading" class="mb-10 text-center font-chillax text-headline-md text-intense-cocoa">
            {{ __('storefront.home.instagram_title') }}
        </h2>
        <div class="grid grid-cols-3 gap-2 md:grid-cols-6">
            @foreach (range(1, 6) as $i)
                <a
                    href="https://instagram.com/leenhandbags"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="aspect-square overflow-hidden rounded-none bg-soft-sand transition-opacity hover:opacity-80"
                    aria-label="{{ __('storefront.home.instagram_cta') }}"
                >
                    <div class="flex h-full w-full items-center justify-center">
                        <span class="text-label-caps text-intense-cocoa/30" aria-hidden="true">@</span>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="mt-8 text-center">
            <a
                href="https://instagram.com/leenhandbags"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-block text-label-caps font-semibold uppercase tracking-wider text-intense-cocoa underline underline-offset-4 transition-colors hover:text-soft-gold"
            >
                {{ __('storefront.home.instagram_cta') }}
            </a>
        </div>
    </section>
</x-layouts::storefront>
