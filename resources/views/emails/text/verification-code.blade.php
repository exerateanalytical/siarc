@php $isFr = ($lang ?? 'fr') === 'fr'; @endphp
@if($isFr)
Artisan Hub 237
Notre héritage, notre fierté, notre avenir

CONFIRMEZ VOTRE ADRESSE EMAIL

Saisissez ce code sur Artisan Hub 237 pour activer votre compte :

    {{ $code }}

Ce code expire dans 10 minutes.

Si vous n'êtes pas à l'origine de cette demande, ignorez ce message. Aucun
compte ne sera activé et personne ne peut utiliser ce code sans accès à votre
boîte mail.

--
{{ rtrim(config('app.url'), '/') }}@if(config('legal.company.email')) · {{ config('legal.company.email') }}@endif

Artisan Hub 237 met en relation artisans et acheteurs. La plateforme n'est pas
partie aux transactions et n'encaisse aucun paiement.
@else
Artisan Hub 237
Our heritage, our pride, our future

CONFIRM YOUR EMAIL ADDRESS

Enter this code on Artisan Hub 237 to activate your account:

    {{ $code }}

This code expires in 10 minutes.

If you did not request this, ignore this message. No account will be activated,
and nobody can use this code without access to your inbox.

--
{{ rtrim(config('app.url'), '/') }}@if(config('legal.company.email')) · {{ config('legal.company.email') }}@endif

Artisan Hub 237 connects artisans and buyers. The platform is not a party to
transactions and collects no payments.
@endif
