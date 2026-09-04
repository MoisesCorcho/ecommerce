<x-mail::layout>
<x-slot:head>
<meta name="color-scheme" content="light dark">
<meta name="supported-color-schemes" content="light dark">
<style>
:root {
  color-scheme: light dark;
  supported-color-schemes: light dark;
}
@media (prefers-color-scheme: dark) {
  .leen-contrast-box {
    background-color: #FBF9F5 !important;
    border-color: #EFEAE4 !important;
  }
}
</style>
</x-slot:head>

<x-slot:header>
<x-mail::header :url="config('app.url')">
<span class="leen-contrast-box" style="display: inline-block; background-color: #FBF9F5; padding: 10px 24px; border-radius: 12px; border: 1px solid #EFEAE4;">
<img src="{{ $logoUrl }}" alt="{{ config('app.name', 'Leen') }}" style="height: 44px; width: auto; max-height: 44px; display: block; margin: 0 auto;">
</span>
</x-mail::header>
</x-slot:header>

# {{ __('auth.emails.reset_password.greeting', ['name' => $user->name ?? '']) }}

{{ __('auth.emails.reset_password.line_1') }}

<x-mail::button :url="$url" color="primary">
{{ __('auth.emails.reset_password.action') }}
</x-mail::button>

{{ __('auth.emails.reset_password.line_2', ['count' => $count]) }}

{{ __('auth.emails.reset_password.line_3') }}

<x-slot:footer>
<x-mail::footer>
© {{ date('Y') }} {{ config('app.name', 'Leen') }}. {{ __('auth.emails.footer_note') }}
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
