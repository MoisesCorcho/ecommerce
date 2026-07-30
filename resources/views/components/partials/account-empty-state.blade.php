{{--
    Shared empty-state block for the /profile/* account section (orders, reviews).
    Structure and classes copied verbatim from cart-page.blade.php's empty state.

    Required:
    - $title      string
    - $message    string
    - $ctaLabel   string
    - $ctaHref    string
    Slot:
    - default     the icon markup (inline SVG, aria-hidden="true")
--}}

@props([
    'title',
    'message',
    'ctaLabel',
    'ctaHref',
])

<div class="flex flex-col items-center justify-center gap-4 py-20 text-center">
    {{ $slot }}
    <h2 class="font-[family-name:var(--font-chillax)] text-2xl font-semibold text-intense-cocoa">
        {{ $title }}
    </h2>
    <p class="max-w-sm text-intense-cocoa/70">
        {{ $message }}
    </p>
    <x-secondary-button
        tag="a"
        href="{{ $ctaHref }}"
        class="mt-2 h-11 px-6"
    >
        {{ $ctaLabel }}
    </x-secondary-button>
</div>
