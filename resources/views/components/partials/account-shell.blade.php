{{--
    Shared shell for the /profile/* account section: breadcrumb + responsive nav + content region.

    Required:
    - $active        string  One of: 'profile', 'addresses', 'orders', 'reviews'.
    Optional:
    - $orderNumber   string  When set, adds a 4th breadcrumb segment (order detail page).
--}}

@props([
    'active',
    'orderNumber' => null,
])

@php
    $sectionLabels = [
        'addresses' => __('account.nav.addresses'),
        'orders' => __('account.nav.orders'),
        'reviews' => __('account.nav.reviews'),
    ];

    $sectionRoutes = [
        'addresses' => route('profile.addresses'),
        'orders' => route('profile.orders'),
        'reviews' => route('profile.reviews'),
    ];

    $hasSection = $active !== 'profile';
    $hasOrderNumber = $orderNumber !== null;
@endphp

<div class="py-8 lg:py-12">
    <div class="mx-auto max-w-storefront px-margin-mobile lg:px-margin-desktop">
        <nav aria-label="Breadcrumb" class="mb-8">
            <ol class="flex flex-wrap items-center gap-2 text-[10px] font-semibold text-intense-cocoa/60">
                <li><a href="{{ url('/') }}" class="transition-colors hover:text-soft-gold">{{ __('account.breadcrumb.home') }}</a></li>
                <li><span class="text-intense-cocoa/30">/</span></li>
                @if ($hasSection)
                    <li><a href="{{ route('profile') }}" class="transition-colors hover:text-soft-gold">{{ __('account.breadcrumb.account') }}</a></li>
                    <li><span class="text-intense-cocoa/30">/</span></li>
                    @if ($hasOrderNumber)
                        <li><a href="{{ $sectionRoutes[$active] }}" class="transition-colors hover:text-soft-gold">{{ $sectionLabels[$active] }}</a></li>
                        <li><span class="text-intense-cocoa/30">/</span></li>
                        <li aria-current="page" class="text-intense-cocoa">{{ $orderNumber }}</li>
                    @else
                        <li aria-current="page" class="text-intense-cocoa">{{ $sectionLabels[$active] }}</li>
                    @endif
                @else
                    <li aria-current="page" class="text-intense-cocoa">{{ __('account.breadcrumb.account') }}</li>
                @endif
            </ol>
        </nav>

        <div class="flex flex-col gap-margin-desktop lg:flex-row lg:items-start">
            <x-partials.account-nav :active="$active" />

            <div class="w-full flex-grow">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
