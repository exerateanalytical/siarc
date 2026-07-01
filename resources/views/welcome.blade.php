<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Welcome — Galerie virtuelle de l'artisanat du Cameroun</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    brand: { 50:'#fef9ee',100:'#fdf0d3',200:'#fada9a',300:'#f7c062',400:'#f4a32a',500:'#e8880e',600:'#cc6a09',700:'#a84e0b',800:'#873d10',900:'#6e3311' },
                    forest: { 50:'#f0f9f4',100:'#dbf0e3',200:'#b8e0c9',300:'#8cc9a8',400:'#5ba883',500:'#2d6a4f',600:'#1b4332',700:'#0d2b1e',800:'#082018',900:'#03130e' },
                },
                fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] },
            }
        }
    }
</script>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>body{font-family:'Inter',system-ui,sans-serif;}</style>
</head>
<body class="bg-gray-50 text-gray-900 antialiased">

{{-- Minimal header --}}
<header class="bg-white border-b border-gray-200">
    <div class="max-w-6xl mx-auto px-4 h-14 flex items-center justify-between">
        <a href="/" class="flex items-center gap-2.5">
            <div class="w-7 h-7 bg-forest-500 rounded flex items-center justify-center">
                <i data-lucide="store" class="w-4 h-4 text-white"></i>
            </div>
            <span class="font-bold text-gray-900 text-sm">Galerie Artisanat <span class="font-normal text-gray-400">— SIAC Cameroun</span></span>
        </a>
        <a href="/dashboard" class="text-sm text-gray-500 hover:text-gray-900 flex items-center gap-1">
            <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
            Dashboard
        </a>
    </div>
</header>

@php
    $authUser = session('auth_user');
    $userType = $authUser['user_type'] ?? 'investor';
    $firstName = $authUser['first_name'] ?? 'there';
    $paths = [
        'investor' => [
            'label'    => 'Investor',
            'headline' => "Start investing in Cameroon's top companies",
            'sub'      => 'Follow these steps to make your first investment.',
            'steps'    => [
                ['title'=>'Complete KYC',          'desc'=>'Verify your identity to unlock share investing. Takes ~5 minutes.',           'url'=>'/investor-profile','cta'=>'Complete KYC'],
                ['title'=>'Fund Your Wallet',      'desc'=>'Top up via MTN MoMo, Orange Money, or Bank Transfer.',                       'url'=>'/wallet',          'cta'=>'Add funds'],
                ['title'=>'Browse Offerings',      'desc'=>'Explore CMF-approved share offerings from verified companies.',               'url'=>'/offerings',       'cta'=>'See offerings'],
                ['title'=>'Make Your First Pledge','desc'=>'Pick an offering, choose your amount, and confirm your pledge.',             'url'=>'/offerings',       'cta'=>'Invest now'],
            ],
        ],
        'job_seeker' => [
            'label'    => 'Job Seeker',
            'headline' => 'Land your next role at a top Cameroon company',
            'sub'      => 'Build your profile, create a CV, and apply to open positions.',
            'steps'    => [
                ['title'=>'Build Career Profile','desc'=>'Add headline, experience, education, and skills.',                               'url'=>'/my-profile','cta'=>'Set up profile'],
                ['title'=>'Create Your CV',      'desc'=>'Generate a professional CV using our builder.',                                  'url'=>'/cv',        'cta'=>'Build CV'],
                ['title'=>'Browse Open Jobs',    'desc'=>'Filter by full-time, part-time, contract, remote, or internship.',              'url'=>'/jobs',      'cta'=>'Browse jobs'],
                ['title'=>'Apply and Track',     'desc'=>'Submit applications and track status from your career profile.',                 'url'=>'/my-profile','cta'=>'View applications'],
            ],
        ],
        'company_owner' => [
            'label'    => 'Company Owner',
            'headline' => 'Get your company visible to investors and talent',
            'sub'      => 'Claim your listing and start attracting investment and great hires.',
            'steps'    => [
                ['title'=>'Find Your Company',   'desc'=>'Search the directory — your company may already be registered.',                'url'=>'/','cta'=>'Search directory'],
                ['title'=>'Claim Your Listing',  'desc'=>'Submit a claim with your role and supporting documents.',                       'url'=>'/','cta'=>'Find and claim'],
                ['title'=>'Complete Your Profile','desc'=>'Add company description, team, products, and official documents.',             'url'=>'/dashboard','cta'=>'Go to dashboard'],
                ['title'=>'Post a Job',          'desc'=>'Attract great hires by listing positions on the jobs board.',                   'url'=>'/dashboard','cta'=>'Post a job'],
            ],
        ],
        'developer' => [
            'label'    => 'Developer',
            'headline' => 'Integrate Cameroon company data into your app',
            'sub'      => '77 REST endpoints, OpenAPI 3.1 docs, webhook events.',
            'steps'    => [
                ['title'=>'Generate an API Key', 'desc'=>'Create your first key from the Developer Portal.',                              'url'=>'/developer','cta'=>'Get API key'],
                ['title'=>'Read the Docs',       'desc'=>'Explore all 77 endpoints — companies, offerings, jobs, investors, and more.',   'url'=>'/docs/api', 'cta'=>'Read docs'],
                ['title'=>'Make Your First Call','desc'=>'Try GET /companies — no auth needed for public endpoints.',                     'url'=>'/docs/api', 'cta'=>'Try it'],
                ['title'=>'Set Up Webhooks',     'desc'=>'Subscribe to new_offering, company_verified, offering_closed events.',          'url'=>'/developer','cta'=>'Configure'],
            ],
        ],
    ];
