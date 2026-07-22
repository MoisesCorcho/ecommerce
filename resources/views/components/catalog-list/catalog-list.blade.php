<div x-data="{ mobileFiltersOpen: false }" class="min-h-screen">
    {{-- Mobile filter drawer overlay --}}
    <div
        x-show="mobileFiltersOpen"
        x-transition:enter="transition-opacity duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-40 bg-intense-cocoa/40 lg:hidden"
        x-cloak
        x-on:click="mobileFiltersOpen = false"
    ></div>

    {{-- Main content --}}
    <div class="mx-auto max-w-storefront px-margin-mobile py-8 lg:px-margin-desktop">
        {{-- Breadcrumb --}}
        <nav aria-label="Breadcrumb" class="mb-stack-md">
            <ol class="flex items-center gap-2 text-label-caps text-intense-cocoa/60 uppercase">
                <li><a href="{{ url('/') }}" class="hover:text-soft-gold transition-colors">{{ __('storefront.shop.breadcrumb_home') }}</a></li>
                <li><span class="text-intense-cocoa/30">/</span></li>
                <li aria-current="page" class="text-intense-cocoa">{{ __('storefront.shop.breadcrumb_shop') }}</li>
            </ol>
        </nav>

        {{-- Page header --}}
        <header class="mb-stack-lg flex flex-col items-center text-center md:items-start md:text-left">
            <h1 class="font-chillax text-display-lg-mobile text-intense-cocoa md:text-display-lg">
                {{ __('storefront.shop.title') }}
            </h1>
        </header>

        {{-- Layout: sidebar + grid --}}
        <div class="flex flex-col gap-margin-desktop lg:flex-row lg:items-start">
            {{-- Mobile filter toggle --}}
            <div class="flex items-center justify-between lg:hidden">
                <span class="text-body-md text-intense-cocoa/60">
                    {{ trans_choice('storefront.shop.results_count', $products->total(), ['count' => $products->total()]) }}
                </span>
                <button
                    type="button"
                    x-on:click="mobileFiltersOpen = true"
                    class="inline-flex items-center gap-2 rounded border border-intense-cocoa/20 px-4 py-2 text-label-caps font-semibold uppercase tracking-widest text-intense-cocoa transition-colors hover:bg-soft-sand"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                    </svg>
                    {{ __('storefront.shop.filters_button') }}
                </button>
            </div>

            {{-- Sidebar filters (desktop) --}}
            <aside
                class="hidden w-[260px] shrink-0 flex-col gap-stack-lg self-start rounded bg-soft-sand p-6 lg:sticky lg:top-32 lg:flex"
                aria-label="{{ __('storefront.shop.filters_title') }}"
            >
                @include('components.catalog-list._filters')
            </aside>

            {{-- Mobile filter drawer --}}
            <aside
                x-show="mobileFiltersOpen"
                x-transition:enter="transition-transform duration-300"
                x-transition:enter-start="translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transition-transform duration-200"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="translate-x-full"
                class="fixed inset-y-0 right-0 z-50 w-[320px] max-w-[85vw] overflow-y-auto bg-silk-cream p-6 shadow-xl lg:hidden"
                x-cloak
            >
                <div class="mb-6 flex items-center justify-between">
                    <span class="text-label-caps font-semibold uppercase tracking-widest text-intense-cocoa">
                        {{ __('storefront.shop.filters_title') }}
                    </span>
                    <button
                        type="button"
                        x-on:click="mobileFiltersOpen = false"
                        class="text-intense-cocoa transition-colors hover:text-soft-gold"
                        aria-label="{{ __('storefront.shop.close_filters') }}"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                @include('components.catalog-list._filters')
            </aside>

            {{-- Product grid area --}}
            <div class="flex-grow w-full">
                {{-- Sort + result count (desktop) --}}
                <div class="mb-10 hidden items-center justify-between lg:flex">
                    <span class="text-body-md text-intense-cocoa/60">
                        {{ trans_choice('storefront.shop.results_count', $products->total(), ['count' => $products->total()]) }}
                    </span>
                    <div class="flex items-center gap-4">
                        <span class="text-label-caps font-semibold uppercase tracking-widest text-intense-cocoa">
                            {{ __('storefront.shop.sort_label') }}
                        </span>
                        <select
                            wire:model.live="sort"
                            class="cursor-pointer border-none bg-transparent p-0 pr-8 text-body-md text-intense-cocoa focus:ring-0"
                        >
                            <option value="newest">{{ __('storefront.shop.sort_newest') }}</option>
                            <option value="price_asc">{{ __('storefront.shop.sort_price_asc') }}</option>
                            <option value="price_desc">{{ __('storefront.shop.sort_price_desc') }}</option>
                        </select>
                    </div>
                </div>

                {{-- Sort on mobile --}}
                <div class="mb-6 flex items-center justify-end lg:hidden">
                    <select
                        wire:model.live="sort"
                        class="cursor-pointer border-none bg-transparent p-0 pr-8 text-body-md text-intense-cocoa focus:ring-0"
                    >
                        <option value="newest">{{ __('storefront.shop.sort_newest') }}</option>
                        <option value="price_asc">{{ __('storefront.shop.sort_price_asc') }}</option>
                        <option value="price_desc">{{ __('storefront.shop.sort_price_desc') }}</option>
                    </select>
                </div>

                {{-- Products --}}
                @if ($products->isEmpty())
                    {{-- Empty state --}}
                    <div class="flex flex-col items-center justify-center py-24 text-center">
                        <svg class="mb-6 h-16 w-16 text-intense-cocoa/20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                        </svg>
                        <h2 class="mb-2 font-chillax text-headline-sm text-intense-cocoa">
                            {{ __('storefront.shop.empty_title') }}
                        </h2>
                        <p class="mb-8 text-body-md text-intense-cocoa/60">
                            {{ __('storefront.shop.empty_message') }}
                        </p>
                        <button
                            type="button"
                            wire:click="clearFilters"
                            class="rounded border border-intense-cocoa px-6 py-3 text-label-caps font-semibold uppercase tracking-widest text-intense-cocoa transition-colors hover:bg-soft-sand"
                        >
                            {{ __('storefront.shop.clear_filters') }}
                        </button>
                    </div>
                @else
                    <div class="grid grid-cols-1 gap-gutter sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($products as $product)
                            <livewire:product-card
                                :product="$product"
                                :currency="$currencyEnum->value"
                                wire:key="pc-{{ $product->id }}"
                            />
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-24">
                        {{ $products->links('vendor.pagination.custom') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
