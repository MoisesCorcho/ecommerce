<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Leen</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="apple-touch-icon" href="/favicon.png">

    {{-- Google Fonts preconnect & Montserrat (non-blocking) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" media="print" onload="this.media='all'">
    <noscript>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap">
    </noscript>

    {{-- Critical brand font preload --}}
    <link rel="preload" href="/fonts/chillax/Chillax-Regular.woff2" as="font" type="font/woff2" crossorigin>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-silk-cream text-intense-cocoa font-sans antialiased">
    <div class="flex min-h-screen flex-col lg:h-screen lg:flex-row">
        <div
            class="relative hidden bg-cover bg-center lg:block lg:w-1/2"
            style="background-image: url('{{ asset('images/banners/banner-7.webp') }}')"
            aria-hidden="true"
        >
            <div class="absolute inset-0 bg-intense-cocoa/30"></div>
        </div>

        {{-- Vertical breathing room scales with the viewport instead of being a
             fixed 7.5rem: on a short laptop that constant spent a third of the
             column height on padding and pushed the form into a scroll. --}}
        <div class="flex flex-1 flex-col items-center justify-center px-margin-mobile py-12 lg:w-1/2 lg:items-center lg:justify-start lg:overflow-y-auto lg:px-margin-desktop lg:py-[8vh]">
            <a href="{{ url('/') }}" class="mb-8">
                <img src="/images/logos/leen-brown.webp" alt="{{ config('app.name', 'Leen') }}" class="h-16 w-auto">
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
