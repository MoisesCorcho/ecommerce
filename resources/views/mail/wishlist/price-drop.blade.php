<x-mail::layout>
<x-slot:head>
<meta name="color-scheme" content="light dark">
<meta name="supported-color-schemes" content="light dark">
<style>
:root {
  color-scheme: light dark;
  supported-color-schemes: light dark;
}
@media (prefers-color-scheme: dark) {
  .leen-contrast-box {
    background-color: #FBF9F5 !important;
    border-color: #EFEAE4 !important;
  }
}
</style>
</x-slot:head>

<x-slot:header>
<x-mail::header :url="config('app.url')">
<span class="leen-contrast-box" style="display: inline-block; background-color: #FBF9F5; padding: 10px 24px; border-radius: 12px; border: 1px solid #EFEAE4;">
<img src="{{ $logoUrl }}" alt="{{ config('app.name', 'Leen') }}" style="height: 44px; width: auto; max-height: 44px; display: block; margin: 0 auto;">
</span>
</x-mail::header>
</x-slot:header>

# {{ __('wishlist.mail.price_drop_heading') }}

{{ __('wishlist.mail.price_drop_body') }}

<x-mail::panel>
<h3 style="color: #2E1F1A; margin-top: 0; font-size: 17px; font-weight: bold; text-align: center;">{{ $product->name }}</h3>

@if($imageUrl)
<div style="text-align: center; margin: 16px 0;">
<img src="{{ $imageUrl }}" alt="{{ $product->name }}" style="max-width: 240px; border-radius: 8px;">
</div>
@endif

<div style="margin-top: 14px; text-align: center; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
<span style="color: #9CA3AF; text-decoration: line-through; font-size: 15px; margin-right: 12px; display: inline-block;">{{ $oldPriceFormatted }}</span>
<span style="color: #2E1F1A; font-weight: 800; font-size: 20px; display: inline-block;">{{ $newPriceFormatted }}</span>
</div>
</x-mail::panel>

<x-mail::button :url="$productUrl" color="primary">
{{ __('wishlist.mail.price_drop_cta') }}
</x-mail::button>

<x-slot:footer>
<x-mail::footer>
© {{ date('Y') }} {{ config('app.name', 'Leen') }}. {{ __('wishlist.mail.footer_note') }}
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
