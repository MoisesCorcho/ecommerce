@props(['paginator'])

@if ($paginator && method_exists($paginator, 'hasPages') && $paginator->hasPages())
    <div {{ $attributes->merge(['class' => 'mt-12 flex justify-center']) }}>
        {{ $paginator->links('vendor.pagination.custom') }}
    </div>
@endif
