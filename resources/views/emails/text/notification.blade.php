@php $isFr = ($lang ?? 'fr') === 'fr'; @endphp
Artisan Hub 237
@if($heading ?? null)

{{ mb_strtoupper($heading) }}
@endif

{{ $body }}
@if($ctaUrl ?? null)

{{ $isFr ? 'Ouvrir dans mon espace :' : 'Open in my dashboard:' }}
{{ $ctaUrl }}
@endif

--
{{ rtrim(config('app.url'), '/') }}@if(config('legal.company.email')) · {{ config('legal.company.email') }}@endif
@if($isFr)

Artisan Hub 237 met en relation artisans et acheteurs. La plateforme n'est pas
partie aux ventes et n'en reçoit pas le prix ; seuls ses propres frais de
service lui sont réglés.
@else

Artisan Hub 237 connects artisans and buyers. The platform is not a party to
sales and does not receive the price; only its own service fees are paid to it.
@endif
