@props(['tag' => 'button'])

<{{ $tag }} {{ $attributes->merge(['class' => 'flex h-12 items-center justify-center cursor-pointer border border-error bg-transparent px-6 text-sm font-semibold text-error transition-colors duration-200 hover:bg-error hover:text-silk-cream disabled:cursor-not-allowed disabled:opacity-70']) }}>
    {{ $slot }}
</{{ $tag }}>
