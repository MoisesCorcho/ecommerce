<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? config('app.name', 'Marketplace') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-stone-50 text-stone-900 antialiased">
        <header class="border-b border-stone-200 bg-white">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4">
                <a href="{{ route('products.index') }}" class="text-lg font-semibold tracking-tight">
                    {{ config('app.name', 'Marketplace') }}
                </a>
                <nav class="flex gap-4 text-sm">
                    <a href="{{ route('products.index') }}" class="text-stone-600 hover:text-stone-900">Productos</a>
                    <a href="{{ route('cart.page') }}" class="text-stone-600 hover:text-stone-900">Carrito</a>
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-4 py-8">
            <div class="mx-auto max-w-xl rounded-xl border border-stone-200 bg-white p-8 text-center" data-order-thank-you>
                <h1 class="text-3xl font-semibold tracking-tight">{{ __('orders.thank_you.title') }}</h1>
                <p class="mt-4 text-stone-700">
                    {{ __('orders.thank_you.body', ['number' => $order->order_number]) }}
                </p>
                <p class="mt-2 text-sm text-stone-600" data-order-status>
                    {{ __('orders.thank_you.status', ['status' => $order->status->label()]) }}
                </p>
                <dl class="mt-6 space-y-2 text-left text-sm">
                    <div class="flex justify-between border-b border-stone-100 py-2">
                        <dt class="text-stone-500">{{ __('orders.fields.order_number') }}</dt>
                        <dd class="font-medium" data-order-number>{{ $order->order_number }}</dd>
                    </div>
                    <div class="flex justify-between border-b border-stone-100 py-2">
                        <dt class="text-stone-500">{{ __('orders.fields.total') }}</dt>
                        <dd class="font-medium">{{ number_format($order->total) }} {{ $order->currency->value }}</dd>
                    </div>
                    <div class="flex justify-between border-b border-stone-100 py-2">
                        <dt class="text-stone-500">{{ __('orders.fields.email') }}</dt>
                        <dd>{{ $order->email }}</dd>
                    </div>
                </dl>
                <a href="{{ route('products.index') }}" class="mt-8 inline-block text-sm font-medium text-stone-900 underline">
                    Seguir comprando
                </a>
            </div>
        </main>
    </body>
</html>

