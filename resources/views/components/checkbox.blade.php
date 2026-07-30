@props([
    'id' => null,
    'error' => null,
    'wrapperClass' => '',
    'align' => 'start',
    'labelClass' => 'text-sm text-intense-cocoa/80',
])

@php
    $alignClass = $align === 'center' ? 'items-center' : 'items-start';
    $iconWrapperClass = $align === 'center' ? 'relative flex items-center justify-center' : 'relative mt-1 flex items-center justify-center';
@endphp

<label class="flex {{ $alignClass }} gap-3 {{ $wrapperClass }}">
    <span class="{{ $iconWrapperClass }}">
        <input
            type="checkbox"
            @if ($id) id="{{ $id }}" @endif
            {{ $attributes->merge(['class' => 'peer h-4 w-4 appearance-none border border-intense-cocoa bg-silk-cream checked:border-intense-cocoa checked:bg-intense-cocoa focus:outline-none']) }}
        >
        <svg class="pointer-events-none absolute h-3 w-3 text-silk-cream opacity-0 peer-checked:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
        </svg>
    </span>
    <span class="{{ $labelClass }}">
        {{ $slot }}
    </span>
</label>
@if ($error)
    @error($error)
        <p class="mt-1 text-sm text-error">{{ $message }}</p>
    @enderror
@endif
