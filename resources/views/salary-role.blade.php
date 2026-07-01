<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>{{ $title }} Salary — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:820px;margin:0 auto;padding:1.5rem;}
.back{font-size:.82rem;color:var(--muted);display:inline-flex;gap:.3rem;margin-bottom:1rem;}
.hero-card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);border:1px solid var(--border);padding:1.6rem;margin-bottom:1.2rem;}
.role-title{font-size:1.5rem;font-weight:900;}
.role-sub{font-size:.85rem;color:var(--muted);margin-top:.2rem;}
.big-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:.8rem;margin-top:1.2rem;text-align:center;}
.big-stat{padding:.9rem;background:var(--light-bg);border-radius:10px;}
.big-num{font-size:1.4rem;font-weight:900;color:#0d9488;}
.big-lbl{font-size:.72rem;color:var(--muted);font-weight:600;margin-top:2px;}
.card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);border:1px solid var(--border);margin-bottom:1.2rem;}
.card-title{padding:.8rem 1.2rem;font-weight:700;font-size:.92rem;border-bottom:1px solid var(--border);}
.card-body{padding:1.2rem;}
.dist-row{display:flex;align-items:center;gap:.7rem;margin-bottom:.5rem;font-size:.8rem;}
.dist-label{width:90px;color:var(--muted);flex-shrink:0;}
.dist-bar{flex:1;height:18px;background:var(--light-bg);border-radius:5px;overflow:hidden;}
.dist-fill{height:18px;background:linear-gradient(90deg,#5eead4,#0d9488);border-radius:5px;}
.dist-val{width:70px;text-align:right;font-weight:700;flex-shrink:0;}
.rep-row{display:flex;justify-content:space-between;align-items:center;padding:.5rem 0;border-bottom:1px solid var(--border);font-size:.83rem;}
.rep-row:last-child{border-bottom:none;}
.exp-badge{font-size:.68rem;padding:1px 8px;border-radius:99px;background:var(--light-bg);border:1px solid var(--border);color:var(--muted);}
</style>

@php
$sectorLabels = ['ict'=>'ICT','finance'=>'Finance','agriculture'=>'Agriculture','construction'=>'Construction','health'=>'Health','retail'=>'Retail','transport'=>'Transport','energy'=>'Energy','education'=>'Education','government'=>'Government','general'=>'General'];
$expLabels = ['entry'=>'Entry','junior'=>'Junior','mid'=>'Mid','senior'=>'Senior','lead'=>'Lead','executive'=>'Executive'];
$mo = fn($annual) => number_format($annual/12);
@endphp

<div class="page">
    <a class="back" href="/salaries">← Salary Insights</a>

    <div class="hero-card">
        <div class="role-title">{{ $title }}</div>
        <div class="role-sub">{{ $sectorLabels[$sector]??ucfirst($sector) }} · based on {{ $stats->reports }} anonymous report{{ $stats->reports!=1?'s':'' }}</div>
        <div class="big-stats">
            <div class="big-stat"><div class="big-num">{{ $mo($stats->min_a) }}</div><div class="big-lbl">Low (XAF/mo)</div></div>
            <div class="big-stat" style="background:#ccfbf1;"><div class="big-num">{{ $mo($stats->avg_a) }}</div><div class="big-lbl">Average (XAF/mo)</div></div>
            <div class="big-stat"><div class="big-num">{{ $mo($stats->max_a) }}</div><div class="big-lbl">High (XAF/mo)</div></div>
        </div>
        <div style="font-size:.76rem;color:var(--muted);margin-top:.8rem;text-align:center;">Annual average ≈ {{ number_format($stats->avg_a) }} XAF{{ $stats->avg_bonus ? ' + ~'.number_format($stats->avg_bonus).' bonus' : '' }}</div>
    </div>

    @if($byExperience->count() > 0)
    <div class="card">
        <div class="card-title"><i data-lucide="bar-chart-3" class="lic"></i> By Experience Level (avg monthly)</div>
        <div class="card-body">
            @php $maxExp = max(1, $byExperience->max('avg_a')); @endphp
            @foreach($byExperience as $e)
            <div class="dist-row">
                <div class="dist-label">{{ $expLabels[$e->experience_level]??ucfirst($e->experience_level) }}</div>
                <div class="dist-bar"><div class="dist-fill" style="width:{{ max(4,$e->avg_a/$maxExp*100) }}%;"></div></div>
                <div class="dist-val">{{ $mo($e->avg_a) }}</div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="card">
        <div class="card-title">Recent Reports</div>
        <div class="card-body">
            @foreach($reports as $rep)
            <div class="rep-row">
                <div>
                    <span style="font-weight:700;">{{ $mo($rep->annual_amount) }} XAF/mo</span>
                    <span class="exp-badge">{{ $expLabels[$rep->experience_level]??ucfirst($rep->experience_level) }}</span>
                    @if($rep->city)<span style="color:var(--muted);font-size:.76rem;"> · {{ $rep->city }}</span>@endif
                </div>
                <span style="font-size:.74rem;color:var(--muted);">{{ $rep->years_experience ? $rep->years_experience.' yrs' : '' }} · {{ date('M Y', strtotime($rep->created_at)) }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>
@include('partials.footer')
</body>
</html>
