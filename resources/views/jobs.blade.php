<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Jobs in Cameroon — Galerie virtuelle de l'artisanat du Cameroun</title></head>
<body>
@include('partials.nav')
<style>
.hero{background:linear-gradient(135deg,var(--dark),var(--mid));padding:2.5rem 2rem 1.8rem;text-align:center;color:#fff;}
.hero h1{font-size:1.8rem;font-weight:800;margin-bottom:.3rem;}
.hero p{color:#aab;font-size:.9rem;margin-bottom:1rem;}
.search-row{display:flex;gap:.4rem;max-width:640px;margin:0 auto;}
.search-row input{flex:1;padding:.58rem 1rem;border:none;border-radius:7px;font-size:.9rem;background:rgba(255,255,255,.15);color:#fff;}
.search-row input::placeholder{color:rgba(255,255,255,.5);}
.search-row button{padding:.58rem 1.2rem;background:var(--green);color:#fff;border:none;border-radius:7px;font-weight:600;cursor:pointer;}
.filters{display:flex;gap:.4rem;justify-content:center;flex-wrap:wrap;margin-top:.8rem;}
.filter-chip{padding:.3rem .85rem;border-radius:99px;font-size:.78rem;font-weight:600;cursor:pointer;border:1px solid rgba(255,255,255,.25);color:#fff;background:rgba(255,255,255,.08);text-decoration:none;}
.filter-chip.active,.filter-chip:hover{background:var(--green);border-color:var(--green);}
.main{max-width:900px;margin:0 auto;padding:1.5rem;}
.stats-bar{display:flex;gap:1rem;align-items:center;margin-bottom:1rem;font-size:.83rem;color:var(--muted);}
.jobs-list{display:flex;flex-direction:column;gap:.8rem;}
.job-card{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);padding:1.2rem 1.3rem;display:flex;gap:1rem;align-items:flex-start;transition:transform .15s;}
.job-card:hover{transform:translateY(-2px);box-shadow:var(--shadow-hover);}
.job-logo{width:44px;height:44px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1rem;font-weight:800;color:#fff;flex-shrink:0;}
.job-info{flex:1;min-width:0;}
.job-title{font-size:.97rem;font-weight:700;margin-bottom:.2rem;}
.job-company{font-size:.82rem;color:var(--green);font-weight:600;}
.job-meta{display:flex;gap:.6rem;flex-wrap:wrap;margin-top:.45rem;}
.tag{font-size:.7rem;padding:2px 8px;border-radius:99px;font-weight:600;}
.tag-type{background:#e8f5e9;color:#2e7d32;}
.tag-loc{background:#eef2f7;color:#4a5568;}
.tag-dep{background:#fef3cd;color:#7a5900;}
.job-salary{font-size:.8rem;color:var(--muted);margin-top:.3rem;}
.job-right{text-align:right;flex-shrink:0;}
.job-date{font-size:.73rem;color:var(--muted);margin-bottom:.4rem;}
.job-apply{display:inline-block;padding:.38rem .9rem;background:var(--green);color:#fff;border-radius:7px;font-size:.78rem;font-weight:700;text-decoration:none;}
.vbadge{font-size:.65rem;padding:2px 6px;border-radius:99px;font-weight:700;text-transform:uppercase;}
.vs-certified{background:#d4edda;color:#007a33;}.vs-verified{background:#cce5ff;color:#0056b3;}.vs-basic{background:#fff3cd;color:#856404;}.vs-unverified{background:#f8f9fa;color:#6c757d;}
.empty{text-align:center;padding:3rem;color:var(--muted);}
.pagination{display:flex;justify-content:center;gap:.3rem;margin-top:1.5rem;flex-wrap:wrap;}
.page-btn{padding:.38rem .75rem;border:1px solid var(--border);border-radius:6px;background:var(--white);font-size:.8rem;}
.page-btn:hover{background:var(--light-bg);}
.page-btn.active{background:var(--green);color:#fff;border-color:var(--green);}
@media(max-width:600px){
  .hero{padding:1.6rem 1rem;}.hero h1{font-size:1.45rem;}
  .search-row{flex-direction:column;max-width:100%;}
  .main{padding:1rem;}
  .job-card{flex-wrap:wrap;}
  .job-right{text-align:left;width:100%;display:flex;align-items:center;justify-content:space-between;margin-top:.5rem;}
  .job-date{margin-bottom:0;}
}
</style>

<div class="hero">
    <h1>Jobs in Cameroon</h1>
    <p>{{ number_format($total) }} open positions at verified Cameroonian companies</p>
    <form class="search-row" method="GET" action="/jobs">
        @if($type)<input type="hidden" name="type" value="{{ $type }}">@endif
        <input type="text" name="q" placeholder="Job title, company, location…" value="{{ $q }}">
        <button type="submit">Search</button>
    </form>
    <div class="filters">
        <a class="filter-chip {{ !$type ? 'active' : '' }}" href="/jobs{{ $q ? '?q='.$q : '' }}">All Types</a>
        @foreach(['full_time'=>'Full-time','part_time'=>'Part-time','contract'=>'Contract','internship'=>'Internship','remote'=>'Remote'] as $val=>$label)
        <a class="filter-chip {{ $type===$val ? 'active' : '' }}" href="/jobs?type={{ $val }}{{ $q ? '&q='.$q : '' }}">{{ $label }}</a>
        @endforeach
    </div>
</div>

<div class="main">
    <div class="stats-bar">
        <span>Showing {{ $jobs->count() }} of {{ number_format($total) }} jobs</span>
        @if($q || $type)<a href="/jobs" style="color:var(--green);"><i data-lucide="x" class="lic"></i> Clear filters</a>@endif
    </div>

    @if($jobs->isEmpty())
    <div class="empty"><i data-lucide="briefcase" style="width:40px;height:40px;color:var(--muted);margin-bottom:.4rem;"></i><p>No jobs match your filters.</p><a href="/jobs">Clear filters</a></div>
    @else
    <div class="jobs-list">
        @foreach($jobs as $job)
        @php
            $clrs = ['#007a33','#ce1126','#0056b3','#7b2d8b','#c0392b','#16a085'];
            $clr  = $clrs[crc32($job->company_slug ?? '') % count($clrs)];
            $ini  = strtoupper(substr($job->company_name ?? '?', 0, 2));
            $typeLabel = ['full_time'=>'Full-time','part_time'=>'Part-time','contract'=>'Contract','internship'=>'Internship','remote'=>'Remote'][$job->type] ?? ucfirst($job->type);
            $vsC = match($job->verification_status??'unverified'){'certified'=>'vs-certified','verified'=>'vs-verified','basic'=>'vs-basic',default=>'vs-unverified'};
        @endphp
        <div class="job-card">
            <a href="/companies/{{ $job->company_slug }}" style="text-decoration:none;">
                <div class="job-logo" style="background:{{ $clr }}">{{ $ini }}</div>
            </a>
            <div class="job-info">
                <a href="/jobs/{{ $job->id }}" style="text-decoration:none;color:inherit;">
                    <div class="job-title">{{ $job->title_en }}</div>
                </a>
                <div class="job-company">
                    <a href="/companies/{{ $job->company_slug }}" style="color:inherit;text-decoration:none;">{{ $job->company_name }}</a>
                    <span class="vbadge {{ $vsC }}" style="margin-left:.4rem;">{{ ucfirst($job->verification_status??'') }}</span>
                </div>
                <div class="job-meta">
                    <span class="tag tag-type">{{ $typeLabel }}</span>
                    @if($job->location)<span class="tag tag-loc"><i data-lucide="map-pin" style="width:11px;height:11px;display:inline;vertical-align:-1px;"></i> {{ $job->location }}</span>@endif
                    @if($job->department)<span class="tag tag-dep">{{ $job->department }}</span>@endif
                </div>
                @if($job->salary_min)
                <div class="job-salary">{{ number_format($job->salary_min) }}{{ $job->salary_max ? ' – '.number_format($job->salary_max) : '+' }} XAF/month</div>
                @endif
            </div>
            <div class="job-right">
                <div class="job-date">{{ $job->created_at ? date('d M', strtotime($job->created_at)) : '' }}</div>
                <a class="job-apply" href="/jobs/{{ $job->id }}">View →</a>
            </div>
        </div>
        @endforeach
    </div>

    @if($jobs->hasPages())
    <div class="pagination">
        @if($jobs->onFirstPage())<span class="page-btn" style="opacity:.4">← Prev</span>@else<a class="page-btn" href="{{ $jobs->previousPageUrl() }}">← Prev</a>@endif
        @foreach($jobs->getUrlRange(max(1,$jobs->currentPage()-2), min($jobs->lastPage(),$jobs->currentPage()+2)) as $page=>$url)
        <a class="page-btn {{ $page===$jobs->currentPage() ? 'active' : '' }}" href="{{ $url }}">{{ $page }}</a>
        @endforeach
        @if($jobs->hasMorePages())<a class="page-btn" href="{{ $jobs->nextPageUrl() }}">Next →</a>@else<span class="page-btn" style="opacity:.4">Next →</span>@endif
    </div>
    @endif
    @endif
</div>

<div style="background:var(--white);border-top:1px solid var(--border);padding:1.5rem;text-align:center;margin-top:1rem;">
    <p style="font-size:.85rem;color:var(--muted);margin-bottom:.6rem;">Are you a company looking to hire?</p>
    <a href="/about" style="display:inline-block;padding:.5rem 1.3rem;background:var(--green);color:#fff;border-radius:7px;font-weight:700;font-size:.85rem;text-decoration:none;">List Your Company →</a>
</div>
@include('partials.footer')
</body>
</html>
