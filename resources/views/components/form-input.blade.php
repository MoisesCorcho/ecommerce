@props([
    'id',
    'name' => null,
    'type' => 'text',
    'label' => null,
    'placeholder' => null,
    'autocomplete' => null,
    'error' => null,
])

@php
    $name = $name ?? $id;
    $errorKey = $error ?? $name;
    $hasIcon = isset($icon) && $icon->isNotEmpty();
    $hasError = $errors->has($errorKey);
    $borderClass = $hasError ? 'border-error' : 'border-intense-cocoa/40 hover:border-intense-cocoa';
@endphp

<div>
    @if ($label)
        <label for="{{ $id }}" class="mb-1 block text-sm font-medium text-intense-cocoa">
            {{ $label }}
        </label>
    @endif

    <div class="relative">
        @if ($hasIcon)
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-intense-cocoa/60">
                {{ $icon }}
            </span>
        @endif

        <input
            id="{{ $id }}"
            name="{{ $name }}"
            type="{{ $type }}"
            @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
            @if ($placeholder) placeholder="{{ $placeholder }}" @endif
            {{ $attributes->merge(['class' => "w-full border {$borderClass} bg-silk-cream py-3 " . ($hasIcon ? 'pl-11' : 'px-3') . ' pr-3 text-body-md text-intense-cocoa transition-colors focus:border-intense-cocoa focus:outline-none disabled:cursor-not-allowed disabled:opacity-60']) }}
        >
    </div>

    @error($errorKey)
        <p id="{{ $id }}-error" data-error="{{ $errorKey }}" class="mt-1 text-sm text-error">{{ $message }}</p>
    @enderror
</div>
