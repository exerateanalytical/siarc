@if ($lang === 'fr')
Bonjour {{ $firstName }},

Cliquez sur le lien ci-dessous pour réinitialiser votre mot de passe d'Artisan Hub 237 :

{{ $resetUrl }}

Ce lien expire dans 60 minutes. Si vous n'êtes pas à l'origine de cette demande, ignorez ce message.

— Artisan Hub 237
@else
Hello {{ $firstName }},

Click the link below to reset your Artisan Hub 237 password:

{{ $resetUrl }}

This link expires in 60 minutes. If you didn't request a reset, ignore this email.

— Artisan Hub 237
@endif