@endphp

<div class="max-w-3xl mx-auto px-4 py-12">
    <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Welcome, <span class="text-forest-500">{{ $firstName }}</span>!</h1>
        <p class="text-gray-500 text-sm mt-1">Your account is ready. Tell us what you are here for and we will guide you through it.</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-sm font-bold text-gray-900 mb-4">I am here to&hellip;</h2>
        <div class="flex flex-wrap gap-2 mb-6">
            @foreach($paths as $key => $path)
            <button type="button" onclick="switchType('{{ $key }}', this)"
                class="type-tab px-4 py-1.5 rounded-full text-sm font-semibold border-2 {{ $key === $userType ? 'border-forest-500 text-forest-600 bg-forest-50' : 'border-gray-200 text-gray-500' }} transition-colors">
                {{ $path['label'] }}
            </button>
            @endforeach
        </div>

        @foreach($paths as $key => $path)
        <div class="ob-path {{ $key === $userType ? '' : 'hidden' }}" id="path-{{ $key }}">
            <div class="bg-gradient-to-br from-forest-700 to-forest-900 rounded-xl p-5 text-white mb-5">
                <h3 class="font-bold text-sm mb-1">{{ $path['headline'] }}</h3>
                <p class="text-xs text-gray-300 leading-relaxed">{{ $path['sub'] }} You can revisit this guide anytime at <a href="/welcome" class="text-brand-300 font-semibold">/welcome</a>.</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach($path['steps'] as $i => $step)
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 flex flex-col gap-1.5">
                    <div class="flex gap-2 items-center">
                        <div class="w-6 h-6 rounded-full bg-forest-500 text-white text-xs font-bold flex items-center justify-center shrink-0">{{ $i + 1 }}</div>
                        <div class="text-sm font-semibold text-gray-900">{{ $step['title'] }}</div>
                    </div>
                    <div class="text-xs text-gray-500 leading-relaxed flex-1">{{ $step['desc'] }}</div>
                    <a class="text-xs font-semibold text-forest-600 hover:underline mt-1" href="{{ $step['url'] }}">{{ $step['cta'] }} &rarr;</a>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>

    <div class="text-center">
        <form method="POST" action="/welcome">
            @csrf
            <input type="hidden" name="user_type" id="selected-type" value="{{ $userType }}">
            <button type="submit" class="bg-forest-500 hover:bg-forest-600 text-white font-semibold py-2.5 px-8 rounded-lg text-sm transition-colors">
                Got it &mdash; take me to my dashboard &rarr;
            </button>
        </form>
        <a class="block mt-3 text-xs text-gray-400 hover:text-gray-600" href="/dashboard">Skip for now</a>
    </div>
</div>

<script>
lucide.createIcons();
function switchType(key, btn) {
    document.querySelectorAll('.type-tab').forEach(function(t){
        t.classList.remove('border-forest-500','text-forest-600','bg-forest-50');
        t.classList.add('border-gray-200','text-gray-500');
    });
    document.querySelectorAll('.ob-path').forEach(function(p){p.classList.add('hidden');});
    btn.classList.remove('border-gray-200','text-gray-500');
    btn.classList.add('border-forest-500','text-forest-600','bg-forest-50');
    document.getElementById('path-' + key).classList.remove('hidden');
    document.getElementById('selected-type').value = key;
}
</script>
</body>
</html>
