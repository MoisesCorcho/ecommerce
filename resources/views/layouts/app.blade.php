<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? config('app.name', 'Marketplace') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="min-h-screen bg-stone-50 text-stone-900 antialiased">
        <header class="border-b border-stone-200 bg-white">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4">
                <a href="{{ route('products.index') }}" class="text-lg font-semibold tracking-tight">
                    {{ config('app.name', 'Marketplace') }}
                </a>
                <nav class="flex gap-4 text-sm">
                    <a href="{{ route('products.index') }}" class="text-stone-600 hover:text-stone-900">
                        Productos
                    </a>
                    <a href="{{ route('cart.page') }}" class="text-stone-600 hover:text-stone-900" data-nav-cart>
                        Carrito
                    </a>
                    <a href="{{ route('checkout.show') }}" class="text-stone-600 hover:text-stone-900" data-nav-checkout>
                        Checkout
                    </a>
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-4 py-8">
            {{ $slot }}
        </main>

        @livewireScripts
    </body>
</html>
