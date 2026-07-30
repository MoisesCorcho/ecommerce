@props([
    'title',
    'subtitle' => null,
    'tag' => 'h1',
    'size' => '3xl',
])

@php
    $sizeClass = match ($size) {
        '2xl' => 'text-2xl',
        '4xl' => 'text-3xl lg:text-4xl',
        default => 'text-3xl',
    };
@endphp

<div {{ $attributes->merge(['class' => 'mb-6']) }}>
    <{{ $tag }} class="font-[family-name:var(--font-chillax)] {{ $sizeClass }} font-semibold tracking-tight text-intense-cocoa">
        {{ $title }}
    </{{ $tag }}>

    @if ($subtitle)
        <p class="mt-2 text-sm text-intense-cocoa/70">
            {{ $subtitle }}
        </p>
    @endif

    @if (isset($slot) && $slot->isNotEmpty())
        {{ $slot }}
    @endif
</div>
