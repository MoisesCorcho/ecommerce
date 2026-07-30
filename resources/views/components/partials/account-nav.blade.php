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
    <div class="flex gap-2 overflow-x-auto border-b border-intense-cocoa/10 pb-2 lg:flex-col lg:gap-1 lg:border-b-0 lg:pb-0">
        @foreach ($items as $key => $item)
            <a
                href="{{ $item['href'] }}"
                class="shrink-0 whitespace-nowrap px-4 py-3 text-label-caps font-semibold uppercase tracking-widest transition-colors duration-200 {{ $active === $key ? 'bg-soft-sand border-l-2 border-soft-gold font-semibold' : 'border-l-2 border-transparent text-intense-cocoa hover:text-soft-gold' }}"
            >
                {{ $item['label'] }}
            </a>
        @endforeach
    </div>

    <div class="mt-4 flex flex-col gap-2 border-t border-intense-cocoa/10 pt-4 lg:mt-6 lg:pt-6">
        <span class="px-4 text-[10px] font-semibold uppercase tracking-widest text-intense-cocoa/50">
            {{ __('account.nav.secondary_label') }}
        </span>
        <a
            href="{{ route('profile').'#password' }}"
            class="px-4 py-2 text-sm text-intense-cocoa transition-colors duration-200 hover:text-soft-gold"
        >
            {{ __('account.password.section_title') }}
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button
                type="submit"
                class="w-full px-4 py-2 text-left text-sm text-intense-cocoa transition-colors duration-200 hover:text-soft-gold"
            >
                {{ __('account.nav.logout') }}
            </button>
        </form>
    </div>
</nav>
