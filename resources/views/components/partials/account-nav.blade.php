{{--
    Shared sub-navigation for the /profile/* account section.
    Sticky sidebar on lg and above, horizontally scrollable tabs below lg.

    Required:
    - $active   string   One of: 'profile', 'addresses', 'orders', 'reviews'.
--}}

@props([
    'active',
])

@php
    $items = [
        'profile' => ['label' => __('account.nav.profile'), 'href' => route('profile')],
        'addresses' => ['label' => __('account.nav.addresses'), 'href' => route('profile.addresses')],
        'orders' => ['label' => __('account.nav.orders'), 'href' => route('profile.orders')],
        'reviews' => ['label' => __('account.nav.reviews'), 'href' => route('profile.reviews')],
    ];
@endphp

<nav class="lg:sticky lg:top-32 lg:w-[240px] lg:shrink-0" aria-label="{{ __('account.nav.label') }}">
    {{-- Tabs: 2x2 grid on Mobile (< sm), centered row on Tablet (sm-lg), sidebar on Desktop (lg+) --}}
    <div class="grid grid-cols-2 gap-2 pb-3 sm:flex sm:flex-wrap sm:justify-center sm:gap-1 sm:pb-2 lg:flex-col lg:justify-start lg:gap-1 lg:pb-0">
        @foreach ($items as $key => $item)
            <a
                href="{{ $item['href'] }}"
                class="flex shrink-0 items-center justify-center text-center text-[11px] font-semibold transition-all duration-200 border sm:border-0 sm:border-b-2 lg:border-b-0 lg:border-l-2 px-3 py-2.5 sm:text-xs lg:justify-start lg:text-left lg:text-sm lg:hover:translate-x-1 {{ $active === $key ? 'border-soft-gold bg-soft-sand font-semibold text-intense-cocoa' : 'border-intense-cocoa text-intense-cocoa/70 hover:text-soft-gold sm:border-transparent' }}"
            >
                {{ $item['label'] }}
            </a>
        @endforeach
    </div>

    {{-- Secondary navigation --}}
    <div class="mt-3 border-t border-intense-cocoa/30 pt-3 lg:mt-4 lg:pt-4">
        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <button
                type="submit"
                class="flex w-full cursor-pointer items-center justify-center text-center text-[11px] font-semibold text-intense-cocoa/70 transition-all border sm:border-0 sm:border-b-2 lg:border-b-0 lg:border-l-2 border-intense-cocoa px-3 py-2.5 hover:text-soft-gold sm:border-transparent sm:text-xs lg:justify-start lg:text-left lg:text-sm lg:hover:translate-x-1"
            >
                {{ __('account.nav.logout') }}
            </button>
        </form>
    </div>
</nav>
