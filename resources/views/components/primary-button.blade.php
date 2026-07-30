@props(['tag' => 'button'])

<{{ $tag }} {{ $attributes->merge(['class' => 'flex h-12 items-center justify-center cursor-pointer bg-intense-cocoa px-6 text-sm font-semibold text-silk-cream transition-colors duration-200 hover:bg-soft-gold hover:text-intense-cocoa disabled:cursor-not-allowed disabled:opacity-70']) }}>
    {{ $slot }}
</{{ $tag }}>
