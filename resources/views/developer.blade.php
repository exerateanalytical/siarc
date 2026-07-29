<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Developer Portal — Artisan Hub 237 API</title>
<style>
    /* This page's own colour tokens. They used to be an inline
       `tailwind.config` compiled in the browser; the stylesheet is
       static now and reads them from here, so a token that means a
       different shade on another page still resolves per page —
       including inside shared partials. See tailwind.config.cjs. */
    :root {
        --c-brand-100: 253 240 211;
        --c-brand-200: 250 218 154;
        --c-brand-300: 247 192 98;
        --c-brand-400: 244 163 42;
        --c-brand-50: 254 249 238;
        --c-brand-500: 232 136 14;
        --c-brand-600: 204 106 9;
        --c-brand-700: 168 78 11;
        --c-brand-800: 135 61 16;
        --c-brand-900: 110 51 17;
        --c-forest-100: 219 240 227;
        --c-forest-200: 184 224 201;
        --c-forest-400: 91 168 131;
        --c-forest-50: 240 249 244;
        --c-forest-500: 45 106 79;
        --c-forest-600: 27 67 50;
        --c-forest-700: 13 43 30;
        --c-forest-800: 8 32 24;
        --c-forest-900: 3 19 14;
    }
</style>
<script src="{{ asset('vendor/lucide-subset.js') }}"></script>
<link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
<style>
        /* Nothing may scroll the page sideways on a phone; wide content
           (tables, diagrams) scrolls inside its own container instead. */
        html, body { overflow-x: clip; }
body{font-family:'Poppins',system-ui,sans-serif;}</style>
    @include('pages.partials.ui-kit')
    @include('pages.partials.favicon')
    {{-- The one stylesheet. Built by `npm run build:assets`; see tailwind.config.cjs. --}}
    <link rel="stylesheet" href="{{ asset('vendor/app.css') }}">
</head>
<body class="bg-[#F8F6F2] dark:bg-[#0A0C09] text-[#1B1B18] dark:text-[#F3EFE7] antialiased">

{{-- Minimal header --}}
<header class="bg-white dark:bg-[#12150F] border-b border-[#EEEDEA] dark:border-[#262B21]">
    <div class="max-w-6xl mx-auto px-4 h-[60px] flex items-center justify-between">
        <a href="/" class="flex items-center gap-3">
            <img src="{{ brand_asset('mark') }}" alt="" class="w-[35px] h-[35px] object-contain">
            <span class="font-bold text-[#1B1B18] dark:text-[#F3EFE7] text-[12px] uppercase tracking-[0.02em]">Artisan Hub 237 <span class="font-semibold text-[#157A43] dark:text-[#339B56] normal-case tracking-normal">— Developer Portal</span></span>
        </a>
        <a href="/tableau-de-bord" class="text-[13px] font-semibold text-[#14652F] dark:text-[#339B56] hover:text-[#14532D] hover:dark:text-[#339B56] flex items-center gap-1.5">
            <i data-lucide="layout-dashboard" class="w-4 h-4" style="stroke-width:1.7"></i>
            Dashboard
        </a>
    </div>
</header>
<div class="flex h-[5px]"><div class="w-[46%] bg-[#094F2B]"></div><div class="w-[26%] bg-[#B61012]"></div><div class="flex-1 bg-[#E9A411]"></div></div>

