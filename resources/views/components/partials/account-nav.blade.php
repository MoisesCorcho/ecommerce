{{--
    Shared sub-navigation for the /profile/* account section.

    Required:
    - $active   string   One of: 'profile', 'addresses', 'orders', 'reviews'.
--}}

<nav class="mb-8 flex flex-wrap gap-x-8 gap-y-2 border-b border-intense-cocoa/10 pb-4" aria-label="{{ __('account.nav.label') }}">
    <a
        href="{{ route('profile') }}"
        class="text-label-caps font-semibold uppercase tracking-widest transition-colors duration-200 {{ $active === 'profile' ? 'text-soft-gold' : 'text-intense-cocoa hover:text-soft-gold' }}"
    >
        {{ __('account.nav.profile') }}
    </a>
    <a
        href="{{ route('profile.addresses') }}"
        class="text-label-caps font-semibold uppercase tracking-widest transition-colors duration-200 {{ $active === 'addresses' ? 'text-soft-gold' : 'text-intense-cocoa hover:text-soft-gold' }}"
    >
        {{ __('account.nav.addresses') }}
    </a>
    <a
        href="{{ route('profile.orders') }}"
        class="text-label-caps font-semibold uppercase tracking-widest transition-colors duration-200 {{ $active === 'orders' ? 'text-soft-gold' : 'text-intense-cocoa hover:text-soft-gold' }}"
    >
        {{ __('account.nav.orders') }}
    </a>
    <a
        href="{{ route('profile.reviews') }}"
        class="text-label-caps font-semibold uppercase tracking-widest transition-colors duration-200 {{ $active === 'reviews' ? 'text-soft-gold' : 'text-intense-cocoa hover:text-soft-gold' }}"
    >
        {{ __('account.nav.reviews') }}
    </a>
</nav>
