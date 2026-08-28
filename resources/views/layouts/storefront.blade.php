<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Leen</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="apple-touch-icon" href="/favicon.png">

    <link rel="preload" href="/fonts/chillax/Chillax-Regular.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="/fonts/montserrat/Montserrat-VariableFont_wght.ttf" as="font" type="font/ttf" crossorigin>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="flex min-h-screen flex-col bg-silk-cream text-intense-cocoa font-sans antialiased">
    <x-announcement-bar />

    <header class="sticky top-0 z-50 bg-soft-sand border-b border-intense-cocoa/10" x-data="{ open: false }">

        <div class="relative mx-auto flex max-w-storefront items-center justify-between px-margin-mobile py-5 lg:px-margin-desktop">
            {{-- Navigation links (desktop) — left side --}}
            <nav class="hidden flex-1 lg:flex items-center gap-8" aria-label="Primary">
                <a href="{{ url('/') }}" class="text-xs font-semibold uppercase tracking-widest text-intense-cocoa transition-colors duration-300 hover:text-soft-gold">
                    {{ __('storefront.nav.home') }}
                </a>
                <a href="{{ route('products.index') }}" class="text-xs font-semibold uppercase tracking-widest text-intense-cocoa transition-colors duration-300 hover:text-soft-gold">
                    {{ __('storefront.nav.shop') }}
                </a>
                <a href="{{ url('/about-us') }}" class="text-xs font-semibold uppercase tracking-widest text-intense-cocoa transition-colors duration-300 hover:text-soft-gold">
                    {{ __('storefront.nav.about') }}
                </a>
                <a href="{{ route('contact') }}" class="text-xs font-semibold uppercase tracking-widest text-intense-cocoa transition-colors duration-300 hover:text-soft-gold">
                    {{ __('storefront.nav.contact') }}
                </a>
            </nav>

            {{-- Mobile hamburger --}}
            <button type="button" class="text-intense-cocoa lg:hidden" x-on:click="open = !open" :aria-expanded="open" aria-label="{{ __('storefront.nav.menu_toggle') }}" dusk="mobile-menu-toggle">
                <svg class="h-6 w-6 sm:h-7 sm:w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>

            {{-- Brand logo (centered) --}}
            <a href="{{ url('/') }}" class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2">
                <img src="/images/logos/leen-brown.png" alt="{{ config('app.name', 'Leen') }}" class="h-[2.625rem] w-auto sm:h-11 lg:h-12">
            </a>

            {{-- Trailing icons — right side --}}
            <div class="flex flex-1 justify-end items-center gap-3 sm:gap-5 text-intense-cocoa">
                <div class="hidden lg:block">
                    <x-currency-switcher />
                </div>
                <div class="hidden lg:block">
                    <x-locale-switcher />
                </div>
                <a href="{{ route('wishlist') }}" class="transition-colors duration-300 hover:text-soft-gold" aria-label="{{ __('storefront.nav.favorites') }}">
                    <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                    </svg>
                </a>
                @guest
                    <a href="{{ route('login') }}" class="transition-colors duration-300 hover:text-soft-gold" aria-label="{{ __('storefront.nav.login') }}">
                        <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                    </a>
                @else
                    <a href="{{ Route::has('profile') ? route('profile') : url('/') }}" class="transition-colors duration-300 hover:text-soft-gold" aria-label="{{ __('storefront.nav.account') }}">
                        <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                    </a>
                @endguest
                <a href="{{ route('cart.page') }}" class="relative transition-colors duration-300 hover:text-soft-gold" aria-label="{{ __('storefront.nav.bag') }}">
                    <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.46 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z" />
                    </svg>
                </a>
            </div>
        </div>

        {{-- Floating mobile navigation dropdown --}}
        <nav
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            class="absolute top-full left-0 right-0 z-50 border-b border-intense-cocoa/10 bg-soft-sand shadow-lg lg:hidden"
            x-cloak
            aria-label="Mobile"
        >
            <div class="mx-auto flex max-w-storefront flex-col gap-4 px-margin-mobile py-5">
                <a href="{{ url('/') }}" x-on:click="open = false" class="text-label-caps font-semibold uppercase tracking-widest text-intense-cocoa transition-colors hover:text-soft-gold">
                    {{ __('storefront.nav.home') }}
                </a>
                <a href="{{ route('products.index') }}" x-on:click="open = false" class="text-label-caps font-semibold uppercase tracking-widest text-intense-cocoa transition-colors hover:text-soft-gold">
                    {{ __('storefront.nav.shop') }}
                </a>
                <a href="{{ url('/about-us') }}" x-on:click="open = false" class="text-label-caps font-semibold uppercase tracking-widest text-intense-cocoa transition-colors hover:text-soft-gold">
                    {{ __('storefront.nav.about') }}
                </a>
                <a href="{{ route('contact') }}" x-on:click="open = false" class="text-label-caps font-semibold uppercase tracking-widest text-intense-cocoa transition-colors hover:text-soft-gold">
                    {{ __('storefront.nav.contact') }}
                </a>
                @guest
                    <a href="{{ route('login') }}" x-on:click="open = false" class="text-label-caps font-semibold uppercase tracking-widest text-intense-cocoa transition-colors hover:text-soft-gold">
                        {{ __('storefront.nav.login') }}
                    </a>
                @else
                    <a href="{{ Route::has('profile') ? route('profile') : url('/') }}" x-on:click="open = false" class="text-label-caps font-semibold uppercase tracking-widest text-intense-cocoa transition-colors hover:text-soft-gold">
                        {{ __('storefront.nav.account') }}
                    </a>
                @endguest
                <div class="flex flex-col gap-4 border-t border-intense-cocoa/10 pt-4">
                    <x-locale-switcher variant="inline" />
                    <x-currency-switcher variant="inline" />
                </div>
            </div>
        </nav>
    </header>

    <main class="flex-1">
        {{ $slot }}
    </main>

    <footer class="border-t border-intense-cocoa/5 bg-soft-sand">
        <div class="mx-auto max-w-storefront px-margin-mobile py-10 lg:px-margin-desktop">
            <div class="flex flex-col items-center gap-6">
                {{-- Brand logo --}}
                <a href="{{ url('/') }}">
                    <img src="/images/logos/leen-brown.png" alt="{{ config('app.name', 'Leen') }}" class="h-20 w-auto">
                </a>

                {{-- Emotional script accent --}}
                <p class="font-labelle-aurore text-[2rem] leading-none text-intense-cocoa/80">Sweeter than honey</p>

                {{-- Links and social icons --}}
                <div class="flex flex-col items-center justify-center gap-x-8 gap-y-4 md:flex-row">
                    <nav class="flex items-center gap-8" aria-label="Footer">
                        <a href="{{ url('/faq') }}" class="text-[11px] font-semibold uppercase tracking-widest text-intense-cocoa/80 transition-colors hover:text-soft-gold">
                            {{ __('storefront.footer.faqs') }}
                        </a>
                        <a href="{{ route('contact') }}" class="text-[11px] font-semibold uppercase tracking-widest text-intense-cocoa/80 transition-colors hover:text-soft-gold">
                            {{ __('storefront.footer.contact') }}
                        </a>
                    </nav>

                    <div class="flex items-center gap-4">
                        <a href="{{ config('ecommerce.contact.social.instagram') }}" target="_blank" rel="noopener noreferrer" class="text-intense-cocoa/70 transition-colors hover:text-soft-gold" aria-label="{{ __('storefront.footer.instagram') }}">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                            </svg>
                        </a>
                        <a href="{{ config('ecommerce.contact.social.tiktok') }}" target="_blank" rel="noopener noreferrer" class="text-intense-cocoa/70 transition-colors hover:text-soft-gold" aria-label="{{ __('storefront.footer.tiktok') }}">
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

    <livewire:product-quick-view />
    <x-promotional-popup />

    @livewireScripts
</body>
</html>
