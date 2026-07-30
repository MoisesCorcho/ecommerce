<div x-data="{ mobileFiltersOpen: false }" class="min-h-screen">
<x-partials.toast>
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
    <div class="pt-8 pb-section-gap lg:pt-12">
        {{-- Breadcrumb --}}
        <x-breadcrumb.breadcrumb :items="[
            ['label' => __('storefront.shop.breadcrumb_home'), 'href' => url('/')],
            ['label' => __('storefront.shop.breadcrumb_shop')],
        ]" />

        <div class="mx-auto max-w-storefront px-margin-mobile lg:px-margin-desktop">
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
                class="hidden w-[240px] shrink-0 flex-col gap-stack-lg self-start rounded-sm bg-soft-sand p-6 lg:sticky lg:top-32 lg:flex"
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
                        <span class="text-[11px] font-semibold uppercase tracking-widest text-intense-cocoa">
                            {{ __('storefront.shop.sort_label') }}
                        </span>
                        <div class="relative">
                            <select
                                wire:model.live="sort"
                                class="peer cursor-pointer appearance-none border border-intense-cocoa bg-transparent px-3 py-2 pr-10 text-body-md text-intense-cocoa transition-colors duration-200 hover:bg-intense-cocoa hover:text-silk-cream focus:outline-none focus:bg-intense-cocoa focus:text-silk-cream"
                            >
                                <option value="newest" class="bg-silk-cream text-intense-cocoa hover:bg-soft-gold hover:text-intense-cocoa">{{ __('storefront.shop.sort_newest') }}</option>
                                <option value="price_asc" class="bg-silk-cream text-intense-cocoa hover:bg-soft-gold hover:text-intense-cocoa">{{ __('storefront.shop.sort_price_asc') }}</option>
                                <option value="price_desc" class="bg-silk-cream text-intense-cocoa hover:bg-soft-gold hover:text-intense-cocoa">{{ __('storefront.shop.sort_price_desc') }}</option>
                            </select>
                            <svg class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-intense-cocoa transition-all duration-200 peer-hover:text-silk-cream peer-focus:text-silk-cream peer-focus:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Sort on mobile --}}
                <div class="mb-6 flex items-center justify-end lg:hidden">
                    <div class="relative">
                        <select
                            wire:model.live="sort"
                                class="peer cursor-pointer appearance-none border border-intense-cocoa bg-transparent px-3 py-2 pr-10 text-body-md text-intense-cocoa transition-colors duration-200 hover:bg-intense-cocoa hover:text-silk-cream focus:outline-none focus:bg-intense-cocoa focus:text-silk-cream"
                        >
                            <option value="newest" class="bg-silk-cream text-intense-cocoa hover:bg-soft-gold hover:text-intense-cocoa">{{ __('storefront.shop.sort_newest') }}</option>
                            <option value="price_asc" class="bg-silk-cream text-intense-cocoa hover:bg-soft-gold hover:text-intense-cocoa">{{ __('storefront.shop.sort_price_asc') }}</option>
                            <option value="price_desc" class="bg-silk-cream text-intense-cocoa hover:bg-soft-gold hover:text-intense-cocoa">{{ __('storefront.shop.sort_price_desc') }}</option>
                        </select>
                        <svg class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-intense-cocoa transition-all duration-200 peer-focus:text-silk-cream peer-focus:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </div>
                </div>

                {{-- Products --}}
                @if ($products->isEmpty())
                    {{-- Empty state --}}
                    <div class="flex flex-col items-center justify-center py-24 text-center">
                        <svg class="mb-6 h-20 w-20 text-intense-cocoa/10" fill="none" viewBox="0 0 64 64" stroke="currentColor" stroke-width="1">
                            <path d="M16 24h32l-4 28H20L16 24z" />
                            <path d="M22 24V16a10 10 0 0 1 20 0v8" />
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
                            class="border border-intense-cocoa px-6 py-3 text-label-caps font-semibold text-intense-cocoa transition-colors hover:bg-intense-cocoa hover:text-silk-cream"
                        >
                            {{ __('storefront.shop.clear_filters') }}
                        </button>
                    </div>
                @else
                    <div class="grid grid-cols-1 gap-x-gutter gap-y-16 sm:grid-cols-2 lg:grid-cols-3">
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
</x-partials.toast>
</div>
