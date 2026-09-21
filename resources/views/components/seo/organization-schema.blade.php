@php
    $organizationSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => __('seo.brand_name'),
        'url' => url('/'),
        'logo' => asset('images/logos/leen-brown.png'),
        'sameAs' => array_values(array_filter([
            config('ecommerce.contact.social.instagram'),
            config('ecommerce.contact.social.tiktok'),
        ])),
        'contactPoint' => [
            '@type' => 'ContactPoint',
            'telephone' => config('ecommerce.contact.phone_raw'),
            'contactType' => 'customer service',
            'availableLanguage' => ['Spanish', 'English'],
        ],
    ];

    $websiteSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => __('seo.brand_name'),
        'url' => url('/'),
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => url('/products').'?search={search_term_string}',
            'query-input' => 'required name=search_term_string',
        ],
    ];
@endphp

<script type="application/ld+json">
{!! json_encode($organizationSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
</script>
<script type="application/ld+json">
{!! json_encode($websiteSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
</script>
