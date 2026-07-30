@props(['items' => []])

<nav aria-label="Breadcrumb" class="mx-auto mb-6 max-w-storefront px-margin-mobile text-sm text-intense-cocoa/60 lg:px-margin-desktop">
    <ol class="flex flex-wrap items-center gap-1.5">
        @foreach ($items as $index => $item)
            @if ($index > 0)
                <li aria-hidden="true" class="text-intense-cocoa/30">/</li>
            @endif
            <li>
                @if ($loop->last)
                    <span aria-current="page" class="font-medium text-intense-cocoa">{{ $item['label'] }}</span>
                @else
                    <a href="{{ $item['href'] }}" class="transition-colors hover:text-intense-cocoa hover:underline">{{ $item['label'] }}</a>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
