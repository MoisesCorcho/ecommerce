@props([
    'type' => 'error',
])

@php
    $typeClasses = match ($type) {
        'success' => 'border-success/20 bg-success/5 text-success',
        'info', 'status' => 'border-intense-cocoa/20 bg-intense-cocoa/5 text-intense-cocoa',
        default => 'border-error/20 bg-error/5 text-error',
    };

    $role = match ($type) {
        'info', 'status' => 'status',
        default => 'alert',
    };
@endphp

<p {{ $attributes->merge(['class' => "border px-4 py-3 text-sm {$typeClasses}"]) }} role="{{ $role }}">
    {{ $slot }}
</p>
