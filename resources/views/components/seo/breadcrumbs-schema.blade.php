@props([
    'items' => [],
])

@php
    $itemListElement = [];
    $position = 1;
    foreach ($items as $item) {
        if (! is_array($item) || empty($item['label'])) {
            continue;
        }

        $entry = [
            '@type' => 'ListItem',
            'position' => $position++,
            'name' => $item['label'],
        ];

        if (! empty($item['href'])) {
            $entry['item'] = $item['href'];
        }

        $itemListElement[] = $entry;
    }

    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $itemListElement,
    ];
@endphp

<script type="application/ld+json">
{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
</script>
