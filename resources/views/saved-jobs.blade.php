<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Saved Jobs & Alerts — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:880px;margin:0 auto;padding:1.5rem;}
.h-title{font-size:1.5rem;font-weight:900;margin-bottom:.2rem;}
.subtitle{font-size:.85rem;color:var(--muted);margin-bottom:1.3rem;}
.success{background:#d4edda;border-radius:var(--radius);padding:.7rem 1rem;font-size:.84rem;color:#155724;margin-bottom:1rem;}
.error{background:#f8d7da;border-radius:var(--radius);padding:.7rem 1rem;font-size:.84rem;color:#842029;margin-bottom:1rem;}
.section-title{font-weight:800;font-size:1.05rem;margin:1.4rem 0 .8rem;display:flex;align-items:center;gap:.5rem;}
.job-card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);border:1px solid var(--border);padding:1.1rem 1.3rem;margin-bottom:.7rem;display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;}
.job-title{font-weight:800;font-size:.95rem;color:var(--text);}
.job-meta{font-size:.76rem;color:var(--muted);margin-top:.25rem;display:flex;gap:.8rem;flex-wrap:wrap;}
.btn{padding:.42rem .9rem;border-radius:7px;font-size:.79rem;font-weight:700;text-decoration:none;cursor:pointer;border:1px solid var(--border);background:#fff;color:var(--text);}
.btn-green{background:var(--green);color:#fff;border-color:var(--green);}
.btn-danger:hover{border-color:var(--red);color:var(--red);}
.alert-card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);border:1px solid var(--border);padding:.9rem 1.2rem;margin-bottom:.6rem;display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;}
.alert-chip{display:inline-block;font-size:.72rem;padding:2px 9px;border-radius:99px;background:#eef2ff;color:#3730a3;border:1px solid #c7d2fe;margin-right:.3rem;}
.create-card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);border:1px solid var(--border);padding:1.2rem;margin-bottom:1rem;}
.form-row{display:grid;grid-template-columns:1fr 1fr auto auto;gap:.6rem;align-items:end;}
.fg label{display:block;font-size:.74rem;font-weight:600;margin-bottom:.25rem;}
.fg input,.fg select{padding:.5rem .7rem;border:1px solid var(--border);border-radius:7px;font-size:.84rem;font-family:inherit;width:100%;box-sizing:border-box;}
.fg input:focus,.fg select:focus{outline:none;border-color:var(--green);}
.empty{text-align:center;padding:2.5rem;background:#fff;border-radius:var(--radius);border:1px solid var(--border);color:var(--muted);font-size:.85rem;}
@media(max-width:640px){.form-row{grid-template-columns:1fr 1fr;}}
</style>

@php
$typeLabels = ['any'=>'Any type','full_time'=>'Full-time','part_time'=>'Part-time','contract'=>'Contract','internship'=>'Internship','remote'=>'Remote'];
@endphp

<div class="page">
    <div class="h-title"><i data-lucide="bookmark" class="lic"></i> Saved Jobs &amp; Alerts</div>
    <p class="subtitle">Bookmark jobs to apply later, and get notified when new matching roles are posted.</p>

    @if(session('success'))<div class="success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="error">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="error">{{ $errors->first() }}</div>@endif

    <div class="section-title"><i data-lucide="bell" class="lic"></i> Job Alerts</div>
    <div class="create-card">
        <form method="POST" action="/job-alerts">
            @csrf
            <div class="form-row">
                <div class="fg"><label>Keyword</label><input type="text" name="keyword" placeholder="e.g. engineer, accountant"></div>
                <div class="fg"><label>Location</label><input type="text" name="location" placeholder="e.g. Douala"></div>
                <div class="fg"><label>Type</label><select name="job_type">@foreach($typeLabels as $k=>$v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select></div>
                <div class="fg"><button type="submit" class="btn btn-green" style="padding:.5rem 1.1rem;">+ Create Alert</button></div>
            </div>
        </form>
    </div>
    @if($alerts->isEmpty())
    <div class="empty">No alerts yet. Create one above to be notified about matching jobs.</div>
    @else
    @foreach($alerts as $a)
    <div class="alert-card">
        <div>
            @if($a->keyword)<span class="alert-chip"><i data-lucide="search" class="lic"></i> {{ $a->keyword }}</span>@endif
            @if($a->location)<span class="alert-chip"><i data-lucide="map-pin" class="lic"></i> {{ $a->location }}</span>@endif
            <span class="alert-chip"><i data-lucide="briefcase" class="lic"></i> {{ $typeLabels[$a->job_type] ?? $a->job_type }}</span>
        </div>
        <form method="POST" action="/job-alerts/{{ $a->id }}/delete" onsubmit="return confirm('Delete this alert?')">
            @csrf
            <button type="submit" class="btn btn-danger">Remove</button>
        </form>
    </div>
    @endforeach
    @endif

    <div class="section-title"><i data-lucide="pin" class="lic"></i> Saved Jobs <span style="font-size:.8rem;color:var(--muted);font-weight:600;">({{ $saved->count() }})</span></div>
    @if($saved->isEmpty())
    <div class="empty">No saved jobs yet. Browse <a href="/jobs" style="color:var(--green);">open positions</a> and tap “Save” to bookmark them.</div>
    @else
    @foreach($saved as $j)
    <div class="job-card">
        <div style="flex:1;min-width:200px;">
            <a href="/jobs/{{ $j->id }}" style="color:var(--text);"><div class="job-title">{{ $j->title_en }}</div></a>
            <div class="job-meta">
                <span><a href="/companies/{{ $j->company_slug }}" style="color:var(--green);font-weight:600;">{{ $j->company_name }}</a></span>
                <span><i data-lucide="briefcase" class="lic"></i> {{ $typeLabels[$j->type] ?? $j->type }}</span>
                @if($j->location)<span><i data-lucide="map-pin" class="lic"></i> {{ $j->location }}</span>@endif
                @if($j->salary_min && $j->salary_max)<span><i data-lucide="banknote" class="lic"></i> {{ number_format($j->salary_min) }}–{{ number_format($j->salary_max) }} XAF</span>@endif
                <span style="color:{{ $j->status==='open'?'#16a34a':'#9ca3af' }};font-weight:700;">{{ ucfirst($j->status) }}</span>
            </div>
        </div>
        <div style="display:flex;gap:.5rem;">
            <a href="/jobs/{{ $j->id }}" class="btn btn-green">View &amp; Apply</a>
            <form method="POST" action="/jobs/{{ $j->id }}/save" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-danger">Unsave</button>
            </form>
        </div>
    </div>
    @endforeach
    @endif
</div>
@include('partials.footer')
</body>
</html>
