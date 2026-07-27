@php
    $isFr = ($lang ?? 'fr') === 'fr';
@endphp
@component('emails.layout', [
    'lang'      => $lang,
    'subject'   => $subject,
    'preheader' => $isFr ? "Votre code : {$code} — valable 10 minutes." : "Your code: {$code} — valid for 10 minutes.",
    'heading'   => $isFr ? 'Confirmez votre adresse email' : 'Confirm your email address',
])
<p style="margin:0 0 18px 0;">
    {{ $isFr
       ? 'Saisissez ce code sur Artisan Hub 237 pour activer votre compte.'
       : 'Enter this code on Artisan Hub 237 to activate your account.' }}
</p>

{{-- The code itself. Letter-spaced and boxed so it reads cleanly and is easy to
     select on a phone, which is how most members will copy it. --}}
<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 18px 0;">
    <tr>
        <td align="center" bgcolor="#F1F7F3" style="border:1px solid #CFE0D4; border-radius:10px; padding:18px 30px;">
            <span style="font-family:'Courier New',Courier,monospace; font-size:30px; font-weight:bold; letter-spacing:9px; color:#02301B;">{{ $code }}</span>
        </td>
    </tr>
</table>

<p style="margin:0 0 14px 0; font-size:13px; color:#6F6B60;">
    {{ $isFr
       ? 'Ce code expire dans 10 minutes.'
       : 'This code expires in 10 minutes.' }}
</p>

<p style="margin:0; font-size:13px; color:#6F6B60;">
    {{ $isFr
       ? 'Si vous n\'êtes pas à l\'origine de cette demande, ignorez ce message — aucun compte ne sera activé et personne ne peut utiliser ce code sans votre adresse email.'
       : 'If you did not request this, ignore this message — no account will be activated, and nobody can use this code without access to your inbox.' }}
</p>
@endcomponent
