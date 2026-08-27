@php
    $announcement = \App\Models\Announcement::query()->active()->ordered()->first();
@endphp

@if ($announcement)
    @php
        $text = $announcement->getLocalizedText();
        $isExternal = $announcement->url && (str_starts_with($announcement->url, 'http://') || str_starts_with($announcement->url, 'https://'));
    @endphp

    @if (! empty($text))
        <aside
            id="announcement-bar-{{ $announcement->id }}"
            x-data="{ dismissed: false, id: {{ $announcement->id }} }"
            x-init="dismissed = localStorage.getItem('leen_announcement_dismissed_' + id) === '1'"
            x-show="!dismissed"
            dusk="announcement-bar"
            aria-label="{{ __('announcements.model.label') }}"
            class="relative z-50 bg-intense-cocoa text-silk-cream text-xs py-2 px-4 transition-all duration-300"
        >
            <script>
                if (localStorage.getItem('leen_announcement_dismissed_{{ $announcement->id }}') === '1') {
                    document.getElementById('announcement-bar-{{ $announcement->id }}').style.display = 'none';
                }
            </script>
            <div class="mx-auto flex max-w-storefront items-center justify-center px-8 sm:px-12">
                <div class="text-center font-medium tracking-wide">
                    @if ($announcement->url)
                        <a
                            href="{{ $announcement->url }}"
                            @if ($isExternal)
                                target="_blank"
                                rel="noopener noreferrer"
                            @endif
                            class="inline-flex items-center gap-1.5 transition-colors duration-300 hover:text-soft-gold underline underline-offset-2"
                        >
                            <span>{{ $text }}</span>
                            @if ($isExternal)
                                <svg class="h-3 w-3 inline-block opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                </svg>
                            @endif
                        </a>
                    @else
                        <span>{{ $text }}</span>
                    @endif
                </div>
            </div>

            <button
                type="button"
                @click="dismissed = true; localStorage.setItem('leen_announcement_dismissed_' + id, '1')"
                aria-label="{{ __('announcements.close') }}"
                class="absolute right-3 sm:right-5 top-1/2 -translate-y-1/2 text-silk-cream/70 hover:text-silk-cream transition-colors p-1"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </aside>
    @endif
@endif
