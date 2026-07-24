{{--
    Reusable text-field partial for checkout forms.

    Renders a <label> + <input> + @error block using brand tokens, matching
    the field contract used across checkout-page.blade.php. This is a plain
    Blade partial (@include), not a component — wire:model bindings are
    passed through verbatim so Livewire keeps binding the exact property
    the caller already uses.

    Required:
    - $field       string  Property name. Used for `id`, `for`, and the @error key.
    - $label       string  Translation key resolved via __() for the <label> text.
    - $wireModel   string  Full wire:model attribute, e.g. 'wire:model="firstName"'
                           or 'wire:model.live="addressMode"'.

    Optional:
    - $type              string       Input `type` attribute. Default: 'text'.
    - $placeholder       string|null  Translation key resolved via __() for the placeholder. Default: null.
    - $colSpan           string       Extra classes on the wrapper <div> (e.g. 'sm:col-span-2'). Default: ''.
    - $inputAttributes   string       Extra raw HTML attributes appended to the <input> tag
                                      (e.g. 'maxlength="2"'). Default: ''.
--}}

@php
    $type ??= 'text';
    $placeholder ??= null;
    $colSpan ??= '';
    $inputAttributes ??= '';
@endphp

<div class="{{ $colSpan }}">
    <label for="{{ $field }}" class="mb-1 block text-sm font-medium text-intense-cocoa">
        {{ __($label) }}
    </label>
    <input
        id="{{ $field }}"
        type="{{ $type }}"
        {!! $wireModel !!}
        @if ($placeholder) placeholder="{{ __($placeholder) }}" @endif
        {!! $inputAttributes !!}
        class="w-full border border-intense-cocoa/40 bg-silk-cream px-3 py-2 text-sm text-intense-cocoa placeholder:text-intense-cocoa/80 transition-colors focus:border-intense-cocoa focus:outline-none"
    />
    @error($field)
        <p class="mt-1 text-sm text-error">{{ $message }}</p>
    @enderror
</div>
