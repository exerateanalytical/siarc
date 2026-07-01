<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Collaboration Health Score — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:1000px;margin:0 auto;padding:1.5rem;}
.hero{background:linear-gradient(135deg,#064e3b,#047857);border-radius:var(--radius);padding:2.5rem 2rem;margin-bottom:1.5rem;color:#fff;}
.hero-title{font-size:1.8rem;font-weight:900;margin-bottom:.4rem;}
.hero-sub{font-size:.9rem;color:#a7f3d0;margin-bottom:1.2rem;}
.btn-white{padding:.55rem 1.3rem;background:#fff;color:#064e3b;border-radius:8px;font-weight:700;font-size:.88rem;border:none;cursor:pointer;text-decoration:none;display:inline-block;}
.pillars{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:.8rem;margin-bottom:1.5rem;}
.pillar{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);border:1px solid var(--border);padding:1rem;border-top:3px solid;}
.pillar-icon{font-size:1.4rem;margin-bottom:.3rem;}
.pillar-name{font-weight:800;font-size:.85rem;color:var(--text);}
.pillar-weight{font-size:.7rem;color:var(--muted);font-weight:600;}
.pillar-desc{font-size:.76rem;color:var(--muted);line-height:1.4;margin-top:.35rem;}
.section-title{font-weight:800;font-size:1rem;color:var(--text);margin:1.2rem 0 .8rem;}
.board{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);border:1px solid var(--border);overflow:hidden;}
.board-row{display:flex;align-items:center;gap:.85rem;padding:.85rem 1.2rem;border-bottom:1px solid var(--border);text-decoration:none;color:var(--text);transition:background .12s;}
.board-row:last-child{border-bottom:none;}
.board-row:hover{background:var(--light-bg);}
.rank{width:28px;font-weight:800;color:var(--muted);font-size:.9rem;text-align:center;}
.co-avatar{width:38px;height:38px;border-radius:9px;background:linear-gradient(135deg,#064e3b,#047857);display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:800;color:#fff;flex-shrink:0;}
.co-name{font-weight:700;font-size:.9rem;}
.co-bars{display:flex;gap:3px;margin-top:.3rem;}
.mini-bar{height:5px;width:26px;border-radius:3px;background:var(--border);overflow:hidden;}
.mini-fill{height:5px;border-radius:3px;}
.grade-badge{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;font-weight:900;color:#fff;flex-shrink:0;}
.overall-num{font-weight:900;font-size:1.1rem;width:42px;text-align:right;}
</style>

@php
$authUser = webUser();
$scores = DB::table('company_health_scores')
    ->join('companies','company_health_scores.company_id','=','companies.id')
    ->whereNull('companies.deleted_at')
    ->select('company_health_scores.*','companies.name','companies.slug','companies.verification_status')
    ->orderByDesc('overall_score')->limit(30)->get();

// the logged-in user's own company score shortcut
$myScore = null;
if ($authUser) {
    $myCo = DB::table('company_users')
        ->join('companies','company_users.company_id','=','companies.id')
        ->where('company_users.user_id',$authUser->id)->where('company_users.is_active',1)
        ->whereNull('companies.deleted_at')->select('companies.slug')->first();
    if ($myCo) $myScore = $myCo->slug;
}

$gradeColors = ['A'=>'#16a34a','B'=>'#0284c7','C'=>'#d97706','D'=>'#dc2626','E'=>'#6b7280'];
$pillarColors = ['network'=>'#6d28d9','activity'=>'#0284c7','reputation'=>'#d97706','sustainability'=>'#16a34a','engagement'=>'#be185d'];
@endphp

<div class="page">
    <div class="hero">
        <div class="hero-title"><i data-lucide="heart" class="lic"></i> Collaboration Health Score</div>
        <div class="hero-sub">A unified measure of how actively and reliably a company collaborates across the Galerie virtuelle de l'artisanat du Cameroun ecosystem</div>
        @if($myScore)
        <a class="btn-white" href="/health-score/{{ $myScore }}">View My Company's Score →</a>
        @elseif(!$authUser)
        <a class="btn-white" href="/auth/login">Sign In</a>
        @endif
    </div>

    <div class="pillars">
        <div class="pillar" style="border-top-color:#6d28d9;"><div class="pillar-icon"><i data-lucide="globe" class="lic"></i></div><div class="pillar-name">Network</div><div class="pillar-weight">20% weight</div><div class="pillar-desc">Collaborations, federations & partnership requests</div></div>
        <div class="pillar" style="border-top-color:#0284c7;"><div class="pillar-icon"><i data-lucide="zap" class="lic"></i></div><div class="pillar-name">Activity</div><div class="pillar-weight">25% weight</div><div class="pillar-desc">Tenders, opportunities, innovation, events & listings</div></div>
        <div class="pillar" style="border-top-color:#d97706;"><div class="pillar-icon"><i data-lucide="star" class="lic"></i></div><div class="pillar-name">Reputation</div><div class="pillar-weight">25% weight</div><div class="pillar-desc">Supplier reviews, reputation points & verification</div></div>
        <div class="pillar" style="border-top-color:#16a34a;"><div class="pillar-icon"><i data-lucide="sprout" class="lic"></i></div><div class="pillar-name">Sustainability</div><div class="pillar-weight">15% weight</div><div class="pillar-desc">ESG score & regulatory compliance status</div></div>
        <div class="pillar" style="border-top-color:#be185d;"><div class="pillar-icon"><i data-lucide="trending-up" class="lic"></i></div><div class="pillar-name">Engagement</div><div class="pillar-weight">15% weight</div><div class="pillar-desc">Recency of platform activity</div></div>
    </div>

    <div class="section-title"><i data-lucide="trophy" class="lic"></i> Health Leaderboard</div>
    <div class="board">
        @foreach($scores as $i => $s)
        <a href="/health-score/{{ $s->slug }}" class="board-row">
            <div class="rank">{{ $i+1 }}</div>
            <div class="co-avatar">{{ strtoupper(substr($s->name,0,2)) }}</div>
            <div style="flex:1;min-width:0;">
                <div class="co-name">{{ $s->name }}{{ $s->verification_status==='verified' ? ' <i data-lucide="check" class="lic"></i>' : '' }}</div>
                <div class="co-bars">
                    <div class="mini-bar" title="Network"><div class="mini-fill" style="width:{{ $s->network_score }}%;background:#6d28d9;"></div></div>
                    <div class="mini-bar" title="Activity"><div class="mini-fill" style="width:{{ $s->activity_score }}%;background:#0284c7;"></div></div>
                    <div class="mini-bar" title="Reputation"><div class="mini-fill" style="width:{{ $s->reputation_score }}%;background:#d97706;"></div></div>
                    <div class="mini-bar" title="Sustainability"><div class="mini-fill" style="width:{{ $s->sustainability_score }}%;background:#16a34a;"></div></div>
                    <div class="mini-bar" title="Engagement"><div class="mini-fill" style="width:{{ $s->engagement_score }}%;background:#be185d;"></div></div>
                </div>
            </div>
            <div class="overall-num" style="color:{{ $gradeColors[$s->grade]??'#6b7280' }};">{{ $s->overall_score }}</div>
            <div class="grade-badge" style="background:{{ $gradeColors[$s->grade]??'#6b7280' }};">{{ $s->grade }}</div>
        </a>
        @endforeach
    </div>
    <div style="font-size:.76rem;color:var(--muted);margin-top:.8rem;text-align:center;">Scores recompute from live platform activity. Grades: A (85+) · B (70+) · C (55+) · D (40+) · E (&lt;40)</div>
</div>
@include('partials.footer')
</body>
</html>
