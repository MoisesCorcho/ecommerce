<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name', 'Leen Handbags') }}</title>

    <link rel="preload" href="/fonts/chillax/Chillax-Regular.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="/fonts/montserrat/Montserrat-VariableFont_wght.ttf" as="font" type="font/ttf" crossorigin>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-silk-cream text-intense-cocoa font-sans antialiased">
    <header class="sticky top-0 z-50 bg-soft-sand border-b border-intense-cocoa/10" x-data="{ open: false }">
        <div class="relative mx-auto flex max-w-storefront items-center justify-between px-margin-mobile py-5 lg:px-margin-desktop">
            {{-- Navigation links (desktop) — left side --}}
            <nav class="hidden flex-1 lg:flex items-center gap-8" aria-label="Primary">
                <a href="{{ url('/') }}" class="text-label-caps font-semibold uppercase tracking-widest text-intense-cocoa opacity-70 transition-opacity duration-300 hover:opacity-100 hover:text-soft-gold">
                    {{ __('storefront.nav.home') }}
                </a>
                <a href="{{ route('products.index') }}" class="text-label-caps font-semibold uppercase tracking-widest text-intense-cocoa opacity-70 transition-opacity duration-300 hover:opacity-100 hover:text-soft-gold">
                    {{ __('storefront.nav.shop') }}
                </a>
                <a href="{{ url('/about-us') }}" class="text-label-caps font-semibold uppercase tracking-widest text-intense-cocoa opacity-70 transition-opacity duration-300 hover:opacity-100 hover:text-soft-gold">
                    {{ __('storefront.nav.about') }}
                </a>
                <a href="{{ url('/contact') }}" class="text-label-caps font-semibold uppercase tracking-widest text-intense-cocoa opacity-70 transition-opacity duration-300 hover:opacity-100 hover:text-soft-gold">
                    {{ __('storefront.nav.contact') }}
                </a>
            </nav>

            {{-- Mobile hamburger --}}
            <button type="button" class="text-intense-cocoa lg:hidden" x-on:click="open = !open" :aria-expanded="open" aria-label="{{ __('storefront.nav.menu_toggle') }}">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>

            {{-- Brand logo (centered) --}}
            <a href="{{ url('/') }}" class="absolute left-1/2 -translate-x-1/2">
                <img src="/images/logos/leen-brown.png" alt="{{ config('app.name', 'Leen Handbags') }}" class="h-8 w-auto lg:h-10">
            </a>

            {{-- Trailing icons — right side --}}
            <div class="flex flex-1 justify-end items-center gap-4 text-intense-cocoa">
                <a href="{{ route('products.index') }}" class="transition-colors duration-300 hover:text-soft-gold" aria-label="{{ __('storefront.nav.search') }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </a>
                <a href="#" class="transition-colors duration-300 hover:text-soft-gold" aria-label="{{ __('storefront.nav.favorites') }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                    </svg>
                </a>
                <a href="{{ route('cart.page') }}" class="relative transition-colors duration-300 hover:text-soft-gold" aria-label="{{ __('storefront.nav.bag') }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.46 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z" />
                    </svg>
                </a>
            </div>
        </div>

        {{-- Mobile nav drawer --}}
        <nav class="border-t border-intense-cocoa/10 lg:hidden" x-show="open" x-cloak aria-label="Mobile">
            <div class="mx-auto flex max-w-storefront flex-col gap-3 px-margin-mobile py-4">
                <a href="{{ url('/') }}" class="text-label-caps font-semibold uppercase tracking-widest text-intense-cocoa transition-colors hover:text-soft-gold">
                    {{ __('storefront.nav.home') }}
                </a>
                <a href="{{ route('products.index') }}" class="text-label-caps font-semibold uppercase tracking-widest text-intense-cocoa transition-colors hover:text-soft-gold">
                    {{ __('storefront.nav.shop') }}
                </a>
                <a href="{{ url('/about-us') }}" class="text-label-caps font-semibold uppercase tracking-widest text-intense-cocoa transition-colors hover:text-soft-gold">
                    {{ __('storefront.nav.about') }}
                </a>
                <a href="{{ url('/contact') }}" class="text-label-caps font-semibold uppercase tracking-widest text-intense-cocoa transition-colors hover:text-soft-gold">
                    {{ __('storefront.nav.contact') }}
                </a>
            </div>
        </nav>
    </header>

    <main>
        {{ $slot }}
    </main>

    <footer class="border-t border-intense-cocoa/5 bg-soft-sand">
        <div class="mx-auto max-w-storefront px-margin-mobile py-14 lg:px-margin-desktop">
            <div class="flex flex-col items-center gap-8">
                {{-- Brand logo --}}
                <a href="{{ url('/') }}">
                    <img src="/images/logos/leen-brown.png" alt="{{ config('app.name', 'Leen Handbags') }}" class="h-10 w-auto">
                </a>

                {{-- Emotional script accent --}}
                <p class="font-labelle-aurore text-[2.25rem] leading-none text-intense-cocoa/90">Leen Handbags</p>

                {{-- Links and social icons --}}
                <div class="flex flex-col items-center justify-center gap-x-12 gap-y-6 md:flex-row">
                    <nav class="flex items-center gap-8" aria-label="Footer">
                        <a href="{{ url('/faqs') }}" class="text-[11px] font-semibold uppercase tracking-widest text-intense-cocoa/80 transition-colors hover:text-soft-gold">
                            {{ __('storefront.footer.faqs') }}
                        </a>
                        <a href="{{ url('/contact') }}" class="text-[11px] font-semibold uppercase tracking-widest text-intense-cocoa/80 transition-colors hover:text-soft-gold">
                            {{ __('storefront.footer.contact') }}
                        </a>
                    </nav>

                    <div class="flex items-center gap-6">
                        <a href="https://instagram.com" target="_blank" rel="noopener noreferrer" class="text-intense-cocoa/70 transition-colors hover:text-soft-gold" aria-label="{{ __('storefront.footer.instagram') }}">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                            </svg>
                        </a>
                        <a href="https://tiktok.com" target="_blank" rel="noopener noreferrer" class="text-intense-cocoa/70 transition-colors hover:text-soft-gold" aria-label="{{ __('storefront.footer.tiktok') }}">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z"/>
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- Copyright --}}
                <div class="flex flex-col items-center gap-1 text-[11px] font-semibold uppercase tracking-widest text-intense-cocoa/40">
                    <p>{{ __('storefront.footer.copyright', ['year' => date('Y')]) }}</p>
                </div>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
