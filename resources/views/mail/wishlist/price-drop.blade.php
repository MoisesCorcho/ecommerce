<x-mail::layout>
<x-slot:header>
<x-mail::header :url="config('app.url')">
<img src="{{ $logoUrl }}" alt="{{ config('app.name', 'Leen') }}" style="height: 48px; width: auto; max-height: 48px;">
</x-mail::header>
</x-slot:header>

# {{ __('wishlist.mail.price_drop_heading') }}

{{ __('wishlist.mail.price_drop_body') }}

<x-mail::panel>
### {{ $product->name }}

@if($imageUrl)
<div style="text-align: center; margin: 16px 0;">
<img src="{{ $imageUrl }}" alt="{{ $product->name }}" style="max-width: 240px; border-radius: 8px;">
</div>
@endif

**{{ __('wishlist.mail.old_price_label') }}:** ~~{{ $oldPriceFormatted }}~~  
**{{ __('wishlist.mail.new_price_label') }}:** **{{ $newPriceFormatted }}**
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
