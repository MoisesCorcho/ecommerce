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
    <div class="grid grid-cols-2 gap-2 pb-3 sm:flex sm:gap-1 sm:justify-center sm:pb-2 lg:flex-col lg:gap-1 lg:justify-start lg:pb-0">
        @foreach ($items as $key => $item)
            <a
                href="{{ $item['href'] }}"
                class="flex items-center justify-center lg:justify-start text-center lg:text-left shrink-0 px-3 py-2.5 text-[11px] sm:text-xs lg:text-sm font-semibold uppercase tracking-wider transition-all duration-200 lg:hover:translate-x-1 border sm:border-0 sm:border-b-2 lg:border-b-0 lg:border-l-2 {{ $active === $key ? 'bg-soft-sand border-soft-gold text-intense-cocoa font-semibold' : 'border-intense-cocoa sm:border-transparent text-intense-cocoa/70 hover:text-soft-gold' }}"
            >
                {{ $item['label'] }}
            </a>
        @endforeach
    </div>

    {{-- Secondary navigation --}}
    <div class="mt-3 flex items-center justify-between border-t border-intense-cocoa/10 pt-3 lg:mt-6 lg:flex-col lg:items-start lg:gap-2 lg:border-t lg:pt-6">
        <span class="px-2 text-[10px] font-semibold uppercase tracking-widest text-intense-cocoa/50 lg:px-4">
            {{ __('account.nav.secondary_label') }}
        </span>
        <form method="POST" action="{{ route('logout') }}" class="inline-block">
            @csrf
            <button
                type="submit"
                class="cursor-pointer px-2 py-1 text-sm text-intense-cocoa transition-colors duration-200 hover:text-soft-gold lg:px-4 lg:py-2"
            >
                {{ __('account.nav.logout') }}
            </button>
        </form>
    </div>
</nav>
