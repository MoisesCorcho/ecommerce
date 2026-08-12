@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('pagination.aria_label') ?? 'Paginación' }}" class="flex items-center justify-center gap-1.5 py-4">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="flex h-9 w-9 items-center justify-center border border-intense-cocoa/20 text-xs text-intense-cocoa/30 cursor-not-allowed" aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4" aria-hidden="true">
                    <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 0 1-.02 1.06L8.832 10l3.938 3.71a.75.75 0 1 1-1.04 1.08l-4.5-4.25a.75.75 0 0 1 0-1.08l4.5-4.25a.75.75 0 0 1 1.06.02Z" clip-rule="evenodd" />
                </svg>
            </span>
        @else
            <button
                type="button"
                wire:click="previousPage"
                wire:loading.attr="disabled"
                class="flex h-9 w-9 items-center justify-center border border-intense-cocoa bg-transparent text-xs font-semibold uppercase tracking-widest text-intense-cocoa transition-all duration-200 hover:border-soft-gold hover:text-soft-gold focus:outline-none"
                aria-label="{{ __('pagination.previous') }}"
            >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4" aria-hidden="true">
                    <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 0 1-.02 1.06L8.832 10l3.938 3.71a.75.75 0 1 1-1.04 1.08l-4.5-4.25a.75.75 0 0 1 0-1.08l4.5-4.25a.75.75 0 0 1 1.06.02Z" clip-rule="evenodd" />
                </svg>
            </button>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <span class="flex h-9 w-9 items-center justify-center text-xs font-semibold text-intense-cocoa/40" aria-disabled="true">
                    {{ $element }}
                </span>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page" class="flex h-9 w-9 items-center justify-center border border-intense-cocoa bg-intense-cocoa text-xs font-semibold uppercase tracking-widest text-silk-cream">
                            {{ $page }}
                        </span>
                    @else
                        <button
                            type="button"
                            wire:click="gotoPage({{ $page }})"
                            wire:loading.attr="disabled"
                            class="flex h-9 w-9 items-center justify-center border border-intense-cocoa bg-transparent text-xs font-semibold uppercase tracking-widest text-intense-cocoa transition-all duration-200 hover:border-soft-gold hover:text-soft-gold focus:outline-none"
                            aria-label="Página {{ $page }}"
                        >
                            {{ $page }}
                        </button>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <button
                type="button"
                wire:click="nextPage"
                wire:loading.attr="disabled"
                class="flex h-9 w-9 items-center justify-center border border-intense-cocoa bg-transparent text-xs font-semibold uppercase tracking-widest text-intense-cocoa transition-all duration-200 hover:border-soft-gold hover:text-soft-gold focus:outline-none"
                aria-label="{{ __('pagination.next') }}"
            >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4" aria-hidden="true">
                    <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" />
                </svg>
            </button>
        @else
            <span class="flex h-9 w-9 items-center justify-center border border-intense-cocoa/20 text-xs text-intense-cocoa/30 cursor-not-allowed" aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4" aria-hidden="true">
                    <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" />
                </svg>
            </span>
        @endif
    </nav>
@endif
