@props([
    'product',
    'currency',
    'selectedVariantId' => null,
])

@php
    $currencyEnum = $currency instanceof \App\Enums\Commerce\CurrencyEnum 
        ? $currency 
        : \App\Enums\Commerce\CurrencyEnum::from((string) $currency);

    $cleanDescription = \Illuminate\Support\Str::limit(
        \Illuminate\Support\Str::squish(strip_tags((string) $product->description)),
        250,
        '...'
    );

    if (empty($cleanDescription)) {
        $cleanDescription = __('seo.product_fallback_description', ['name' => $product->name]);
    }

    $imageUrls = $product->images->map(fn ($img) => \Illuminate\Support\Facades\Storage::disk('public')->url($img->path))->all();
    if (empty($imageUrls)) {
        $imageUrls = [asset('images/logos/leen-brown.png')];
    }

    $variant = null;
    if ($selectedVariantId) {
        $variant = $product->variants->firstWhere('id', $selectedVariantId);
    }
    if (! $variant) {
        $variant = $product->variants->first();
    }

    $priceModel = $variant?->prices->firstWhere('currency', $currencyEnum->value);
    $priceAmount = $priceModel ? ($priceModel->price / $currencyEnum->minorUnits()) : 0;

    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $product->name,
        'description' => $cleanDescription,
        'image' => $imageUrls,
        'sku' => $variant?->sku ?? $product->slug,
        'brand' => [
            '@type' => 'Brand',
            'name' => __('seo.brand_name'),
        ],
        'offers' => [
            '@type' => 'Offer',
            'priceCurrency' => $currencyEnum->value,
            'price' => (string) $priceAmount,
            'availability' => $product->is_preorder ? 'https://schema.org/PreOrder' : 'https://schema.org/InStock',
            'url' => route('products.show', $product->slug),
        ],
    ];
@endphp

<script type="application/ld+json">
{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
</script>
