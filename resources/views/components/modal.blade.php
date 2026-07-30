@props([
    'name' => 'show',
    'title' => null,
    'titleId' => null,
    'maxWidth' => 'sm',
])

@php
    $titleId = $titleId ?? ($title ? 'modal-title-' . md5((string) $title) : null);
    $maxWidthClass = match ($maxWidth) {
        'xs' => 'max-w-xs',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        default => 'max-w-sm',
    };
@endphp

<div
    x-show="{{ $name }}"
    x-cloak
    x-transition.opacity
    x-on:keydown.escape.window="{{ $name }} = false"
    class="fixed inset-0 z-[60] flex items-center justify-center bg-intense-cocoa/50 px-4"
>
    <div
        x-show="{{ $name }}"
        x-transition
        x-on:click.outside="{{ $name }} = false"
        {{ $attributes->merge(['class' => "w-full {$maxWidthClass} bg-silk-cream p-6 shadow-xl"]) }}
        role="alertdialog"
        aria-modal="true"
        @if ($titleId) aria-labelledby="{{ $titleId }}" @endif
    >
        @if ($title)
            <h2 id="{{ $titleId }}" class="text-center font-[family-name:var(--font-chillax)] text-xl font-semibold text-intense-cocoa">
                {{ $title }}
            </h2>
        @endif

        {{ $slot }}
    </div>
</div>
