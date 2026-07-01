<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Export Readiness Results — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:740px;margin:0 auto;padding:1.5rem;}
.result-card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);padding:2rem;border:1px solid var(--border);text-align:center;}
.score-ring{width:120px;height:120px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;font-size:2rem;font-weight:900;border:6px solid;}
.level-badge{display:inline-block;padding:.4rem 1.2rem;border-radius:99px;font-size:.85rem;font-weight:800;margin-bottom:1rem;}
.result-title{font-size:1.3rem;font-weight:900;margin-bottom:.4rem;}
.result-sub{font-size:.88rem;color:var(--muted);margin-bottom:1.5rem;}
.rec-card{background:var(--light-bg);border-radius:var(--radius);padding:1.5rem;margin-top:1.5rem;text-align:left;}
.rec-title{font-weight:800;font-size:.95rem;margin-bottom:.8rem;}
.rec-item{display:flex;gap:.6rem;align-items:flex-start;padding:.5rem 0;border-bottom:1px solid var(--border);font-size:.85rem;color:var(--text);}
.rec-item:last-child{border-bottom:none;}
.rec-num{width:22px;height:22px;border-radius:50%;background:var(--green);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:800;flex-shrink:0;margin-top:1px;}
.meta-grid{display:grid;grid-template-columns:1fr 1fr;gap:.6rem;margin:1rem 0;text-align:left;}
.meta-item{padding:.6rem .8rem;background:var(--light-bg);border-radius:8px;}
.meta-lbl{font-size:.68rem;color:var(--muted);font-weight:600;margin-bottom:2px;}
.meta-val{font-size:.88rem;font-weight:700;color:var(--text);}
</style>

@php
$score  = $assessment->readiness_score ?? 0;
$level  = $assessment->readiness_level ?? 'not_ready';
$recs   = json_decode($assessment->recommendations ?? '[]', true) ?: [];
$answers = json_decode($assessment->answers ?? '{}', true) ?: [];
$levelData = [
    'not_ready'    => ['construction','Not Ready Yet','#dc2626','#fef2f2','You have significant preparation ahead. Focus on the recommendations below first.'],
    'developing'   => ['trending-up','Developing','#d97706','#fffbeb','You have started your export journey. Keep building the foundations.'],
    'almost_ready' => ['zap','Almost Ready','#0284c7','#eff6ff','You\'re close! A few more steps and you\'ll be ready to export.'],
    'ready'        => ['check-circle-2','Export Ready','#16a34a','#f0fdf4','You are well-prepared to export. Review any remaining gaps and begin.'],
    'expert'       => ['trophy','Export Expert','#7c3aed','#fdf4ff','Excellent preparation. You are ready to compete in international markets.'],
];
$ld = $levelData[$level] ?? $levelData['developing'];
@endphp

<div class="page">
    <a href="/export-hub" style="font-size:.82rem;color:var(--muted);display:inline-flex;gap:.3rem;margin-bottom:1rem;">← Export Hub</a>
    <div class="result-card">
        <div class="score-ring" style="border-color:{{ $ld[2] }};background:{{ $ld[3] }};color:{{ $ld[2] }};">{{ $score }}%</div>
        <span class="level-badge" style="background:{{ $ld[3] }};color:{{ $ld[2] }};">{{ $ld[0] }} {{ $ld[1] }}</span>
        <div class="result-title">{{ $company->name }}</div>
        <div style="font-size:.82rem;color:var(--muted);margin-bottom:.5rem;">{{ $assessment->product_name }}{{ $assessment->target_market ? ' → '.$assessment->target_market : '' }}</div>
        <div class="result-sub">{{ $ld[4] }}</div>

        <div class="meta-grid">
            <div class="meta-item"><div class="meta-lbl">Product</div><div class="meta-val">{{ $assessment->product_name }}</div></div>
            @if($assessment->target_market)<div class="meta-item"><div class="meta-lbl">Target Market</div><div class="meta-val">{{ $assessment->target_market }}</div></div>@endif
            @if($assessment->hs_code)<div class="meta-item"><div class="meta-lbl">HS Code</div><div class="meta-val">{{ $assessment->hs_code }}</div></div>@endif
            <div class="meta-item"><div class="meta-lbl">Readiness Score</div><div class="meta-val" style="color:{{ $ld[2] }};">{{ $score }}/100</div></div>
        </div>

        @if(count($recs) > 0)
        <div class="rec-card">
            <div class="rec-title"><i data-lucide="clipboard-list" class="lic"></i> Your Action Plan ({{ count($recs) }} steps)</div>
            @foreach($recs as $i => $rec)
            <div class="rec-item">
                <div class="rec-num">{{ $i+1 }}</div>
                <div>{{ $rec }}</div>
            </div>
            @endforeach
        </div>
        @else
        <div class="rec-card" style="background:#d4edda;">
            <div class="rec-title" style="color:#166534;"><i data-lucide="party-popper" class="lic"></i> Excellent! No critical gaps identified.</div>
            <div style="font-size:.85rem;color:#166534;">You appear to be well-prepared. Consider connecting with an export agent to begin your first shipment.</div>
        </div>
        @endif

        <div style="margin-top:1.5rem;display:flex;gap:.75rem;justify-content:center;flex-wrap:wrap;">
            <a href="/export-hub" style="padding:.55rem 1.2rem;background:var(--green);color:#fff;border-radius:7px;font-weight:700;font-size:.88rem;">Browse Export Guides →</a>
            <a href="/export-hub/assessment" style="padding:.55rem 1.2rem;border:1px solid var(--border);color:var(--text);border-radius:7px;font-weight:700;font-size:.88rem;">Retake Assessment</a>
            <a href="/support" style="padding:.55rem 1.2rem;border:1px solid var(--border);color:var(--text);border-radius:7px;font-weight:700;font-size:.88rem;">Talk to an Expert</a>
        </div>
    </div>
</div>
@include('partials.footer')
</body>
</html>
