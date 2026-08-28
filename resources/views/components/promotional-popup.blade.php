@php
    $popup = \App\Models\PromotionalPopup::query()->active()->with('coupon')->ordered()->first();
@endphp

@if ($popup)
    @php
        $title = $popup->getLocalizedTitle();
        $subtitle = $popup->getLocalizedSubtitle();
        $ctaText = $popup->getLocalizedCtaText();
        $hasCoupon = $popup->hasValidCoupon();
    @endphp

    @if (! empty($title))
        <div
            id="promotional-popup-{{ $popup->id }}"
            x-data="{ show: false, copied: false, id: {{ $popup->id }}, delay: {{ $popup->delay_seconds }} }"
            x-init="
                const dismissedKey = 'leen_popup_dismissed_' + id;
                const dismissedAt = localStorage.getItem(dismissedKey);
                const sevenDaysMs = 7 * 24 * 60 * 60 * 1000;
                if (!dismissedAt || (Date.now() - parseInt(dismissedAt, 10)) > sevenDaysMs) {
                    setTimeout(() => { show = true; }, delay * 1000);
                }
            "
            x-show="show"
            x-cloak
            dusk="promotional-popup"
            role="dialog"
            aria-modal="true"
            aria-labelledby="popup-title-{{ $popup->id }}"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 overflow-y-auto"
        >
            {{-- Backdrop --}}
            <div
                x-show="show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="show = false; localStorage.setItem('leen_popup_dismissed_' + id, Date.now().toString())"
                class="fixed inset-0 bg-intense-cocoa/60 backdrop-blur-sm transition-opacity"
            ></div>

            {{-- Modal Dialog --}}
            <div
                x-show="show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="relative w-full max-w-lg overflow-hidden rounded-2xl bg-soft-sand text-intense-cocoa shadow-2xl border border-intense-cocoa/10 p-6 sm:p-8 z-10 my-8"
            >
                {{-- Close Button --}}
                <button
                    type="button"
                    @click="show = false; localStorage.setItem('leen_popup_dismissed_' + id, Date.now().toString())"
                    aria-label="{{ __('promotional_popups.storefront.close') }}"
                    class="absolute right-4 top-4 rounded-full p-2 text-intense-cocoa/60 hover:text-intense-cocoa hover:bg-intense-cocoa/5 transition-colors focus:outline-none focus:ring-2 focus:ring-soft-gold"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>

                {{-- Image Banner (if available) --}}
                @if ($popup->image_path)
                    <div class="mb-5 overflow-hidden rounded-xl">
                        <img
                            src="{{ asset('storage/' . $popup->image_path) }}"
                            alt="{{ $title }}"
                            class="w-full h-48 sm:h-56 object-cover"
                        >
                    </div>
                @endif

                {{-- Content Body --}}
                <div class="text-center">
                    <h3 id="popup-title-{{ $popup->id }}" class="text-2xl sm:text-3xl font-bold tracking-tight text-intense-cocoa">
                        {{ $title }}
                    </h3>

                    @if ($subtitle)
                        <p class="mt-2 text-sm sm:text-base text-intense-cocoa/80">
                            {{ $subtitle }}
                        </p>
                    @endif

                    {{-- Coupon Section (if valid coupon attached) --}}
                    @if ($hasCoupon)
                        <div class="mt-6 rounded-xl border border-dashed border-intense-cocoa/30 bg-silk-cream/80 p-4">
                            @if ($popup->coupon->type === \App\Enums\Coupons\CouponTypeEnum::Percentage)
                                <div class="inline-block rounded-full bg-soft-gold/20 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-intense-cocoa mb-2">
                                    {{ $popup->coupon->value }}% {{ __('promotional_popups.storefront.off') }}
                                </div>
                            @endif

                            <div class="flex items-center justify-center gap-2">
                                <span class="font-mono text-xl font-bold tracking-widest text-intense-cocoa px-3 py-1 bg-white/70 rounded-lg border border-intense-cocoa/10">
                                    {{ $popup->coupon->code }}
                                </span>

                                <button
                                    type="button"
                                    dusk="copy-coupon-btn"
                                    @click="navigator.clipboard.writeText('{{ $popup->coupon->code }}'); copied = true; setTimeout(() => copied = false, 2500)"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-intense-cocoa px-4 py-2 text-xs font-semibold uppercase tracking-wider text-silk-cream transition-colors hover:bg-intense-cocoa/90 focus:outline-none focus:ring-2 focus:ring-soft-gold"
                                >
                                    <span x-show="!copied">{{ __('promotional_popups.storefront.copy_code') }}</span>
                                    <span x-show="copied" x-cloak>{{ __('promotional_popups.storefront.code_copied') }}</span>
                                </button>
                            </div>
                        </div>
                    @endif

                    {{-- CTA Button --}}
                    @if ($popup->cta_url && $ctaText)
                        <div class="mt-6">
                            <a
                                href="{{ $popup->cta_url }}"
                                class="inline-block w-full rounded-xl bg-intense-cocoa px-6 py-3.5 text-center text-sm font-semibold uppercase tracking-widest text-silk-cream shadow-md transition-colors hover:bg-intense-cocoa/90 focus:outline-none focus:ring-2 focus:ring-soft-gold"
                            >
                                {{ $ctaText }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
@endif
