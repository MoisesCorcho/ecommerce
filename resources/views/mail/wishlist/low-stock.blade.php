<x-mail::layout>
<x-slot:header>
<x-mail::header :url="config('app.url')">
<img src="{{ $logoUrl }}" alt="{{ config('app.name', 'Leen') }}" style="height: 48px; width: auto; max-height: 48px;">
</x-mail::header>
</x-slot:header>

# {{ __('wishlist.mail.low_stock_heading') }}

{{ __('wishlist.mail.low_stock_body', ['stock' => $stockRemaining]) }}

<x-mail::panel>
### {{ $product->name }}

@if($imageUrl)
<div style="text-align: center; margin: 16px 0;">
<img src="{{ $imageUrl }}" alt="{{ $product->name }}" style="max-width: 240px; border-radius: 8px;">
</div>
@endif

<p style="color: #b91c1c; font-weight: bold;">
⚠️ {{ __('wishlist.mail.low_stock_badge', ['stock' => $stockRemaining]) }}
</p>
</x-mail::panel>

<x-mail::button :url="$productUrl" color="primary">
{{ __('wishlist.mail.low_stock_cta') }}
</x-mail::button>

<x-slot:footer>
<x-mail::footer>
© {{ date('Y') }} {{ config('app.name', 'Leen') }}. {{ __('wishlist.mail.footer_note') }}
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
