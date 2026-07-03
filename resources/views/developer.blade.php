<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Developer Portal — Galerie virtuelle de l'artisanat du Cameroun API</title>
<script src="{{ asset('vendor/tailwindcss.js') }}"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    brand: { 50:'#fef9ee',100:'#fdf0d3',200:'#fada9a',300:'#f7c062',400:'#f4a32a',500:'#e8880e',600:'#cc6a09',700:'#a84e0b',800:'#873d10',900:'#6e3311' },
                    forest: { 50:'#f0f9f4',100:'#dbf0e3',200:'#b8e0c9',300:'#8cc9a8',400:'#5ba883',500:'#2d6a4f',600:'#1b4332',700:'#0d2b1e',800:'#082018',900:'#03130e' },
                },
                fontFamily: { sans: ['Poppins', 'system-ui', 'sans-serif'] },
            }
        }
    }
</script>
<script src="{{ asset('vendor/lucide.min.js') }}"></script>
<link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
<style>body{font-family:'Poppins',system-ui,sans-serif;}</style>
</head>
<body class="bg-[#F8F6F2] text-[#1B1B18] antialiased">

{{-- Minimal header --}}
<header class="bg-white border-b border-[#EEEDEA]">
    <div class="max-w-6xl mx-auto px-4 h-[60px] flex items-center justify-between">
        <a href="/" class="flex items-center gap-3">
            <img src="{{ asset('images/landing/logo.png') }}" alt="" class="w-[32px] h-[35px] object-contain">
            <span class="font-bold text-[#1B1B18] text-[12px] uppercase tracking-[0.02em]">Galerie Virtuelle Nationale <span class="font-semibold text-[#157A43] normal-case tracking-normal">— Developer Portal</span></span>
        </a>
        <a href="/tableau-de-bord" class="text-[13px] font-semibold text-[#14652F] hover:text-[#14532D] flex items-center gap-1.5">
            <i data-lucide="layout-dashboard" class="w-4 h-4" style="stroke-width:1.7"></i>
            Dashboard
        </a>
    </div>
</header>
<div class="flex h-[5px]"><div class="w-[46%] bg-[#094F2B]"></div><div class="w-[26%] bg-[#B61012]"></div><div class="flex-1 bg-[#E9A411]"></div></div>

