{{--
    Browser tab and home-screen icons.

    No page declared these before, so every tab fell back to /favicon.ico —
    which was a 0-byte file, i.e. a blank icon everywhere.

    An admin can upload their own through Paramètres → Branding; that upload
    wins, and the packaged brand mark is the fallback.
--}}
{{--
    The lookup is wrapped because this partial is included by the error views,
    and the commonest reason a 500 page is being rendered at all is that the
    database has just gone away. An uncaught query here means Laravel cannot
    render errors::500 either, and the visitor gets Symfony's bare grey
    "Oops! An Error Occurred" instead of the branded page — the one moment the
    site most needs to look like itself. A missing custom favicon is not worth
    a page; fall back to the packaged brand mark and carry on.
--}}
@php
    try {
        $favSetting = \Illuminate\Support\Facades\DB::table('platform_settings')
            ->whereIn('key', ['favicon_path', 'branding_favicon'])
            ->value('value');
    } catch (\Throwable $e) {
        $favSetting = null;
    }

    $favUrl = filled($favSetting) ? asset('storage/' . $favSetting) : brand_asset('mark');
@endphp
<link rel="icon" type="image/png" href="{{ $favUrl }}">
<link rel="apple-touch-icon" href="{{ $favUrl }}">
{{-- Address-bar tint on Android Chrome, matching the emblem's deep green. --}}
<meta name="theme-color" content="#02301B">
