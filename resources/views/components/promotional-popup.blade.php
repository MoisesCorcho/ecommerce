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
            {{-- Backdrop con tinte de cacao y desenfoque editorial --}}
            <div
                x-show="show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="show = false; localStorage.setItem('leen_popup_dismissed_' + id, Date.now().toString())"
                class="fixed inset-0 bg-intense-cocoa/70 backdrop-blur-md transition-opacity"
            ></div>

            {{-- Modal Box: Doble Marco de Lujo (Passepartout Effect / 100% Ortogonal) --}}
            <div
                x-show="show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="relative w-full {{ $popup->image_path ? 'max-w-lg laptop-horizontal:max-w-2xl lg:laptop-horizontal:max-w-3xl' : 'max-w-lg' }} border border-intense-cocoa/20 bg-silk-cream text-intense-cocoa shadow-2xl p-2.5 sm:p-3.5 z-10 my-auto max-h-[calc(100vh-2rem)] overflow-y-auto no-scrollbar"
            >
                {{-- Botón de Cierre: Lengüeta Editorial Integrada al Marco Exterior (Idle Nudge + Hover Spin) --}}
                <button
                    type="button"
                    @click="show = false; localStorage.setItem('leen_popup_dismissed_' + id, Date.now().toString())"
                    aria-label="{{ __('promotional_popups.storefront.close') }}"
                    class="group absolute -top-px -right-px z-30 flex h-9 w-9 sm:h-10 sm:w-10 cursor-pointer items-center justify-center border-b border-l border-intense-cocoa/35 bg-silk-cream text-intense-cocoa transition-colors duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-intense-cocoa focus-visible:ring-offset-2 focus-visible:ring-offset-silk-cream"
                >
                    <svg
                        class="h-5 w-5 animate-close-idle transition-transform duration-300 group-hover:![animation:none] group-hover:rotate-90"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                        aria-hidden="true"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>

                {{-- Marco Interior (Estuche de Atelier) --}}
                <div class="relative border border-intense-cocoa/35 bg-silk-cream overflow-hidden">
                    <div class="{{ $popup->image_path ? 'flex flex-col laptop-horizontal:flex-row items-stretch' : 'p-6 sm:p-8 text-center' }}">
                        {{-- Banner de Imagen Editorial con Filigrana (Columna Izquierda en Laptop / Superior en Monitor Grande y Mobile) --}}
                        @if ($popup->image_path)
                            <div class="w-full laptop-horizontal:w-5/12 h-48 sm:h-56 laptop-horizontal:h-auto min-h-[180px] laptop-horizontal:min-h-[300px] overflow-hidden border-b laptop-horizontal:border-b-0 laptop-horizontal:border-r border-intense-cocoa/20 bg-soft-sand relative shrink-0">
                                <img
                                    src="{{ asset('storage/' . $popup->image_path) }}"
                                    alt="{{ $title }}"
                                    class="w-full h-full object-cover"
                                >
                            </div>
                        @endif

                        {{-- Bloque de Contenido y Acciones (Columna Derecha en Laptop / Inferior en Monitor Grande y Mobile) --}}
                        <div class="{{ $popup->image_path ? 'w-full laptop-horizontal:w-7/12 p-6 sm:p-8 laptop-horizontal:p-6 lg:laptop-horizontal:p-7 flex flex-col justify-center text-center' : '' }}">
                            <span class="font-labelle-aurore text-2xl sm:text-3xl text-soft-gold block leading-none mb-1.5 select-none px-4 sm:px-0">
                                {{ __('promotional_popups.storefront.eyebrow') }}
                            </span>

                            <h3 id="popup-title-{{ $popup->id }}" class="font-chillax text-2xl sm:text-3xl laptop-horizontal:text-xl lg:laptop-horizontal:text-2xl font-semibold tracking-tight text-intense-cocoa uppercase">
                                {{ $title }}
                            </h3>

                            @if ($subtitle)
                                <p class="mt-2.5 text-sm sm:text-base text-intense-cocoa/75 font-sans leading-relaxed max-w-sm laptop-horizontal:max-w-md mx-auto">
                                    {{ $subtitle }}
                                </p>
                            @endif

                            {{-- Bloque de Cupón: Voucher de Atelier --}}
                            @if ($hasCoupon)
                                <div class="mt-6 laptop-horizontal:mt-4 border border-dashed border-intense-cocoa/30 bg-soft-sand/70 p-4 relative text-center">
                                    @if ($popup->coupon->type === \App\Enums\Coupons\CouponTypeEnum::Percentage)
                                        <div class="w-fit mx-auto bg-soft-gold text-intense-cocoa px-3.5 py-1 text-xs font-bold tracking-widest uppercase mb-3 laptop-horizontal:mb-2 border border-soft-gold/30">
                                            -{{ $popup->coupon->value }}% {{ __('promotional_popups.storefront.off') }}
                                        </div>
                                    @endif

                                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-center gap-2">
                                        <span class="font-sans text-base font-bold tracking-[0.2em] text-intense-cocoa px-4 py-2 bg-silk-cream border border-intense-cocoa/20 text-center select-all">
                                            {{ $popup->coupon->code }}
                                        </span>

                                        <button
                                            type="button"
                                            dusk="copy-coupon-btn"
                                            @click="navigator.clipboard.writeText('{{ $popup->coupon->code }}'); copied = true; setTimeout(() => copied = false, 2500)"
                                            class="group flex h-11 laptop-horizontal:h-10 items-center justify-center gap-2 cursor-pointer border border-intense-cocoa bg-transparent px-4 text-xs font-semibold tracking-wider text-intense-cocoa transition-colors duration-200 hover:bg-intense-cocoa hover:text-silk-cream focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-intense-cocoa focus-visible:ring-offset-2 focus-visible:ring-offset-soft-sand"
                                        >
                                            <span x-show="!copied" class="inline-flex items-center gap-1.5">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                </svg>
                                                {{ __('promotional_popups.storefront.copy_code') }}
                                            </span>
                                            <span x-show="copied" x-cloak class="inline-flex items-center gap-1.5 font-bold">
                                                <svg class="h-3.5 w-3.5 text-soft-gold group-hover:text-silk-cream transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                </svg>
                                                {{ __('promotional_popups.storefront.code_copied') }}
                                            </span>
                                        </button>

                                        {{-- Región accesible para lectores de pantalla --}}
                                        <span class="sr-only" role="status" aria-live="polite" x-text="copied ? '{{ __('promotional_popups.storefront.code_copied') }}' : ''"></span>
                                    </div>
                                </div>
                            @endif

                            {{-- Botón Principal CTA Cuadrado --}}
                            @if ($popup->cta_url && $ctaText)
                                <div class="mt-6 laptop-horizontal:mt-4 text-center">
                                    <a
                                        href="{{ $popup->cta_url }}"
                                        @click="localStorage.setItem('leen_popup_dismissed_' + id, Date.now().toString())"
                                        class="flex h-12 laptop-horizontal:h-11 w-full items-center justify-center text-center cursor-pointer bg-intense-cocoa px-6 text-sm font-semibold tracking-wider text-silk-cream transition-colors duration-200 hover:bg-soft-gold hover:text-intense-cocoa focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-intense-cocoa focus-visible:ring-offset-2 focus-visible:ring-offset-silk-cream"
                                    >
                                        <span class="w-full text-center">{{ $ctaText }}</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endif
