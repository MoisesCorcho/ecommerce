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
<body class="min-h-screen bg-silk-cream text-intense-cocoa font-sans antialiased">
    <div class="flex min-h-screen flex-col lg:h-screen lg:flex-row">
        <div
            class="relative hidden bg-cover bg-center lg:block lg:w-1/2"
            style="background-image: url('{{ asset('images/banners/banner-7.jpg') }}')"
            aria-hidden="true"
        >
            <div class="absolute inset-0 bg-intense-cocoa/30"></div>
        </div>

        <div class="flex flex-1 flex-col items-center justify-center px-margin-mobile py-12 lg:w-1/2 lg:overflow-y-auto lg:px-margin-desktop">
            <a href="{{ url('/') }}" class="mb-8">
                <img src="/images/logos/leen-brown.png" alt="{{ config('app.name', 'Leen Handbags') }}" class="h-16 w-auto">
            </a>

            <main class="w-full max-w-[450px]">
                {{ $slot }}
            </main>

            <a href="{{ url('/') }}" class="mt-6 text-sm font-medium text-intense-cocoa transition-colors hover:text-soft-gold">
                {{ __('auth.back_to_store') }}
            </a>
        </div>
    </div>

    @livewireScripts
</body>
</html>
