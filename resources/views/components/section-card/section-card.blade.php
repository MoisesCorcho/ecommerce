@props(['tag' => 'div'])

<{{ $tag }} {{ $attributes->merge(['class' => 'bg-soft-sand p-8 shadow-ambient']) }}>
    {{ $slot }}
</{{ $tag }}>
