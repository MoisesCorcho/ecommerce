@props(['variant' => 'dropdown'])

@php
    use App\Enums\Commerce\CurrencyEnum;
    use App\Support\Commerce\CurrentCurrency;

    $current = CurrentCurrency::get();
@endphp

@if ($variant === 'inline')
    {{-- Mobile: the menu is already expanded, so a nested dropdown would only
         add a tap and risk overflowing the panel. Options are laid out flat. --}}
    <div class="flex items-center gap-4" role="group" aria-label="{{ __('commerce.currency.switcher_label') }}" dusk="currency-inline">
        <span class="text-label-caps uppercase tracking-widest text-intense-cocoa/60">
            {{ __('commerce.currency.heading') }}
        </span>
        @foreach (CurrencyEnum::cases() as $currency)
            <form method="POST" action="{{ route('currency.update') }}">
                @csrf
                <input type="hidden" name="currency" value="{{ $currency->value }}">
                <button
                    type="submit"
                    dusk="currency-inline-{{ $currency->value }}"
                    @if ($currency === $current) aria-current="true" @endif
                    aria-label="{{ __('commerce.currency.select', ['currency' => $currency->label()]) }}"
                    class="text-label-caps uppercase tracking-widest transition-colors duration-300 hover:text-soft-gold {{ $currency === $current ? 'font-semibold text-soft-gold' : 'text-intense-cocoa' }}"
                >
                    {{ $currency->value }}
                </button>
            </form>
        @endforeach
    </div>
@else
    <div class="relative" x-data="{ open: false }" x-on:keydown.escape.window="open = false">
        <button
            type="button"
            x-on:click="open = !open"
            :aria-expanded="open"
            aria-haspopup="true"
            aria-label="{{ __('commerce.currency.switcher_label') }}"
            dusk="currency-trigger"
            class="flex items-center gap-1 text-label-caps font-semibold uppercase tracking-widest text-intense-cocoa transition-colors duration-300 hover:text-soft-gold"
        >
            <span>{{ $current->value }}</span>
            <svg class="h-3 w-3" :class="open && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
            </svg>
        </button>

        <div
            x-show="open"
            x-on:click.outside="open = false"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-1"
            x-cloak
            dusk="currency-panel"
            class="absolute right-0 z-50 mt-3 min-w-[12rem] rounded-md border border-intense-cocoa/10 bg-soft-sand py-2 shadow-lg"
        >
            @foreach (CurrencyEnum::cases() as $currency)
                <form method="POST" action="{{ route('currency.update') }}">
                    @csrf
                    <input type="hidden" name="currency" value="{{ $currency->value }}">
                    <button
                        type="submit"
                        dusk="currency-option-{{ $currency->value }}"
                        @if ($currency === $current) aria-current="true" @endif
                        aria-label="{{ __('commerce.currency.select', ['currency' => $currency->label()]) }}"
                        class="block w-full px-4 py-2 text-left text-label-caps uppercase tracking-widest transition-colors duration-300 hover:text-soft-gold {{ $currency === $current ? 'font-semibold text-soft-gold' : 'text-intense-cocoa' }}"
                    >
                        {{ $currency->value }} <span class="normal-case tracking-normal text-intense-cocoa/60">— {{ $currency->label() }}</span>
                    </button>
                </form>
            @endforeach
        </div>
    </div>
@endif
