<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>ESG & Sustainability — Galerie virtuelle de l'artisanat du Cameroun</title>
<meta name="description" content="Environmental, Social and Governance reporting and sustainability leaderboard for Cameroonian companies.">
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:1100px;margin:0 auto;padding:1.5rem;}
.hero{background:linear-gradient(135deg,#052e16,#14532d);border-radius:var(--radius);padding:2.5rem 2rem;color:#fff;margin-bottom:2rem;position:relative;overflow:hidden;}
.hero::after{content:'';position:absolute;right:-40px;top:-40px;width:250px;height:250px;border-radius:50%;background:rgba(134,239,172,.08);}
.hero-title{font-size:1.8rem;font-weight:900;margin-bottom:.5rem;}
.hero-sub{color:#a7f3d0;font-size:.9rem;max-width:560px;}
.h-stats{display:flex;gap:2rem;margin-top:1.5rem;flex-wrap:wrap;}
.h-stat-val{font-size:1.4rem;font-weight:800;color:#86efac;}
.h-stat-lbl{font-size:.72rem;color:#6ee7b7;}
.pillars{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:2rem;}
.pillar{border-radius:var(--radius);padding:1.4rem;border:1px solid var(--border);}
.pillar-e{background:linear-gradient(135deg,#f0fdf4,#dcfce7);border-color:#bbf7d0;}
.pillar-s{background:linear-gradient(135deg,#eff6ff,#dbeafe);border-color:#bfdbfe;}
.pillar-g{background:linear-gradient(135deg,#fdf4ff,#f3e8ff);border-color:#e9d5ff;}
.pillar-icon{font-size:1.8rem;margin-bottom:.5rem;}
.pillar-title{font-weight:800;font-size:.95rem;margin-bottom:.3rem;}
.pillar-points{display:flex;flex-direction:column;gap:.2rem;margin-top:.5rem;}
.pillar-point{font-size:.78rem;color:var(--muted);}
.section-title{font-size:1.1rem;font-weight:800;color:var(--text);margin:1.5rem 0 1rem;}
.leader-table{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden;border:1px solid var(--border);}
.lt-head{display:grid;grid-template-columns:40px 1fr 80px 80px 80px 100px;gap:.5rem;padding:.6rem 1rem;background:var(--light-bg);border-bottom:1px solid var(--border);font-size:.72rem;font-weight:700;color:var(--muted);text-transform:uppercase;}
.lt-row{display:grid;grid-template-columns:40px 1fr 80px 80px 80px 100px;gap:.5rem;padding:.75rem 1rem;border-bottom:1px solid var(--border);align-items:center;transition:background .15s;}
.lt-row:last-child{border-bottom:none;}
.lt-row:hover{background:var(--light-bg);}
.lt-rank{font-weight:900;font-size:1rem;color:var(--muted);}
.lt-rank-1{color:#f59e0b;}
.lt-rank-2{color:#94a3b8;}
.lt-rank-3{color:#cd7c2f;}
.lt-co{font-weight:700;font-size:.88rem;color:var(--text);}
.lt-co-ver{font-size:.68rem;color:#166534;font-weight:600;}
.score-bar{height:6px;border-radius:3px;margin-top:3px;}
.score-e{background:linear-gradient(90deg,#22c55e,#86efac);}
.score-s{background:linear-gradient(90deg,#3b82f6,#93c5fd);}
.score-g{background:linear-gradient(90deg,#a855f7,#d8b4fe);}
.score-badge{display:inline-block;padding:3px 10px;border-radius:99px;font-size:.75rem;font-weight:800;}
.badge-a{background:#d4edda;color:#166534;}
.badge-b{background:#cce5ff;color:#0056b3;}
.badge-c{background:#fff3cd;color:#856404;}
.badge-d{background:#f8d7da;color:#721c24;}
.cta-banner{background:linear-gradient(135deg,#052e16,#14532d);border-radius:var(--radius);padding:2rem;color:#fff;text-align:center;margin-top:2rem;}
@media(max-width:700px){.pillars{grid-template-columns:1fr;}.lt-head,.lt-row{grid-template-columns:30px 1fr 70px 90px;}.lt-env,.lt-soc,.lt-gov{display:none;}}
</style>

@php
$authUser = session('auth_user');
$reports = DB::table('esg_reports')
    ->join('companies','esg_reports.company_id','=','companies.id')
    ->where('esg_reports.status','published')
    ->whereNull('companies.deleted_at')
    ->select('esg_reports.*','companies.name as co_name','companies.slug as co_slug','companies.verification_status as co_ver')
    ->orderByDesc('overall_esg_score')->get();
$myCompanies = $authUser ? DB::table('company_users')
    ->join('companies','company_users.company_id','=','companies.id')
    ->where('company_users.user_id',$authUser['id'])
    ->where('company_users.status','approved')
    ->whereNull('companies.deleted_at')
    ->select('companies.id','companies.name')->get() : collect();
$hasMyReport = $authUser && $myCompanies->count() > 0 &&
    DB::table('esg_reports')->whereIn('company_id',$myCompanies->pluck('id')->toArray())->exists();
$getGrade = fn($score) => $score >= 80 ? 'A' : ($score >= 65 ? 'B' : ($score >= 50 ? 'C' : 'D'));
@endphp

<div class="page">
    <div class="hero">
        <div style="position:relative;z-index:1;">
            <div style="display:inline-block;background:rgba(134,239,172,.15);border:1px solid rgba(134,239,172,.3);color:#86efac;padding:3px 12px;border-radius:99px;font-size:.72rem;font-weight:700;margin-bottom:.7rem;">ESG & SUSTAINABILITY</div>
            <div class="hero-title">Sustainability Intelligence</div>
            <div class="hero-sub">Track environmental impact, social performance, and governance standards across Cameroonian companies. Build trust through transparency.</div>
            <div class="h-stats">
                <div><div class="h-stat-val">{{ $reports->count() }}</div><div class="h-stat-lbl">ESG Reports Published</div></div>
                <div><div class="h-stat-val">{{ $reports->count() > 0 ? round($reports->avg('overall_esg_score')) : '—' }}</div><div class="h-stat-lbl">Average ESG Score</div></div>
                <div><div class="h-stat-val">{{ $reports->where('overall_esg_score','>=',80)->count() }}</div><div class="h-stat-lbl">Grade A Companies</div></div>
            </div>
        </div>
    </div>

    <div class="pillars">
        <div class="pillar pillar-e">
            <div class="pillar-icon"><i data-lucide="globe" class="lic"></i></div>
            <div class="pillar-title" style="color:#166534;">Environmental</div>
            <div class="pillar-points">
                <div class="pillar-point"><i data-lucide="factory" class="lic"></i> CO₂ emissions tracking</div>
                <div class="pillar-point"><i data-lucide="zap" class="lic"></i> Renewable energy usage</div>
                <div class="pillar-point"><i data-lucide="droplet" class="lic"></i> Water consumption</div>
                <div class="pillar-point"><i data-lucide="recycle" class="lic"></i> Waste & recycling</div>
                <div class="pillar-point"><i data-lucide="trees" class="lic"></i> Green initiatives</div>
            </div>
        </div>
        <div class="pillar pillar-s">
            <div class="pillar-icon"><i data-lucide="users" class="lic"></i></div>
            <div class="pillar-title" style="color:#1d4ed8;">Social</div>
            <div class="pillar-points">
                <div class="pillar-point"><i data-lucide="user" class="lic"></i>‍<i data-lucide="briefcase" class="lic"></i> Gender diversity</div>
                <div class="pillar-point"><i data-lucide="graduation-cap" class="lic"></i> Employee training</div>
                <div class="pillar-point"><i data-lucide="shield" class="lic"></i> Safety record</div>
                <div class="pillar-point"><i data-lucide="heart-pulse" class="lic"></i> Health benefits</div>
                <div class="pillar-point"><i data-lucide="handshake" class="lic"></i> Community impact</div>
            </div>
        </div>
        <div class="pillar pillar-g">
            <div class="pillar-icon"><i data-lucide="landmark" class="lic"></i></div>
            <div class="pillar-title" style="color:#7c3aed;">Governance</div>
            <div class="pillar-points">
                <div class="pillar-point"><i data-lucide="clipboard-list" class="lic"></i> Ethics policy</div>
                <div class="pillar-point"><i data-lucide="bell" class="lic"></i> Whistleblower protection</div>
                <div class="pillar-point"><i data-lucide="user" class="lic"></i>‍<i data-lucide="briefcase" class="lic"></i> Board diversity</div>
                <div class="pillar-point"><i data-lucide="ban" class="lic"></i> Anti-corruption training</div>
                <div class="pillar-point"><i data-lucide="bar-chart-3" class="lic"></i> Transparent reporting</div>
            </div>
        </div>
    </div>

    <div class="section-title"><i data-lucide="trophy" class="lic"></i> ESG Leaderboard</div>
    @if($reports->isEmpty())
        <div style="text-align:center;padding:3rem;color:var(--muted);">No ESG reports published yet. <a href="#" style="color:var(--green);">Be the first →</a></div>
    @else
        <div class="leader-table">
            <div class="lt-head">
                <div>#</div>
                <div>Company</div>
                <div class="lt-env"><i data-lucide="globe" class="lic"></i> Env.</div>
                <div class="lt-soc"><i data-lucide="users" class="lic"></i> Social</div>
                <div class="lt-gov"><i data-lucide="landmark" class="lic"></i> Gov.</div>
                <div>Overall</div>
            </div>
            @foreach($reports as $i => $r)
            @php $grade = $getGrade($r->overall_esg_score??0); @endphp
            <div class="lt-row">
                <div class="lt-rank {{ $i<3?'lt-rank-'.($i+1):'' }}">{{ $i===0?'medal':($i===1?'medal':($i===2?'medal':$i+1)) }}</div>
                <div>
                    <div class="lt-co"><a href="/companies/{{ $r->co_slug }}" style="color:var(--text);">{{ $r->co_name }}</a></div>
                    @if($r->co_ver==='verified')<div class="lt-co-ver"><i data-lucide="check" class="lic"></i> Verified · {{ $r->year }}</div>@else<div style="font-size:.68rem;color:var(--muted);">{{ $r->year }}</div>@endif
                </div>
                <div class="lt-env">
                    <div style="font-size:.82rem;font-weight:700;color:#166534;">{{ $r->env_score??'—' }}<span style="font-size:.65rem;font-weight:400;">/100</span></div>
                    @if($r->env_score)<div class="score-bar score-e" style="width:{{ $r->env_score }}%;"></div>@endif
                </div>
                <div class="lt-soc">
                    <div style="font-size:.82rem;font-weight:700;color:#1d4ed8;">{{ $r->social_score??'—' }}<span style="font-size:.65rem;font-weight:400;">/100</span></div>
                    @if($r->social_score)<div class="score-bar score-s" style="width:{{ $r->social_score }}%;"></div>@endif
                </div>
                <div class="lt-gov">
                    <div style="font-size:.82rem;font-weight:700;color:#7c3aed;">{{ $r->governance_score??'—' }}<span style="font-size:.65rem;font-weight:400;">/100</span></div>
                    @if($r->governance_score)<div class="score-bar score-g" style="width:{{ $r->governance_score }}%;"></div>@endif
                </div>
                <div>
                    <span class="score-badge badge-{{ strtolower($grade) }}">{{ $r->overall_esg_score??'—' }} / Grade {{ $grade }}</span>
                </div>
            </div>
            @endforeach
        </div>
    @endif

    <div class="cta-banner">
        <div style="font-size:1.1rem;font-weight:800;margin-bottom:.5rem;"><i data-lucide="bar-chart-3" class="lic"></i> Submit Your ESG Report</div>
        <div style="font-size:.85rem;color:#a7f3d0;margin-bottom:1rem;">Transparent sustainability reporting builds trust with investors, customers, and partners. Submit your company's ESG data for {{ date('Y') }}.</div>
        @if($authUser && $myCompanies->count() > 0 && !$hasMyReport)
            <a href="/esg/submit" style="display:inline-block;padding:.6rem 1.5rem;background:#22c55e;color:#fff;border-radius:8px;font-weight:700;font-size:.9rem;">Submit ESG Report →</a>
        @elseif($authUser && $hasMyReport)
            <div style="color:#86efac;font-weight:700;"><i data-lucide="check" class="lic"></i> Your company has already submitted an ESG report this year.</div>
        @else
            <a href="/auth/login" style="display:inline-block;padding:.6rem 1.5rem;background:#22c55e;color:#fff;border-radius:8px;font-weight:700;font-size:.9rem;">Sign In to Submit →</a>
        @endif
    </div>
</div>
@include('partials.footer')
</body>
</html>
