<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? config('app.name', 'Marketplace') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="min-h-screen bg-silk-cream text-intense-cocoa antialiased">
        <header class="border-b border-intense-cocoa/10 bg-soft-sand">
            <div class="mx-auto flex max-w-storefront items-center justify-between px-margin-mobile py-4">
                <a href="{{ route('products.index') }}" class="text-lg font-semibold tracking-tight">
                    {{ config('app.name', 'Marketplace') }}
                </a>
                <nav class="flex gap-4 text-sm">
                    <a href="{{ route('products.index') }}" class="text-intense-cocoa/70 hover:text-intense-cocoa">
                        {{ __('navigation.products') }}
                    </a>
                    <a href="{{ route('cart.page') }}" class="text-intense-cocoa/70 hover:text-intense-cocoa" data-nav-cart>
                        {{ __('navigation.cart') }}
                    </a>
                    <a href="{{ route('checkout.show') }}" class="text-intense-cocoa/70 hover:text-intense-cocoa" data-nav-checkout>
                        {{ __('navigation.checkout') }}
                    </a>
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-storefront px-margin-mobile py-section-gap">
            {{ $slot }}
        </main>

        @livewireScripts
    </body>
</html>
