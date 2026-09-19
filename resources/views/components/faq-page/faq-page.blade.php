@php
    $categories = __('faq.categories');
    $categoryKeys = array_keys($categories);
    $firstCategory = $categoryKeys[0] ?? 'compras';
@endphp

<div class="py-8 lg:py-12">
    {{-- Breadcrumb (R9) --}}
    <x-breadcrumb.breadcrumb :items="[
        ['label' => __('faq.breadcrumb.home'), 'href' => route('home')],
        ['label' => __('faq.breadcrumb.faq')],
    ]"></x-breadcrumb.breadcrumb>

    <div
        class="mx-auto max-w-storefront px-margin-mobile lg:px-margin-desktop"
        x-data="{
            activeCategory: '{{ $firstCategory }}',
            openQuestion: null
        }"
    >
        {{-- Title + subtitle (R1) --}}
        <h1 class="font-[family-name:var(--font-chillax)] text-3xl font-semibold tracking-tight text-intense-cocoa lg:text-4xl">
            {{ __('faq.title') }}
        </h1>
        <p class="mt-2 text-body-md text-intense-cocoa/80 leading-relaxed lg:text-body-lg">
            {{ __('faq.subtitle') }}
        </p>

        {{-- Tabs de categorías (R3, R7) --}}
        <div class="relative mt-8 border-b border-intense-cocoa/10">
            <div
                role="tablist"
                aria-label="{{ __('faq.title') }}"
                class="flex flex-wrap gap-1"
            >
                @foreach ($categories as $key => $category)
                    <button
                        role="tab"
                        :aria-selected="activeCategory === '{{ $key }}'"
                        @click="activeCategory = '{{ $key }}'; openQuestion = null"
                        :class="activeCategory === '{{ $key }}'
                            ? 'bg-intense-cocoa text-silk-cream border-b-2 border-soft-gold -mb-px'
                            : 'text-intense-cocoa/70 hover:text-intense-cocoa hover:bg-soft-sand'"
                        class="px-5 py-2.5 text-label-caps font-semibold transition-colors duration-200"
                    >
                        {{ $category['label'] }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Acordeón de preguntas (R4, R5, R6) --}}
        <div class="mt-8">
            @foreach ($categories as $key => $category)
                <div x-show="activeCategory === '{{ $key }}'" x-cloak>
                    @if (empty($category['questions']))
                        {{-- Categoría vacía (R13) --}}
                        <p class="py-8 text-center text-intense-cocoa/50">
                            {{ __('faq.empty') }}
                        </p>
                    @else
                        <div>
                            @foreach ($category['questions'] as $i => $qa)
                                <div class="{{ $i % 2 === 0 ? 'bg-soft-sand' : '' }}">
                                    <button
                                        @click="openQuestion === {{ $i }} ? openQuestion = null : openQuestion = {{ $i }}"
                                        :aria-expanded="openQuestion === {{ $i }}"
                                        class="flex w-full items-center justify-between gap-4 px-4 py-7 text-left transition-colors hover:bg-soft-sand/50"
                                    >
                                        <span class="text-[15px] font-medium text-intense-cocoa lg:text-base">
                                            {{ $qa['q'] }}
                                        </span>
                                        <span class="flex-shrink-0 text-intense-cocoa" aria-hidden="true">
                                            <svg x-show="openQuestion !== {{ $i }}" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                            </svg>
                                            <svg x-show="openQuestion === {{ $i }}" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15" />
                                            </svg>
                                        </span>
                                    </button>
                                    <div
                                        x-show="openQuestion === {{ $i }}"
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 -translate-y-1"
                                        x-transition:enter-end="opacity-100 translate-y-0"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100 translate-y-0"
                                        x-transition:leave-end="opacity-0 -translate-y-1"
                                        class="px-4 pb-7 pr-9 text-[15px] leading-relaxed text-intense-cocoa/70 lg:text-base"
                                    >
                                        {{ $qa['a'] }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- CTA a contacto (R8) --}}
        <section class="mt-12 flex flex-col items-center gap-3 bg-soft-sand px-8 py-10 text-center lg:mt-16">
            <h2 class="font-[family-name:var(--font-chillax)] text-2xl font-semibold text-intense-cocoa">
                {{ __('faq.cta.heading') }}
            </h2>
            <p class="text-body-md text-intense-cocoa/80 leading-relaxed lg:text-body-lg">
                {{ __('faq.cta.body') }}
            </p>
            <x-primary-button
                tag="a"
                href="{{ route('contact') }}"
                class="mt-2 inline-flex px-6 py-3 text-center text-label-caps"
            >
                {{ __('faq.cta.button') }}
            </x-primary-button>
        </section>
    </div>
</div>
