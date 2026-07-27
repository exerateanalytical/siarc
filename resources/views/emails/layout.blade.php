{{--
    Shared shell for every email the platform sends.

    Email clients are not browsers: Outlook renders through Word, Gmail strips
    <style> blocks and anything it does not recognise, and none of them support
    flexbox or grid reliably. So this is deliberately old-fashioned — nested
    tables, inline styles, no external stylesheet, no web fonts. It looks like
    2005 markup because that is what survives.

    Expects: $subject, $lang. Optional: $preheader, $heading, $ctaUrl, $ctaLabel.
--}}
@php
    $isFr  = ($lang ?? 'fr') === 'fr';
    $green = '#02301B';
    $leaf  = '#157A43';
    $gold  = '#E5A82E';
    $ink   = '#1D1B16';
    $muted = '#6F6B60';
    $line  = '#E7E3DA';
    $site  = rtrim(config('app.url'), '/');
@endphp
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="{{ $lang ?? 'fr' }}">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="x-apple-disable-message-reformatting" />
    <title>{{ $subject }}</title>
    <!--[if mso]>
    <style type="text/css">
        body, table, td { font-family: Arial, Helvetica, sans-serif !important; }
    </style>
    <![endif]-->
</head>
<body style="margin:0; padding:0; background-color:#F5F3EE; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%;">

{{-- Inbox preview line. Hidden in the body, but most clients show it beside the
     subject, so leaving it empty means they scrape the first visible words. --}}
@if(! empty($preheader))
<div style="display:none; font-size:1px; color:#F5F3EE; line-height:1px; max-height:0; max-width:0; opacity:0; overflow:hidden;">
    {{ $preheader }}
    &#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;
</div>
@endif

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#F5F3EE;">
    <tr>
        <td align="center" style="padding:24px 12px;">

            {{-- width:100% + max-width is what lets this shrink on a phone; the
                 width="600" attribute is for Outlook, which ignores max-width and
                 would otherwise render the card at full window width. --}}
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:100%; max-width:600px; background-color:#FFFFFF; border-radius:14px; overflow:hidden; border:1px solid {{ $line }};">

                {{-- Cameroon tricolour band, same motif as the site header --}}
                <tr>
                    <td style="font-size:0; line-height:0; height:5px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td width="33%" style="height:5px; background-color:#014D25; font-size:0; line-height:0;">&nbsp;</td>
                                <td width="34%" style="height:5px; background-color:#CA0107; font-size:0; line-height:0;">&nbsp;</td>
                                <td width="33%" style="height:5px; background-color:{{ $gold }}; font-size:0; line-height:0;">&nbsp;</td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- Logo, with the brand name as its alt text.

                     Most clients block remote images until the reader allows
                     them, so the alt is styled to render in the brand serif and
                     green — a blocked logo then reads as "Artisan Hub 237"
                     rather than a broken-image icon. The tagline underneath is
                     real text and always shows.

                     The src must be absolute; a relative path resolves against
                     the mail client, not the site. --}}
                <tr>
                    <td align="left" style="padding:26px 32px 0 32px;">
                        <a href="{{ $site }}" style="text-decoration:none;">
                            {{-- asset() builds this from APP_URL, so it is already
                                 absolute — which email needs, since a relative
                                 path would resolve against the mail client. --}}
                            <img src="{{ brand_asset('full') }}"
                                 alt="Artisan Hub 237"
                                 width="200" height="68"
                                 style="display:block; width:200px; max-width:200px; height:auto; border:0; outline:none; text-decoration:none; font-family:Georgia,'Times New Roman',serif; font-size:19px; font-weight:bold; color:{{ $green }};">
                            <span style="display:block; margin-top:6px; font-family:Arial,Helvetica,sans-serif; font-size:11px; color:{{ $leaf }};">{{ $isFr ? 'Notre héritage, notre fierté, notre avenir' : 'Our heritage, our pride, our future' }}</span>
                        </a>
                    </td>
                </tr>

                @if(! empty($heading))
                <tr>
                    <td align="left" style="padding:22px 32px 0 32px;">
                        <h1 style="margin:0; font-family:Georgia,'Times New Roman',serif; font-size:22px; line-height:1.35; font-weight:bold; color:{{ $ink }};">{{ $heading }}</h1>
                    </td>
                </tr>
                @endif

                <tr>
                    <td align="left" style="padding:16px 32px 0 32px; font-family:Arial,Helvetica,sans-serif; font-size:14px; line-height:1.65; color:#3A3A35;">
                        {{ $slot }}
                    </td>
                </tr>

                @if(! empty($ctaUrl) && ! empty($ctaLabel))
                <tr>
                    <td align="left" style="padding:26px 32px 0 32px;">
                        {{-- Bulletproof button: Outlook ignores padding on <a>, so
                             the table cell provides the box and VML the fill. --}}
                        <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td align="center" bgcolor="{{ $green }}" style="border-radius:8px;">
                                    <a href="{{ $ctaUrl }}" target="_blank" style="display:inline-block; padding:13px 26px; font-family:Arial,Helvetica,sans-serif; font-size:14px; font-weight:bold; color:#FFFFFF; text-decoration:none; border-radius:8px;">{{ $ctaLabel }}</a>
                                </td>
                            </tr>
                        </table>
                        <p style="margin:14px 0 0 0; font-family:Arial,Helvetica,sans-serif; font-size:11.5px; line-height:1.6; color:{{ $muted }};">
                            {{ $isFr ? 'Si le bouton ne fonctionne pas, copiez ce lien dans votre navigateur :' : 'If the button does not work, copy this link into your browser:' }}<br />
                            <a href="{{ $ctaUrl }}" style="color:{{ $leaf }}; text-decoration:underline; word-break:break-all;">{{ $ctaUrl }}</a>
                        </p>
                    </td>
                </tr>
                @endif

                <tr>
                    <td style="padding:28px 32px 0 32px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr><td style="height:1px; background-color:{{ $line }}; font-size:0; line-height:0;">&nbsp;</td></tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td align="left" style="padding:18px 32px 30px 32px; font-family:Arial,Helvetica,sans-serif; font-size:11.5px; line-height:1.7; color:{{ $muted }};">
                        <p style="margin:0 0 8px 0;">
                            <a href="{{ $site }}" style="color:{{ $leaf }}; text-decoration:none;">artisanhub237.com</a>
                            @if(config('legal.company.email'))
                            &nbsp;&middot;&nbsp;<a href="mailto:{{ config('legal.company.email') }}" style="color:{{ $leaf }}; text-decoration:none;">{{ config('legal.company.email') }}</a>
                            @endif
                        </p>
                        <p style="margin:0; color:#9A968C;">
                            {{ $isFr
                               ? 'Artisan Hub 237 met en relation artisans et acheteurs. La plateforme n\'est pas partie aux transactions et n\'encaisse aucun paiement.'
                               : 'Artisan Hub 237 connects artisans and buyers. The platform is not a party to transactions and collects no payments.' }}
                        </p>
                    </td>
                </tr>
            </table>

        </td>
    </tr>
</table>
</body>
</html>
