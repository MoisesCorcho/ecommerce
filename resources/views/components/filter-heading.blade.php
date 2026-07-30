@props(['tag' => 'h3'])

<{{ $tag }} {{ $attributes->merge(['class' => 'border-b border-intense-cocoa/10 pb-2 mb-2 text-[11px] font-semibold uppercase tracking-[0.2em] text-intense-cocoa']) }}>
    {{ $slot }}
</{{ $tag }}>
