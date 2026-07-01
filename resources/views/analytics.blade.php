<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Business Analytics — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:1000px;margin:0 auto;padding:1.5rem;}
.h-title{font-size:1.5rem;font-weight:900;margin-bottom:.2rem;}
.subtitle{font-size:.85rem;color:var(--muted);margin-bottom:1.3rem;}
.stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:.8rem;margin-bottom:1.5rem;}
.stat{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);border:1px solid var(--border);padding:1.1rem 1.2rem;}
.stat-num{font-size:1.7rem;font-weight:900;color:var(--text);line-height:1;}
.stat-lbl{font-size:.74rem;color:var(--muted);font-weight:600;margin-top:.35rem;}
.section-title{font-weight:800;font-size:1.05rem;margin:1.4rem 0 .8rem;}
.card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);border:1px solid var(--border);padding:1.3rem;margin-bottom:1.2rem;}
/* funnel */
.funnel-row{display:flex;align-items:center;gap:.8rem;margin-bottom:.6rem;}
.funnel-label{width:110px;font-size:.82rem;font-weight:600;flex-shrink:0;}
.funnel-track{flex:1;height:24px;background:var(--light-bg);border-radius:6px;overflow:hidden;}
.funnel-fill{height:24px;border-radius:6px;display:flex;align-items:center;padding:0 .6rem;color:#fff;font-size:.75rem;font-weight:800;min-width:1.6rem;transition:width .4s;}
.pair{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:.8rem;}
.pair-card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);border:1px solid var(--border);padding:1rem 1.2rem;display:flex;justify-content:space-between;align-items:center;}
.pair-icon{font-size:1.5rem;}
.pair-main{font-weight:800;font-size:1.3rem;}
.pair-sub{font-size:.74rem;color:var(--muted);}
.jt-row{display:flex;justify-content:space-between;align-items:center;padding:.6rem 0;border-bottom:1px solid var(--border);font-size:.85rem;}
.jt-row:last-child{border-bottom:none;}
.empty{text-align:center;padding:3rem;background:#fff;border-radius:var(--radius);border:1px solid var(--border);}
.grade-badge{display:inline-block;width:34px;height:34px;border-radius:8px;color:#fff;font-weight:900;text-align:center;line-height:34px;}
</style>

@if(!$company)
<div class="page">
    <div class="h-title"><i data-lucide="bar-chart-3" class="lic"></i> Business Analytics</div>
    <div class="empty" style="margin-top:1rem;">
        <div style="font-size:2rem;margin-bottom:.5rem;"><i data-lucide="bar-chart-3" class="lic"></i></div>
        <div style="font-weight:700;margin-bottom:.3rem;">No company to analyse yet</div>
        <div style="font-size:.85rem;color:var(--muted);margin-bottom:1rem;">Claim or create a company to see recruiting and marketplace analytics.</div>
        <a href="/" style="display:inline-block;padding:.6rem 1.3rem;background:var(--green);color:#fff;border-radius:8px;font-weight:700;text-decoration:none;">Find your company →</a>
    </div>
</div>
@else
@php
$gradeColors = ['A'=>'#16a34a','B'=>'#0284c7','C'=>'#d97706','D'=>'#dc2626','E'=>'#6b7280'];
$f = $metrics['funnel'];
$fmax = max(1, max(array_values($f)));
$fcolors = ['submitted'=>'#d97706','shortlisted'=>'#0284c7','interview'=>'#6d28d9','offered'=>'#16a34a','rejected'=>'#dc2626','withdrawn'=>'#9ca3af'];
@endphp
<div class="page">
    <div class="h-title"><i data-lucide="bar-chart-3" class="lic"></i> Business Analytics</div>
    <p class="subtitle">{{ $company->name }} · <a href="/companies/{{ $company->slug }}" style="color:var(--green);">view public profile</a></p>

    <div class="stats">
        <div class="stat"><div class="stat-num">{{ number_format($metrics['profile_views']) }}</div><div class="stat-lbl"><i data-lucide="eye" class="lic"></i> Profile Views</div></div>
        <div class="stat"><div class="stat-num">{{ $metrics['jobs_open'] }}<span style="font-size:.9rem;color:var(--muted);">/{{ $metrics['jobs_total'] }}</span></div><div class="stat-lbl"><i data-lucide="briefcase" class="lic"></i> Open Jobs</div></div>
        <div class="stat"><div class="stat-num">{{ $metrics['applications'] }}</div><div class="stat-lbl"><i data-lucide="inbox" class="lic"></i> Applications</div></div>
        <div class="stat">
            @if($metrics['health'])<div class="stat-num" style="display:flex;align-items:center;gap:.5rem;">{{ $metrics['health']->overall_score }} <span class="grade-badge" style="background:{{ $gradeColors[$metrics['health']->grade]??'#6b7280' }};font-size:.85rem;">{{ $metrics['health']->grade }}</span></div>
            @else<div class="stat-num">—</div>@endif
            <div class="stat-lbl"><i data-lucide="heart" class="lic"></i> Health Score</div>
        </div>
        <div class="stat"><div class="stat-num">{{ $metrics['reviews'] }}{{ $metrics['review_avg'] ? ' · '.number_format($metrics['review_avg'],1).'★' : '' }}</div><div class="stat-lbl"><i data-lucide="star" class="lic"></i> Supplier Reviews</div></div>
    </div>

    <div class="section-title"><i data-lucide="inbox" class="lic"></i> Hiring Funnel</div>
    <div class="card">
        @if($metrics['applications'] === 0)
        <div style="font-size:.85rem;color:var(--muted);text-align:center;padding:1rem;">No applications yet. <a href="/jobs/post" style="color:var(--green);">Post a job</a> to start receiving candidates.</div>
        @else
        @foreach(['submitted'=>'New','shortlisted'=>'Shortlisted','interview'=>'Interview','offered'=>'Offered','rejected'=>'Rejected','withdrawn'=>'Withdrawn'] as $k=>$label)
        <div class="funnel-row">
            <div class="funnel-label">{{ $label }}</div>
            <div class="funnel-track"><div class="funnel-fill" style="width:{{ max(4, round($f[$k]/$fmax*100)) }}%;background:{{ $fcolors[$k] }};">{{ $f[$k] }}</div></div>
        </div>
        @endforeach
        <div style="font-size:.76rem;color:var(--muted);margin-top:.6rem;">Conversion: {{ $metrics['applications'] }} applied → {{ $f['shortlisted']+$f['interview']+$f['offered'] }} progressed → {{ $f['offered'] }} offered.</div>
        @endif
    </div>

    <div class="section-title"><i data-lucide="trending-up" class="lic"></i> Trends</div>
    <div class="pair">
        <div class="card" style="margin-bottom:0;">
            <div style="font-weight:700;font-size:.88rem;margin-bottom:.8rem;">Applications — last 8 weeks</div>
            @php $amax = max(1, max(array_map(fn($p)=>$p['count'],$appTrend))); $atotal = array_sum(array_map(fn($p)=>$p['count'],$appTrend)); @endphp
            <div style="display:flex;align-items:flex-end;gap:.4rem;height:120px;">
                @foreach($appTrend as $p)
                <div style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:flex-end;height:100%;">
                    <div style="font-size:.66rem;color:var(--muted);font-weight:700;">{{ $p['count'] ?: '' }}</div>
                    <div style="width:100%;background:#0284c7;border-radius:4px 4px 0 0;height:{{ $p['count']>0 ? max(4, round($p['count']/$amax*90)) : 2 }}px;{{ $p['count']==0 ? 'background:#e5e7eb;' : '' }}"></div>
                    <div style="font-size:.6rem;color:var(--muted);margin-top:.25rem;white-space:nowrap;">{{ $p['label'] }}</div>
                </div>
                @endforeach
            </div>
            <div style="font-size:.74rem;color:var(--muted);margin-top:.6rem;">{{ $atotal }} application{{ $atotal!=1?'s':'' }} in the last 8 weeks.</div>
        </div>
        <div class="card" style="margin-bottom:0;">
            <div style="font-weight:700;font-size:.88rem;margin-bottom:.8rem;">Jobs posted — last 6 months</div>
            @php $jmax = max(1, max(array_map(fn($p)=>$p['count'],$jobTrend))); @endphp
            <div style="display:flex;align-items:flex-end;gap:.5rem;height:120px;">
                @foreach($jobTrend as $p)
                <div style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:flex-end;height:100%;">
                    <div style="font-size:.66rem;color:var(--muted);font-weight:700;">{{ $p['count'] ?: '' }}</div>
                    <div style="width:100%;background:#16a34a;border-radius:4px 4px 0 0;height:{{ $p['count']>0 ? max(4, round($p['count']/$jmax*90)) : 2 }}px;{{ $p['count']==0 ? 'background:#e5e7eb;' : '' }}"></div>
                    <div style="font-size:.6rem;color:var(--muted);margin-top:.25rem;">{{ $p['label'] }}</div>
                </div>
                @endforeach
            </div>
            <div style="font-size:.74rem;color:var(--muted);margin-top:.6rem;">Hiring activity across the last 6 months.</div>
        </div>
    </div>

    <div class="section-title"><i data-lucide="handshake" class="lic"></i> Marketplace Activity</div>
    <div class="pair">
        <div class="pair-card"><div><div class="pair-main">{{ $metrics['tenders'] }}</div><div class="pair-sub">Tenders posted · {{ $metrics['tender_bids'] }} bids received</div></div><div class="pair-icon"><i data-lucide="hard-hat" class="lic"></i></div></div>
        <div class="pair-card"><div><div class="pair-main">{{ $metrics['assets'] }}</div><div class="pair-sub">Assets listed · {{ $metrics['asset_inq'] }} inquiries</div></div><div class="pair-icon"><i data-lucide="factory" class="lic"></i></div></div>
        <div class="pair-card"><div><div class="pair-main">{{ $metrics['seeks'] }}</div><div class="pair-sub">Investment seeks · {{ $metrics['seek_interest'] }} interested</div></div><div class="pair-icon"><i data-lucide="banknote" class="lic"></i></div></div>
        <div class="pair-card"><div><div class="pair-main">{{ $metrics['events'] }}</div><div class="pair-sub">Events hosted · {{ $metrics['event_regs'] }} registrations</div></div><div class="pair-icon"><i data-lucide="calendar" class="lic"></i></div></div>
    </div>

    @if($topJobs->count() > 0)
    <div class="section-title"><i data-lucide="arrow-up" class="lic"></i> Jobs by Applications</div>
    <div class="card">
        @foreach($topJobs as $j)
        <div class="jt-row">
            <div>
                <a href="/jobs/{{ $j->id }}/applications" style="font-weight:700;color:var(--text);">{{ $j->title_en }}</a>
                <span style="font-size:.72rem;color:{{ $j->status==='open'?'#16a34a':'#9ca3af' }};font-weight:700;"> · {{ ucfirst($j->status) }}</span>
            </div>
            <div style="font-size:.8rem;color:var(--muted);white-space:nowrap;"><i data-lucide="eye" class="lic"></i> {{ number_format($j->view_count) }} · <i data-lucide="inbox" class="lic"></i> {{ $j->apps }}</div>
        </div>
        @endforeach
    </div>
    @endif

    <div style="display:flex;gap:.6rem;flex-wrap:wrap;margin-top:.5rem;">
        <a href="/recruiter" style="padding:.55rem 1.2rem;background:var(--green);color:#fff;border-radius:8px;font-weight:700;font-size:.85rem;text-decoration:none;"><i data-lucide="user" class="lic"></i>‍<i data-lucide="briefcase" class="lic"></i> Recruiter Dashboard</a>
        <a href="/health-score/{{ $company->slug }}" style="padding:.55rem 1.2rem;border:1px solid var(--border);color:var(--text);border-radius:8px;font-weight:700;font-size:.85rem;text-decoration:none;"><i data-lucide="heart" class="lic"></i> Health Breakdown</a>
    </div>
</div>
@endif
@include('partials.footer')
</body>
</html>
