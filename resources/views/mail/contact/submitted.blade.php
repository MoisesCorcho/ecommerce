<x-mail::layout>
<x-slot:header>
<x-mail::header :url="config('app.url')">
<img src="{{ $logoUrl }}" alt="{{ config('app.name', 'Leen') }}" style="height: 48px; width: auto; max-height: 48px;">
</x-mail::header>
</x-slot:header>

# {{ __('contact_submissions.mail.heading') }}

<x-mail::panel>
**{{ __('contact.form.name') }}:** {{ $senderName }}  
**{{ __('contact.form.email') }}:** [{{ $senderEmail }}](mailto:{{ $senderEmail }})  
**{{ __('contact.form.subject') }}:** {{ $subjectLine }}
</x-mail::panel>

### {{ __('contact.form.message') }}

{{ $body }}

@if($adminUrl)
<x-mail::button :url="$adminUrl" color="primary">
{{ __('contact_submissions.mail.view_in_panel') }}
</x-mail::button>
@endif

<x-slot:footer>
<x-mail::footer>
© {{ date('Y') }} {{ config('app.name', 'Leen') }}. {{ __('contact_submissions.mail.footer_note') }}
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>



