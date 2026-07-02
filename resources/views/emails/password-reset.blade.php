@if ($lang === 'fr')
Bonjour {{ $firstName }},

Cliquez sur le lien ci-dessous pour réinitialiser votre mot de passe de la Galerie virtuelle de l'artisanat du Cameroun :

{{ $resetUrl }}

Ce lien expire dans 60 minutes. Si vous n'êtes pas à l'origine de cette demande, ignorez ce message.

— Galerie virtuelle de l'artisanat du Cameroun
@else
Hello {{ $firstName }},

Click the link below to reset your Galerie virtuelle de l'artisanat du Cameroun password:

{{ $resetUrl }}

This link expires in 60 minutes. If you didn't request a reset, ignore this email.

— Galerie virtuelle de l'artisanat du Cameroun
@endif
