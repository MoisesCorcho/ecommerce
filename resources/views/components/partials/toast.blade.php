{{--
    Self-contained toast confirmation listener. Wrap page content with this component;
    it listens on `window` for the `toast` browser event (`$this->dispatch('toast', message: ...)`)
    fired by any Livewire component nested in its slot, and renders a dismissible banner.

    Optional undo action: dispatch with `undoEvent`/`undoPayload` (a Livewire method name and
    its single argument) to render a secondary "undo" affordance next to the message, e.g.
    `$this->dispatch('toast', message: '...', undoEvent: 'restoreThing', undoPayload: $id)`.
--}}
<div
    x-data="{
        toastMessage: '',
        toastVisible: false,
        toastUndoEvent: null,
        toastUndoPayload: null,
        showToast(message, undoEvent = null, undoPayload = null) {
            this.toastMessage = message;
            this.toastUndoEvent = undoEvent;
            this.toastUndoPayload = undoPayload;
            this.toastVisible = true;
            setTimeout(() => { this.toastVisible = false; }, 3000);
        },
        undo() {
            if (this.toastUndoEvent) {
                $wire.call(this.toastUndoEvent, this.toastUndoPayload);
            }
            this.toastVisible = false;
        }
    }"
    x-on:toast.window="showToast($event.detail.message, $event.detail.undoEvent, $event.detail.undoPayload)"
>
    <div
        x-show="toastVisible"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-4"
        class="fixed top-20 right-4 z-[100] flex items-center gap-4 bg-intense-cocoa px-6 py-4 text-base font-medium text-silk-cream shadow-ambient"
        role="status"
        aria-live="polite"
    >
        <span x-text="toastMessage"></span>
        <button
            type="button"
            x-show="toastUndoEvent"
            x-on:click="undo()"
            class="text-soft-gold underline underline-offset-2 hover:text-silk-cream"
        >
            {{ __('storefront.undo') }}
        </button>
    </div>

    {{ $slot }}
</div>
