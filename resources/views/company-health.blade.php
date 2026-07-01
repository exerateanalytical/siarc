<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>{{ $company->name }} — Health Score — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:900px;margin:0 auto;padding:1.5rem;}
.back{font-size:.82rem;color:var(--muted);display:inline-flex;gap:.3rem;margin-bottom:1rem;}
.score-hero{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);border:1px solid var(--border);padding:2rem;display:flex;align-items:center;gap:2rem;margin-bottom:1.5rem;flex-wrap:wrap;}
.ring{width:140px;height:140px;border-radius:50%;display:flex;flex-direction:column;align-items:center;justify-content:center;flex-shrink:0;border:8px solid;}
.ring-num{font-size:2.4rem;font-weight:900;line-height:1;}
.ring-lbl{font-size:.7rem;color:var(--muted);font-weight:600;margin-top:2px;}
.hero-info{flex:1;min-width:240px;}
.hero-co{font-size:1.4rem;font-weight:900;margin-bottom:.2rem;}
.grade-chip{display:inline-block;padding:.3rem 1rem;border-radius:99px;font-size:.9rem;font-weight:800;color:#fff;margin:.4rem 0;}
.hero-verdict{font-size:.88rem;color:var(--muted);line-height:1.6;}
.pillars-detail{display:grid;gap:.9rem;margin-bottom:1.5rem;}
.pillar-card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);border:1px solid var(--border);padding:1.1rem 1.3rem;}
.pc-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:.5rem;}
.pc-name{font-weight:800;font-size:.92rem;}
.pc-score{font-weight:900;font-size:1.1rem;}
.pc-bar{height:10px;border-radius:5px;background:var(--light-bg);overflow:hidden;}
.pc-fill{height:10px;border-radius:5px;transition:width .4s;}
.pc-desc{font-size:.78rem;color:var(--muted);margin-top:.5rem;}
.signals{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:.7rem;}
.signal{background:#fff;border-radius:10px;border:1px solid var(--border);padding:.7rem .9rem;text-align:center;}
.signal-num{font-size:1.3rem;font-weight:900;color:var(--text);}
.signal-lbl{font-size:.7rem;color:var(--muted);font-weight:600;margin-top:1px;}
.section-title{font-weight:800;font-size:1rem;color:var(--text);margin:1.2rem 0 .8rem;}
</style>

@php
$gradeColors = ['A'=>'#16a34a','B'=>'#0284c7','C'=>'#d97706','D'=>'#dc2626','E'=>'#6b7280'];
$gc = $gradeColors[$s['grade']]??'#6b7280';
$gradeVerdict = [
    'A'=>'Outstanding collaborator — highly active, well-connected, and trusted across the ecosystem.',
    'B'=>'Strong collaborator with healthy activity and reputation. A few pillars have room to grow.',
    'C'=>'Solid foundation. Increasing activity and gathering reviews would lift this score notably.',
    'D'=>'Emerging presence. More collaborations, listings, and verified credentials will help.',
    'E'=>'Just getting started. Engage with tenders, federations, and partners to build momentum.',
];
$pillars = [
    ['network','globe','Network','#6d28d9','Collaborations, federation memberships, and partnership requests'],
    ['activity','zap','Activity','#0284c7','Tenders, opportunities, innovation projects, events, and listings posted'],
    ['reputation','star','Reputation','#d97706','Supplier review ratings, reputation points, and verification status'],
    ['sustainability','sprout','Sustainability','#16a34a','Published ESG score and regulatory compliance tracking'],
    ['engagement','trending-up','Engagement','#be185d','How recently the company has been active on the platform'],
];
$sig = $s['signals'];
@endphp

<div class="page">
    <a class="back" href="/health-score">← Health Leaderboard</a>

    <div class="score-hero">
        <div class="ring" style="border-color:{{ $gc }};">
            <div class="ring-num" style="color:{{ $gc }};">{{ $s['overall'] }}</div>
            <div class="ring-lbl">/ 100</div>
        </div>
        <div class="hero-info">
            <div class="hero-co">{{ $company->name }}</div>
            <span class="grade-chip" style="background:{{ $gc }};">Grade {{ $s['grade'] }}</span>
            <div class="hero-verdict">{{ $gradeVerdict[$s['grade']]??'' }}</div>
            <a href="/companies/{{ $company->slug }}" style="display:inline-block;margin-top:.6rem;font-size:.82rem;color:var(--green);font-weight:600;">View company profile →</a>
        </div>
    </div>

    <div class="section-title">Pillar Breakdown</div>
    <div class="pillars-detail">
        @foreach($pillars as [$key,$icon,$name,$color,$desc])
        <div class="pillar-card">
            <div class="pc-head">
                <div class="pc-name">{{ $icon }} {{ $name }}</div>
                <div class="pc-score" style="color:{{ $color }};">{{ $s[$key] }}<span style="font-size:.7rem;color:var(--muted);font-weight:600;">/100</span></div>
            </div>
            <div class="pc-bar"><div class="pc-fill" style="width:{{ $s[$key] }}%;background:{{ $color }};"></div></div>
            <div class="pc-desc">{{ $desc }}</div>
        </div>
        @endforeach
    </div>

    <div class="section-title">Activity Signals</div>
    <div class="signals">
        <div class="signal"><div class="signal-num">{{ $sig['collabs'] }}</div><div class="signal-lbl">Collaborations</div></div>
        <div class="signal"><div class="signal-num">{{ $sig['feds'] }}</div><div class="signal-lbl">Federations</div></div>
        <div class="signal"><div class="signal-num">{{ $sig['requests'] }}</div><div class="signal-lbl">Partnerships</div></div>
        <div class="signal"><div class="signal-num">{{ $sig['tenders'] }}</div><div class="signal-lbl">Tenders Posted</div></div>
        <div class="signal"><div class="signal-num">{{ $sig['bids'] }}</div><div class="signal-lbl">Bids Made</div></div>
        <div class="signal"><div class="signal-num">{{ $sig['opportunities'] }}</div><div class="signal-lbl">Opportunities</div></div>
        <div class="signal"><div class="signal-num">{{ $sig['innovation'] }}</div><div class="signal-lbl">Innovation</div></div>
        <div class="signal"><div class="signal-num">{{ $sig['reviews'] }}</div><div class="signal-lbl">Reviews{{ $sig['avg_review'] ? ' ('.$sig['avg_review'].'★)' : '' }}</div></div>
        <div class="signal"><div class="signal-num">{{ $sig['reputation_points'] }}</div><div class="signal-lbl">Rep. Points</div></div>
        <div class="signal"><div class="signal-num">{{ $sig['esg'] !== null ? $sig['esg'] : '—' }}</div><div class="signal-lbl">ESG Score</div></div>
        <div class="signal"><div class="signal-num">{{ $sig['compliance_total'] > 0 ? $sig['compliance_ok'].'/'.$sig['compliance_total'] : '—' }}</div><div class="signal-lbl">Compliance</div></div>
        <div class="signal"><div class="signal-num">{{ $sig['verified'] ? 'check' : 'x' }}</div><div class="signal-lbl">Verified</div></div>
    </div>

    <div style="font-size:.76rem;color:var(--muted);margin-top:1rem;text-align:center;">Computed live from platform activity{{ isset($computedAt) && $computedAt ? ' · last updated '.$computedAt : '' }}</div>
</div>
@include('partials.footer')
</body>
</html>
