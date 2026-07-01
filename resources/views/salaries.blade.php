<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Salary Insights — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:1000px;margin:0 auto;padding:1.5rem;}
.hero{background:linear-gradient(135deg,#134e4a,#0d9488);border-radius:var(--radius);padding:2.5rem 2rem;margin-bottom:1.5rem;color:#fff;}
.hero-title{font-size:1.8rem;font-weight:900;margin-bottom:.4rem;}
.hero-sub{font-size:.9rem;color:#99f6e4;margin-bottom:1.2rem;}
.btn-white{padding:.55rem 1.3rem;background:#fff;color:#134e4a;border-radius:8px;font-weight:700;font-size:.88rem;border:none;cursor:pointer;}
.btn-outline{padding:.55rem 1.3rem;border:1px solid rgba(255,255,255,.3);color:#fff;border-radius:8px;font-weight:600;font-size:.88rem;cursor:pointer;background:none;text-decoration:none;}
.stats-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:.8rem;margin-bottom:1.5rem;}
.stat-card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);border:1px solid var(--border);padding:1rem 1.2rem;}
.stat-num{font-size:1.5rem;font-weight:900;color:var(--text);}
.stat-lbl{font-size:.74rem;color:var(--muted);font-weight:600;margin-top:2px;}
.cat-tabs{display:flex;gap:.4rem;flex-wrap:wrap;margin-bottom:1rem;}
.cat-tab{padding:.3rem .9rem;border-radius:99px;font-size:.78rem;font-weight:600;cursor:pointer;border:1px solid var(--border);background:#fff;color:var(--muted);}
.cat-tab.active,.cat-tab:hover{background:#0d9488;color:#fff;border-color:#0d9488;}
.role-card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);border:1px solid var(--border);padding:1.1rem 1.3rem;margin-bottom:.8rem;}
.role-head{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem;margin-bottom:.6rem;}
.role-title{font-weight:800;font-size:1rem;color:var(--text);}
.role-meta{font-size:.74rem;color:var(--muted);}
.role-avg{font-weight:900;font-size:1.1rem;color:#0d9488;}
.bar-track{height:8px;border-radius:4px;background:var(--light-bg);position:relative;margin:.5rem 0;}
.bar-range{position:absolute;height:8px;border-radius:4px;background:linear-gradient(90deg,#5eead4,#0d9488);}
.bar-labels{display:flex;justify-content:space-between;font-size:.73rem;color:var(--muted);}
/* modal */
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:200;align-items:center;justify-content:center;}
.modal-overlay.open{display:flex;}
.modal{background:#fff;border-radius:var(--radius);padding:1.5rem;width:min(540px,95vw);max-height:90vh;overflow-y:auto;}
.form-group{margin-bottom:.8rem;}
.form-label{display:block;font-size:.8rem;font-weight:600;margin-bottom:.3rem;}
.form-control{width:100%;padding:.45rem .75rem;border:1px solid var(--border);border-radius:7px;font-size:.85rem;outline:none;font-family:inherit;box-sizing:border-box;}
.form-control:focus{border-color:#0d9488;}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:.6rem;}
@media(max-width:600px){.form-row{grid-template-columns:1fr;}}
</style>

@php
$authUser = webUser();
$sector = request('sector','');
$q = request('q','');
$base = DB::table('salary_reports')->where('status','published');
if ($sector) $base->where('sector',$sector);
if ($q) $base->where('job_title','like',"%$q%");
$roles = (clone $base)
    ->select('job_slug','job_title','sector',
        DB::raw('COUNT(*) as reports'),
        DB::raw('MIN(annual_amount) as min_a'),
        DB::raw('AVG(annual_amount) as avg_a'),
        DB::raw('MAX(annual_amount) as max_a'))
    ->groupBy('job_slug','job_title','sector')
    ->orderByDesc('avg_a')->get();
$totalReports = DB::table('salary_reports')->where('status','published')->count();
$totalRoles = DB::table('salary_reports')->where('status','published')->distinct('job_slug')->count('job_slug');
$overallAvg = DB::table('salary_reports')->where('status','published')->avg('annual_amount');
$globalMax = max(1, $roles->max('max_a') ?: 1);

$sectorLabels = ['ict'=>'ICT','finance'=>'Finance','agriculture'=>'Agriculture','construction'=>'Construction','health'=>'Health','retail'=>'Retail','transport'=>'Transport','energy'=>'Energy','education'=>'Education','government'=>'Government','general'=>'General'];
$fmt = fn($annual) => number_format($annual/12/1000).'K';
@endphp

<div class="page">
    <div class="hero">
        <div class="hero-title"><i data-lucide="banknote" class="lic"></i> Salary Insights</div>
        <div class="hero-sub">Anonymous, crowd-sourced salary benchmarks for the Cameroonian job market — know your worth</div>
        @if($authUser)
        <button class="btn-white" onclick="document.getElementById('addModal').classList.add('open')">+ Contribute Your Salary</button>
        @else
        <a href="/auth/login" class="btn-outline">Sign In to Contribute</a>
        @endif
    </div>

    <div class="stats-row">
        <div class="stat-card"><div class="stat-num">{{ $totalRoles }}</div><div class="stat-lbl">Roles Tracked</div></div>
        <div class="stat-card"><div class="stat-num">{{ number_format($totalReports) }}</div><div class="stat-lbl">Salary Reports</div></div>
        <div class="stat-card"><div class="stat-num">{{ number_format(($overallAvg??0)/12/1000) }}K</div><div class="stat-lbl">Avg Monthly (XAF)</div></div>
    </div>

    <div class="cat-tabs">
        <a class="cat-tab {{ !$sector?'active':'' }}" href="/salaries">All Sectors</a>
        @foreach($sectorLabels as $k=>$v)<a class="cat-tab {{ $sector===$k?'active':'' }}" href="/salaries?sector={{ $k }}">{{ $v }}</a>@endforeach
    </div>

    <form method="GET" action="/salaries" style="display:flex;gap:.5rem;margin-bottom:1.2rem;">
        <input type="text" name="q" value="{{ $q }}" placeholder="Search a job title…" style="flex:1;padding:.45rem .8rem;border:1px solid var(--border);border-radius:7px;font-size:.85rem;outline:none;">
        @if($sector)<input type="hidden" name="sector" value="{{ $sector }}">@endif
        <button type="submit" style="padding:.45rem 1.1rem;background:#0d9488;color:#fff;border:none;border-radius:7px;font-size:.83rem;font-weight:600;cursor:pointer;">Search</button>
    </form>

    @if($roles->isEmpty())
    <div style="text-align:center;padding:3rem;background:#fff;border-radius:var(--radius);border:1px solid var(--border);">
        <div style="font-size:2rem;margin-bottom:.5rem;"><i data-lucide="banknote" class="lic"></i></div>
        <div style="font-weight:700;">No salary data yet</div>
        <div style="font-size:.85rem;color:var(--muted);">Be the first to contribute for this filter.</div>
    </div>
    @else
    <div style="font-size:.76rem;color:var(--muted);margin-bottom:.6rem;">Ranges shown as monthly gross (XAF). Click a role for detail.</div>
    @foreach($roles as $r)
    @php
    $leftPct = $globalMax > 0 ? ($r->min_a/$globalMax*100) : 0;
    $widthPct = $globalMax > 0 ? (($r->max_a-$r->min_a)/$globalMax*100) : 0;
    @endphp
    <a href="/salaries/{{ $r->job_slug }}" class="role-card" style="display:block;">
        <div class="role-head">
            <div>
                <div class="role-title">{{ $r->job_title }}</div>
                <div class="role-meta">{{ $sectorLabels[$r->sector]??ucfirst($r->sector) }} · {{ $r->reports }} report{{ $r->reports!=1?'s':'' }}</div>
            </div>
            <div style="text-align:right;">
                <div class="role-avg">{{ $fmt($r->avg_a) }} <span style="font-size:.7rem;color:var(--muted);font-weight:600;">avg/mo</span></div>
            </div>
        </div>
        <div class="bar-track"><div class="bar-range" style="left:{{ $leftPct }}%;width:{{ max(3,$widthPct) }}%;"></div></div>
        <div class="bar-labels"><span>{{ $fmt($r->min_a) }}/mo</span><span>{{ $fmt($r->max_a) }}/mo</span></div>
    </a>
    @endforeach
    @endif
</div>

@if($authUser)
<div class="modal-overlay" id="addModal">
    <div class="modal">
        <div style="font-weight:800;font-size:1rem;margin-bottom:.4rem;"><i data-lucide="banknote" class="lic"></i> Contribute Your Salary</div>
        <div style="font-size:.78rem;color:var(--muted);margin-bottom:1rem;">100% anonymous. Helps everyone negotiate fairly.</div>
        <form method="POST" action="/salaries">
            @csrf
            <div class="form-group"><label class="form-label">Job Title *</label><input type="text" class="form-control" name="job_title" required placeholder="e.g. Software Developer"></div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Sector</label><select class="form-control" name="sector">@foreach($sectorLabels as $k=>$v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select></div>
                <div class="form-group"><label class="form-label">Experience Level</label><select class="form-control" name="experience_level"><option value="entry">Entry</option><option value="junior">Junior</option><option value="mid" selected>Mid</option><option value="senior">Senior</option><option value="lead">Lead</option><option value="executive">Executive</option></select></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Gross Salary (XAF) *</label><input type="number" class="form-control" name="salary_amount" required min="0" placeholder="e.g. 450000"></div>
                <div class="form-group"><label class="form-label">Period</label><select class="form-control" name="period"><option value="monthly" selected>Monthly</option><option value="annual">Annual</option></select></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">City</label><input type="text" class="form-control" name="city" placeholder="e.g. Douala"></div>
                <div class="form-group"><label class="form-label">Years of Experience</label><input type="number" class="form-control" name="years_experience" min="0" max="60"></div>
            </div>
            <div class="form-group"><label class="form-label">Employment Type</label><select class="form-control" name="employment_type"><option value="full_time">Full-time</option><option value="part_time">Part-time</option><option value="contract">Contract</option><option value="internship">Internship</option><option value="freelance">Freelance</option></select></div>
            <div style="display:flex;gap:.6rem;justify-content:flex-end;margin-top:.5rem;">
                <button type="button" style="padding:.6rem 1.2rem;border:1px solid var(--border);background:#fff;border-radius:8px;font-weight:600;cursor:pointer;" onclick="document.getElementById('addModal').classList.remove('open')">Cancel</button>
                <button type="submit" style="padding:.6rem 1.5rem;background:#0d9488;color:#fff;border:none;border-radius:8px;font-weight:700;cursor:pointer;">Submit Anonymously →</button>
            </div>
        </form>
    </div>
</div>
@endif
@include('partials.footer')
</body>
</html>
