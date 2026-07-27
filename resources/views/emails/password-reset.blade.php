@php $isFr = ($lang ?? 'fr') === 'fr'; @endphp
@component('emails.layout', [
    'message'   => $message ?? null,
    'lang'      => $lang,
    'subject'   => $subject,
    'preheader' => $isFr ? 'Lien de réinitialisation, valable 60 minutes.' : 'Password reset link, valid for 60 minutes.',
    'heading'   => $isFr ? 'Réinitialiser votre mot de passe' : 'Reset your password',
    'ctaUrl'    => $resetUrl,
    'ctaLabel'  => $isFr ? 'Choisir un nouveau mot de passe' : 'Choose a new password',
])
<p style="margin:0 0 14px 0;">{{ $isFr ? 'Bonjour' : 'Hello' }} {{ $firstName }},</p>
<p style="margin:0;">
    {{ $isFr
       ? 'Vous avez demandé à réinitialiser le mot de passe de votre compte Artisan Hub 237. Le lien ci-dessous expire dans 60 minutes.'
       : 'You asked to reset the password on your Artisan Hub 237 account. The link below expires in 60 minutes.' }}
</p>
<p style="margin:16px 0 0 0; font-size:13px; color:#6F6B60;">
    {{ $isFr
       ? 'Si vous n\'êtes pas à l\'origine de cette demande, ignorez ce message : votre mot de passe actuel reste valable.'
       : 'If you did not request this, ignore this message — your current password still works.' }}
</p>
@endcomponent