<div class="max-w-4xl mx-auto px-4 py-10">
    <h1 class="text-xl font-bold text-gray-900 dark:text-[#F3EFE7] mb-1">Developer Portal</h1>
    <p class="text-sm text-gray-500 dark:text-[#868778] mb-6">Manage your API keys, explore endpoints, and integrate Artisan Hub 237 data into your applications.</p>

    @if(session('success'))
    <div class="mb-4 bg-green-50 dark:bg-[#0C3D1D] border border-green-200 dark:border-[#1B5E33] rounded-lg px-4 py-3 text-sm text-green-800 dark:text-[#8BDCA6]">{{ session('success') }}</div>
    @endif

    @if(session('new_api_key'))
    <div class="mb-4 bg-amber-50 dark:bg-[#3A2B06] border border-amber-200 rounded-lg p-4 text-sm text-amber-900 dark:text-[#EDB33A]">
        <strong class="block mb-1">Your new API key — copy it now, it will not be shown again:</strong>
        <div class="font-mono bg-gray-900 text-green-400 rounded-lg px-3 py-2 mt-1 break-all">{{ session('new_api_key') }}</div>
    </div>
    @endif

    <div class="grid grid-cols-3 gap-3 mb-6">
        <div class="bg-white dark:bg-[#12150F] rounded-xl shadow-sm border border-gray-200 dark:border-[#262B21] p-4 text-center">
            <div class="text-2xl font-black text-forest-600 dark:text-[#339B56]">{{ $keyCount }}</div>
            <div class="text-xs text-gray-500 dark:text-[#868778]">Active Keys</div>
        </div>
        <div class="bg-white dark:bg-[#12150F] rounded-xl shadow-sm border border-gray-200 dark:border-[#262B21] p-4 text-center">
            <div class="text-2xl font-black text-forest-600 dark:text-[#339B56]">60</div>
            <div class="text-xs text-gray-500 dark:text-[#868778]">Req / minute</div>
        </div>
        <div class="bg-white dark:bg-[#12150F] rounded-xl shadow-sm border border-gray-200 dark:border-[#262B21] p-4 text-center">
            <div class="text-2xl font-black text-forest-600 dark:text-[#339B56]">77</div>
            <div class="text-xs text-gray-500 dark:text-[#868778]">API Endpoints</div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <div>
            <div class="bg-white dark:bg-[#12150F] rounded-xl shadow-sm border border-gray-200 dark:border-[#262B21] p-5">
                <h2 class="text-sm font-bold text-gray-900 dark:text-[#F3EFE7] mb-3">Your API Keys</h2>
                @if($keys->isEmpty())
                <p class="text-sm text-gray-500 dark:text-[#868778]">No API keys yet. Create one below to get started.</p>
                @else
                @foreach($keys as $key)
                <div class="bg-gray-50 dark:bg-[#0A0C09] rounded-lg px-3 py-2.5 flex items-center gap-2.5 mb-2 flex-wrap">
                    <span class="font-semibold text-sm min-w-[110px]">{{ $key->name }}</span>
                    <span class="font-mono text-xs text-gray-500 dark:text-[#868778] flex-1 break-all">{{ $key->key_prefix ?? substr($key->api_key??'',0,12) }}••••••••</span>
                    <span class="text-[11px] font-bold px-2 py-0.5 rounded-full {{ ($key->is_active??true)?'bg-green-100 dark:bg-[#0C3D1D] text-green-700 dark:text-[#8BDCA6]':'bg-red-100 dark:bg-[#3A1013] text-red-700 dark:text-[#F0555C]' }}">{{ ($key->is_active??true)?'Active':'Revoked' }}</span>
                    @if($key->is_active??true)
                    <form method="POST" action="/developer/keys/{{ $key->id }}/revoke" class="inline">
                        @csrf
                        <button type="submit" class="ui-btn ui-btn-danger ui-btn-sm" onclick="return confirm('Revoke this key? This cannot be undone.')">Revoke</button>
                    </form>
                    @endif
                </div>
                @endforeach
                @endif

                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-[#262B21]">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-[#F3EFE7] mb-2">Generate New Key</h3>
                    <form method="POST" action="/developer/keys">
                        @csrf
                        <div class="mb-3">
                            <label class="ui-label">Key Name / Label</label>
                            <input type="text" name="name" required placeholder="e.g. My App, Production, Testing"
                                class="ui-field">
                        </div>
                        <button type="submit" class="ui-btn ui-btn-primary">Generate API Key</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="bg-white dark:bg-[#12150F] rounded-xl shadow-sm border border-gray-200 dark:border-[#262B21] p-5">
                <h2 class="text-sm font-bold text-gray-900 dark:text-[#F3EFE7] mb-3">Quick Start</h2>
                <p class="text-xs text-gray-500 dark:text-[#868778] mb-2">Authenticate every request with your API key in the header:</p>
                <div class="bg-gray-900 text-gray-100 rounded-lg p-3 font-mono text-xs overflow-x-auto"><span class="text-green-400">Authorization:</span> <span class="text-yellow-300">Bearer YOUR_API_KEY</span></div>
                <p class="text-xs text-gray-500 dark:text-[#868778] mt-3 mb-2">Example — list companies:</p>
                <div class="bg-gray-900 text-gray-100 rounded-lg p-3 font-mono text-xs overflow-x-auto leading-relaxed">curl -X GET \<br>&nbsp;&nbsp;<span class="text-yellow-300">{{ rtrim(config('app.url'), '/') }}/api/v1/companies</span> \<br>&nbsp;&nbsp;-H <span class="text-yellow-300">"Authorization: Bearer ck_YOUR_KEY"</span></div>
            </div>

            <div class="bg-white dark:bg-[#12150F] rounded-xl shadow-sm border border-gray-200 dark:border-[#262B21] p-5">
                <h2 class="text-sm font-bold text-gray-900 dark:text-[#F3EFE7] mb-3">Base URL</h2>
                <div class="bg-gray-900 text-gray-100 rounded-lg p-3 font-mono text-xs"><span class="text-yellow-300">{{ rtrim(config('app.url'), '/') }}/api/v1</span></div>
                <p class="text-xs text-gray-500 dark:text-[#868778] mt-3">All responses are JSON. Rate limit: <strong>60 req/min</strong> authenticated, <strong>20 req/min</strong> public.</p>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-[#12150F] rounded-xl shadow-sm border border-gray-200 dark:border-[#262B21] p-5">
        <h2 class="text-sm font-bold text-gray-900 dark:text-[#F3EFE7] mb-3">Available Endpoints</h2>
        <ul class="divide-y divide-gray-100 dark:divide-[#262B21] text-sm">
            <li class="py-2 flex gap-2 items-center"><span class="text-[11px] font-extrabold px-1.5 py-0.5 rounded bg-green-100 dark:bg-[#0C3D1D] text-green-700 dark:text-[#8BDCA6] font-mono">GET</span><code>/companies</code> — List verified companies (paginated)</li>
            <li class="py-2 flex gap-2 items-center"><span class="text-[11px] font-extrabold px-1.5 py-0.5 rounded bg-green-100 dark:bg-[#0C3D1D] text-green-700 dark:text-[#8BDCA6] font-mono">GET</span><code>/companies/{slug}</code> — Get company details</li>
            <li class="py-2 flex gap-2 items-center"><span class="text-[11px] font-extrabold px-1.5 py-0.5 rounded bg-green-100 dark:bg-[#0C3D1D] text-green-700 dark:text-[#8BDCA6] font-mono">GET</span><code>/offerings</code> — List share offerings</li>
            <li class="py-2 flex gap-2 items-center"><span class="text-[11px] font-extrabold px-1.5 py-0.5 rounded bg-green-100 dark:bg-[#0C3D1D] text-green-700 dark:text-[#8BDCA6] font-mono">GET</span><code>/offerings/{id}</code> — Get offering details</li>
            <li class="py-2 flex gap-2 items-center"><span class="text-[11px] font-extrabold px-1.5 py-0.5 rounded bg-blue-100 dark:bg-[#0E2436] text-blue-700 dark:text-[#8FC2F0] font-mono">POST</span><code>/offerings/{id}/pledge</code> — Create investment pledge (auth)</li>
            <li class="py-2 flex gap-2 items-center"><span class="text-[11px] font-extrabold px-1.5 py-0.5 rounded bg-green-100 dark:bg-[#0C3D1D] text-green-700 dark:text-[#8BDCA6] font-mono">GET</span><code>/jobs</code> — List open job postings</li>
            <li class="py-2 flex gap-2 items-center"><span class="text-[11px] font-extrabold px-1.5 py-0.5 rounded bg-green-100 dark:bg-[#0C3D1D] text-green-700 dark:text-[#8BDCA6] font-mono">GET</span><code>/jobs/{id}</code> — Get job details</li>
            <li class="py-2 flex gap-2 items-center"><span class="text-[11px] font-extrabold px-1.5 py-0.5 rounded bg-blue-100 dark:bg-[#0E2436] text-blue-700 dark:text-[#8FC2F0] font-mono">POST</span><code>/jobs/{id}/apply</code> — Apply for a job (auth)</li>
            <li class="py-2 flex gap-2 items-center"><span class="text-[11px] font-extrabold px-1.5 py-0.5 rounded bg-green-100 dark:bg-[#0C3D1D] text-green-700 dark:text-[#8BDCA6] font-mono">GET</span><code>/blog</code> — List blog posts</li>
            <li class="py-2 flex gap-2 items-center"><span class="text-[11px] font-extrabold px-1.5 py-0.5 rounded bg-green-100 dark:bg-[#0C3D1D] text-green-700 dark:text-[#8BDCA6] font-mono">GET</span><code>/me</code> — Authenticated user profile (auth)</li>
            <li class="py-2 flex gap-2 items-center"><span class="text-[11px] font-extrabold px-1.5 py-0.5 rounded bg-green-100 dark:bg-[#0C3D1D] text-green-700 dark:text-[#8BDCA6] font-mono">GET</span><code>/me/portfolio</code> — My investments (auth)</li>
            <li class="py-2 flex gap-2 items-center"><span class="text-[11px] font-extrabold px-1.5 py-0.5 rounded bg-green-100 dark:bg-[#0C3D1D] text-green-700 dark:text-[#8BDCA6] font-mono">GET</span><code>/me/wallet</code> — My wallet balance (auth)</li>
        </ul>
        <a href="/docs/api" class="inline-block mt-3 text-sm text-forest-600 dark:text-[#339B56] font-semibold hover:underline">View full OpenAPI 3.1 documentation →</a>
    </div>
</div>

<script>lucide.createIcons();</script>
</body>
</html>
