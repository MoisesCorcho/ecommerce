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
    <a
        href="{{ $ctaHref }}"
        class="mt-2 inline-flex h-11 items-center border border-intense-cocoa px-6 text-sm font-semibold text-intense-cocoa transition-colors duration-200 hover:bg-intense-cocoa hover:text-silk-cream"
    >
        {{ $ctaLabel }}
    </a>
</div>
