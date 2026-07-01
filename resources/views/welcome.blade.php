<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Welcome — Galerie virtuelle de l'artisanat du Cameroun</title></head>
<body>
@include('partials.nav')
<style>
.ob-wrap{max-width:800px;margin:3rem auto;padding:0 1.5rem 4rem;}
.ob-header{text-align:center;margin-bottom:2.5rem;}
.ob-header h1{font-size:1.6rem;font-weight:900;color:var(--text);margin-bottom:.4rem;}
.ob-header p{font-size:.93rem;color:var(--muted);}
.ob-name{color:var(--green);}
.type-section{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);padding:1.5rem;margin-bottom:1.5rem;}
.type-section h2{font-size:1rem;font-weight:800;margin-bottom:1rem;}
.type-tabs{display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1.5rem;}
.type-tab{padding:.45rem 1.1rem;border-radius:99px;font-size:.82rem;font-weight:700;border:2px solid var(--border);cursor:pointer;color:var(--muted);background:#fff;transition:.15s;}
.type-tab:hover{border-color:var(--green);color:var(--green);}
.type-tab.active{border-color:var(--green);color:var(--green);background:#e8f5e9;}
.ob-path{display:none;}
.ob-path.active{display:block;}
.highlight-box{background:linear-gradient(135deg,var(--dark),var(--mid));border-radius:var(--radius);padding:1.2rem 1.4rem;color:#fff;margin-bottom:1.2rem;}
.highlight-box h3{font-size:.95rem;font-weight:800;margin-bottom:.3rem;}
.highlight-box p{font-size:.8rem;color:#aab;line-height:1.5;}
.highlight-box a{color:var(--yellow);font-weight:600;}
.steps-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1rem;margin-bottom:.5rem;}
.step-card{background:var(--light-bg);border-radius:var(--radius);padding:1.1rem;display:flex;flex-direction:column;gap:.4rem;border:1px solid var(--border);}
.step-num{width:26px;height:26px;border-radius:50%;background:var(--green);color:#fff;font-size:.72rem;font-weight:800;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.step-title{font-size:.86rem;font-weight:700;}
.step-desc{font-size:.77rem;color:var(--muted);line-height:1.5;flex:1;}
.step-action{display:inline-block;margin-top:.3rem;font-size:.79rem;font-weight:700;color:var(--green);}
.step-action:hover{text-decoration:underline;}
.dismiss-form{text-align:center;margin-top:2rem;}
.dismiss-btn{padding:.7rem 2.2rem;background:var(--green);color:#fff;border:none;border-radius:9px;font-weight:700;font-size:.92rem;cursor:pointer;}
.dismiss-btn:hover{background:#00962e;}
.skip-link{display:block;margin-top:.8rem;font-size:.8rem;color:var(--muted);}
.skip-link:hover{color:var(--text);}
</style>

@php
    $authUser = session('auth_user');
    $userType = $authUser['user_type'] ?? 'investor';
    $firstName = $authUser['first_name'] ?? 'there';
    $paths = [
        'investor' => [
            'label'    => 'Investor',
            'icon'     => 'Investor',
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
            'icon'     => 'Job Seeker',
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
            'icon'     => 'Company Owner',
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
            'icon'     => 'Developer',
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

<div class="ob-wrap">
    <div class="ob-header">
        <h1>Welcome, <span class="ob-name">{{ $firstName }}</span>!</h1>
        <p>Your account is ready. Tell us what you are here for and we will guide you through it.</p>
    </div>

    <div class="type-section">
        <h2>I am here to&hellip;</h2>
        <div class="type-tabs">
            @foreach($paths as $key => $path)
            <button class="type-tab {{ $key === $userType ? 'active' : '' }}" onclick="switchType('{{ $key }}', this)">{{ $path['label'] }}</button>
            @endforeach
        </div>

        @foreach($paths as $key => $path)
        <div class="ob-path {{ $key === $userType ? 'active' : '' }}" id="path-{{ $key }}">
            <div class="highlight-box">
                <h3>{{ $path['headline'] }}</h3>
                <p>{{ $path['sub'] }} You can revisit this guide anytime at <a href="/welcome">/welcome</a>.</p>
            </div>
            <div class="steps-grid">
                @foreach($path['steps'] as $i => $step)
                <div class="step-card">
                    <div style="display:flex;gap:.6rem;align-items:center;">
                        <div class="step-num">{{ $i + 1 }}</div>
                        <div class="step-title">{{ $step['title'] }}</div>
                    </div>
                    <div class="step-desc">{{ $step['desc'] }}</div>
                    <a class="step-action" href="{{ $step['url'] }}">{{ $step['cta'] }} &rarr;</a>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>

    <div class="dismiss-form">
        <form method="POST" action="/welcome">
            @csrf
            <input type="hidden" name="user_type" id="selected-type" value="{{ $userType }}">
            <button type="submit" class="dismiss-btn">Got it &mdash; take me to my dashboard &rarr;</button>
        </form>
        <a class="skip-link" href="/dashboard">Skip for now</a>
    </div>
</div>

<script>
function switchType(key, btn) {
    document.querySelectorAll('.type-tab').forEach(function(t){t.classList.remove('active');});
    document.querySelectorAll('.ob-path').forEach(function(p){p.classList.remove('active');});
    btn.classList.add('active');
    document.getElementById('path-' + key).classList.add('active');
    document.getElementById('selected-type').value = key;
}
</script>
@include('partials.footer')
</body>
</html>
