@php
    $isFr = ($lang ?? 'fr') === 'fr';
@endphp
@component('emails.layout', [
    'message'   => $message ?? null,
    'lang'      => $lang,
    'subject'   => $subject,
    'preheader' => \Illuminate\Support\Str::limit(strip_tags($body), 110),
    'heading'   => $heading ?? null,
    'ctaUrl'    => $ctaUrl ?? null,
    'ctaLabel'  => $ctaLabel ?? ($isFr ? 'Ouvrir dans mon espace' : 'Open in my dashboard'),
])
{{-- Notification bodies are composed as plain text with newlines (they are also
     stored as the in-app notification), so preserve the author's line breaks
     rather than collapsing them into one paragraph. --}}
<p style="margin:0; white-space:pre-line;">{{ $body }}</p>
@endcomponent
