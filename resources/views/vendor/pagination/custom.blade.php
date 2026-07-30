@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex justify-center items-center gap-3 bg-transparent py-4">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="flex h-10 w-10 items-center justify-center rounded-sm bg-soft-sand text-intense-cocoa/20 cursor-not-allowed">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                </svg>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="flex h-10 w-10 items-center justify-center rounded-sm bg-soft-sand text-intense-cocoa/40 transition-colors hover:text-soft-gold" aria-label="Previous">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                </svg>
            </a>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <span class="flex h-10 w-10 items-center justify-center text-intense-cocoa/40" aria-disabled="true">
                    {{ $element }}
                </span>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page" class="flex h-10 w-10 items-center justify-center border-b-2 border-intense-cocoa bg-soft-sand text-body-md font-medium text-intense-cocoa rounded-sm">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}" class="flex h-10 w-10 items-center justify-center rounded-sm bg-soft-sand text-body-md text-intense-cocoa/40 transition-colors hover:text-soft-gold" aria-label="Go to page {{ $page }}">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="flex h-10 w-10 items-center justify-center rounded-sm bg-soft-sand text-intense-cocoa/40 transition-colors hover:text-soft-gold" aria-label="Next">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                </svg>
            </a>
        @else
            <span class="flex h-10 w-10 items-center justify-center rounded-sm bg-soft-sand text-intense-cocoa/20 cursor-not-allowed">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                </svg>
            </span>
        @endif
    </nav>
@endif
