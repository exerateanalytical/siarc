{{--
    Browser tab and home-screen icons.

    No page declared these before, so every tab fell back to /favicon.ico —
    which was a 0-byte file, i.e. a blank icon everywhere.

    An admin can upload their own through Paramètres → Branding; that upload
    wins, and the packaged brand mark is the fallback.
--}}
@php
    $favSetting = \Illuminate\Support\Facades\DB::table('platform_settings')
        ->whereIn('key', ['favicon_path', 'branding_favicon'])
        ->value('value');

    $favUrl = filled($favSetting) ? asset('storage/' . $favSetting) : brand_asset('mark');
@endphp
<link rel="icon" type="image/png" href="{{ $favUrl }}">
<link rel="apple-touch-icon" href="{{ $favUrl }}">
{{-- Address-bar tint on Android Chrome, matching the emblem's deep green. --}}
<meta name="theme-color" content="#02301B">
