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
            x-effect="document.body.classList.toggle('overflow-hidden', show)"
            x-on:keydown.escape.window="show = false; localStorage.setItem('leen_popup_dismissed_' + id, Date.now().toString())"
            x-init="
                const dismissedKey = 'leen_popup_dismissed_' + id;
                const dismissedAt = localStorage.getItem(dismissedKey);
                const oneDayMs = 24 * 60 * 60 * 1000;
                if (!dismissedAt || (Date.now() - parseInt(dismissedAt, 10)) > oneDayMs) {
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

            {{-- Modal Dialog (100% Cuadrado / Sharp Luxury Box) --}}
            <div
                x-show="show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="relative w-full max-w-lg overflow-hidden border border-soft-sand bg-silk-cream text-intense-cocoa shadow-2xl p-6 sm:p-8 z-10 my-8"
            >
                {{-- Botón de Cierre Cuadrado (Consistente con Quick View) --}}
                <button
                    type="button"
                    @click="show = false; localStorage.setItem('leen_popup_dismissed_' + id, Date.now().toString())"
                    aria-label="{{ __('promotional_popups.storefront.close') }}"
                    class="absolute top-3 right-3 z-20 flex h-9 w-9 cursor-pointer items-center justify-center bg-intense-cocoa text-silk-cream transition-colors hover:bg-error hover:text-white focus:outline-none"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>

                {{-- Banner de Imagen Cuadrado --}}
                @if ($popup->image_path)
                    <div class="mb-5 overflow-hidden border border-intense-cocoa/10 bg-soft-sand">
                        <img
                            src="{{ asset('storage/' . $popup->image_path) }}"
                            alt="{{ $title }}"
                            class="w-full h-48 sm:h-56 object-cover"
                        >
                    </div>
                @endif

                {{-- Cuerpo del Modal --}}
                <div class="text-center">
                    <h3 id="popup-title-{{ $popup->id }}" class="font-chillax text-2xl sm:text-3xl font-semibold tracking-tight text-intense-cocoa">
                        {{ $title }}
                    </h3>

                    @if ($subtitle)
                        <p class="mt-2 text-sm sm:text-base text-intense-cocoa/80 font-sans">
                            {{ $subtitle }}
                        </p>
                    @endif

                    {{-- Bloque de Cupón Cuadrado --}}
                    @if ($hasCoupon)
                        <div class="mt-6 border border-dashed border-intense-cocoa/30 bg-soft-sand p-4">
                            @if ($popup->coupon->type === \App\Enums\Coupons\CouponTypeEnum::Percentage)
                                <div class="inline-block bg-soft-gold text-intense-cocoa px-3.5 py-1.5 text-sm font-bold tracking-wide mb-3">
                                    -{{ $popup->coupon->value }}% {{ __('promotional_popups.storefront.off') }}
                                </div>
                            @endif

                            <div class="flex items-center justify-center gap-2">
                                <span class="font-sans text-lg font-bold tracking-[0.15em] text-intense-cocoa px-4 py-2 bg-silk-cream border border-intense-cocoa/20">
                                    {{ $popup->coupon->code }}
                                </span>

                                <button
                                    type="button"
                                    dusk="copy-coupon-btn"
                                    @click="navigator.clipboard.writeText('{{ $popup->coupon->code }}'); copied = true; setTimeout(() => copied = false, 2500)"
                                    class="flex h-11 items-center justify-center cursor-pointer bg-intense-cocoa px-4 text-xs font-semibold tracking-wider text-silk-cream transition-colors duration-200 hover:bg-soft-gold hover:text-intense-cocoa focus:outline-none"
                                >
                                    <span x-show="!copied">{{ __('promotional_popups.storefront.copy_code') }}</span>
                                    <span x-show="copied" x-cloak>{{ __('promotional_popups.storefront.code_copied') }}</span>
                                </button>
                            </div>
                        </div>
                    @endif

                    {{-- Botón Principal CTA Cuadrado --}}
                    @if ($popup->cta_url && $ctaText)
                        <div class="mt-6">
                            <a
                                href="{{ $popup->cta_url }}"
                                @click="localStorage.setItem('leen_popup_dismissed_' + id, Date.now().toString())"
                                class="flex h-12 w-full items-center justify-center cursor-pointer bg-intense-cocoa px-6 text-sm font-semibold tracking-wider text-silk-cream transition-colors duration-200 hover:bg-soft-gold hover:text-intense-cocoa focus:outline-none"
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