<div class="max-w-4xl mx-auto px-4 py-10">
    <h1 class="text-xl font-bold text-gray-900 mb-1">Developer Portal</h1>
    <p class="text-sm text-gray-500 mb-6">Manage your API keys, explore endpoints, and integrate Galerie virtuelle de l'artisanat du Cameroun data into your applications.</p>

    @if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 rounded-lg px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif

    @if(session('new_api_key'))
    <div class="mb-4 bg-amber-50 border border-amber-200 rounded-lg p-4 text-sm text-amber-900">
        <strong class="block mb-1">Your new API key — copy it now, it will not be shown again:</strong>
        <div class="font-mono bg-gray-900 text-green-400 rounded-lg px-3 py-2 mt-1 break-all">{{ session('new_api_key') }}</div>
    </div>
    @endif

    <div class="grid grid-cols-3 gap-3 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 text-center">
            <div class="text-2xl font-black text-forest-600">{{ $keyCount }}</div>
            <div class="text-xs text-gray-500">Active Keys</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 text-center">
            <div class="text-2xl font-black text-forest-600">60</div>
            <div class="text-xs text-gray-500">Req / minute</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 text-center">
            <div class="text-2xl font-black text-forest-600">77</div>
            <div class="text-xs text-gray-500">API Endpoints</div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h2 class="text-sm font-bold text-gray-900 mb-3">Your API Keys</h2>
                @if($keys->isEmpty())
                <p class="text-sm text-gray-500">No API keys yet. Create one below to get started.</p>
                @else
                @foreach($keys as $key)
                <div class="bg-gray-50 rounded-lg px-3 py-2.5 flex items-center gap-2.5 mb-2 flex-wrap">
                    <span class="font-semibold text-sm min-w-[110px]">{{ $key->name }}</span>
                    <span class="font-mono text-xs text-gray-500 flex-1 break-all">{{ $key->key_prefix ?? substr($key->api_key??'',0,12) }}••••••••</span>
                    <span class="text-[11px] font-bold px-2 py-0.5 rounded-full {{ ($key->is_active??true)?'bg-green-100 text-green-700':'bg-red-100 text-red-700' }}">{{ ($key->is_active??true)?'Active':'Revoked' }}</span>
                    @if($key->is_active??true)
                    <form method="POST" action="/developer/keys/{{ $key->id }}/revoke" class="inline">
                        @csrf
                        <button type="submit" class="text-xs font-semibold border border-gray-300 rounded-md px-2.5 py-1 hover:border-red-400 hover:text-red-600" onclick="return confirm('Revoke this key? This cannot be undone.')">Revoke</button>
                    </form>
                    @endif
                </div>
                @endforeach
                @endif

                <div class="mt-4 pt-4 border-t border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">Generate New Key</h3>
                    <form method="POST" action="/developer/keys">
                        @csrf
                        <div class="mb-3">
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Key Name / Label</label>
                            <input type="text" name="name" required placeholder="e.g. My App, Production, Testing"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-forest-400">
                        </div>
                        <button type="submit" class="bg-forest-500 hover:bg-forest-600 text-white font-semibold text-sm px-4 py-2 rounded-lg transition-colors">Generate API Key</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h2 class="text-sm font-bold text-gray-900 mb-3">Quick Start</h2>
                <p class="text-xs text-gray-500 mb-2">Authenticate every request with your API key in the header:</p>
                <div class="bg-gray-900 text-gray-100 rounded-lg p-3 font-mono text-xs overflow-x-auto"><span class="text-green-400">Authorization:</span> <span class="text-yellow-300">Bearer YOUR_API_KEY</span></div>
                <p class="text-xs text-gray-500 mt-3 mb-2">Example — list companies:</p>
                <div class="bg-gray-900 text-gray-100 rounded-lg p-3 font-mono text-xs overflow-x-auto leading-relaxed">curl -X GET \<br>&nbsp;&nbsp;<span class="text-yellow-300">https://api.camcompany.cm/v1/companies</span> \<br>&nbsp;&nbsp;-H <span class="text-yellow-300">"Authorization: Bearer ck_YOUR_KEY"</span></div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h2 class="text-sm font-bold text-gray-900 mb-3">Base URL</h2>
                <div class="bg-gray-900 text-gray-100 rounded-lg p-3 font-mono text-xs"><span class="text-yellow-300">https://api.camcompany.cm/v1</span></div>
                <p class="text-xs text-gray-500 mt-3">All responses are JSON. Rate limit: <strong>60 req/min</strong> authenticated, <strong>20 req/min</strong> public.</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <h2 class="text-sm font-bold text-gray-900 mb-3">Available Endpoints</h2>
        <ul class="divide-y divide-gray-100 text-sm">
            <li class="py-2 flex gap-2 items-center"><span class="text-[11px] font-extrabold px-1.5 py-0.5 rounded bg-green-100 text-green-700 font-mono">GET</span><code>/companies</code> — List verified companies (paginated)</li>
            <li class="py-2 flex gap-2 items-center"><span class="text-[11px] font-extrabold px-1.5 py-0.5 rounded bg-green-100 text-green-700 font-mono">GET</span><code>/companies/{slug}</code> — Get company details</li>
            <li class="py-2 flex gap-2 items-center"><span class="text-[11px] font-extrabold px-1.5 py-0.5 rounded bg-green-100 text-green-700 font-mono">GET</span><code>/offerings</code> — List share offerings</li>
            <li class="py-2 flex gap-2 items-center"><span class="text-[11px] font-extrabold px-1.5 py-0.5 rounded bg-green-100 text-green-700 font-mono">GET</span><code>/offerings/{id}</code> — Get offering details</li>
            <li class="py-2 flex gap-2 items-center"><span class="text-[11px] font-extrabold px-1.5 py-0.5 rounded bg-blue-100 text-blue-700 font-mono">POST</span><code>/offerings/{id}/pledge</code> — Create investment pledge (auth)</li>
            <li class="py-2 flex gap-2 items-center"><span class="text-[11px] font-extrabold px-1.5 py-0.5 rounded bg-green-100 text-green-700 font-mono">GET</span><code>/jobs</code> — List open job postings</li>
            <li class="py-2 flex gap-2 items-center"><span class="text-[11px] font-extrabold px-1.5 py-0.5 rounded bg-green-100 text-green-700 font-mono">GET</span><code>/jobs/{id}</code> — Get job details</li>
            <li class="py-2 flex gap-2 items-center"><span class="text-[11px] font-extrabold px-1.5 py-0.5 rounded bg-blue-100 text-blue-700 font-mono">POST</span><code>/jobs/{id}/apply</code> — Apply for a job (auth)</li>
            <li class="py-2 flex gap-2 items-center"><span class="text-[11px] font-extrabold px-1.5 py-0.5 rounded bg-green-100 text-green-700 font-mono">GET</span><code>/blog</code> — List blog posts</li>
            <li class="py-2 flex gap-2 items-center"><span class="text-[11px] font-extrabold px-1.5 py-0.5 rounded bg-green-100 text-green-700 font-mono">GET</span><code>/me</code> — Authenticated user profile (auth)</li>
            <li class="py-2 flex gap-2 items-center"><span class="text-[11px] font-extrabold px-1.5 py-0.5 rounded bg-green-100 text-green-700 font-mono">GET</span><code>/me/portfolio</code> — My investments (auth)</li>
            <li class="py-2 flex gap-2 items-center"><span class="text-[11px] font-extrabold px-1.5 py-0.5 rounded bg-green-100 text-green-700 font-mono">GET</span><code>/me/wallet</code> — My wallet balance (auth)</li>
        </ul>
        <a href="/docs/api" class="inline-block mt-3 text-sm text-forest-600 font-semibold hover:underline">View full OpenAPI 3.1 documentation →</a>
    </div>
</div>

<script>lucide.createIcons();</script>
</body>
</html>
