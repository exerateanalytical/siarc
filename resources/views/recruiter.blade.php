<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Recruiter — Manage Applications — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:980px;margin:0 auto;padding:1.5rem;}
.header-row{display:flex;justify-content:space-between;align-items:center;margin-bottom:1.2rem;flex-wrap:wrap;gap:.75rem;}
.h-title{font-size:1.5rem;font-weight:900;}
.h-sub{font-size:.85rem;color:var(--muted);}
.job-card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);border:1px solid var(--border);padding:1.1rem 1.3rem;margin-bottom:.8rem;display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;}
.job-title{font-weight:800;font-size:.95rem;color:var(--text);}
.job-meta{font-size:.76rem;color:var(--muted);margin-top:.2rem;}
.status-badge{font-size:.68rem;font-weight:700;padding:2px 9px;border-radius:99px;}
.counts{display:flex;gap:1.3rem;align-items:center;}
.count-box{text-align:center;}
.count-num{font-size:1.3rem;font-weight:900;color:var(--text);}
.count-lbl{font-size:.68rem;color:var(--muted);font-weight:600;}
.btn-manage{padding:.45rem 1rem;background:var(--green);color:#fff;border-radius:7px;font-size:.82rem;font-weight:700;text-decoration:none;}
.empty{text-align:center;padding:3rem;background:#fff;border-radius:var(--radius);border:1px solid var(--border);}
</style>

<div class="page">
    <div class="header-row">
        <div>
            <div class="h-title"><i data-lucide="user" class="lic"></i>‍<i data-lucide="briefcase" class="lic"></i> Recruiter Dashboard</div>
            <div class="h-sub">Review and manage applications to your job postings</div>
        </div>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
            @if(!$jobs->isEmpty())<a href="/recruiter/export" class="btn-manage" style="background:#16a34a;"><i data-lucide="download" class="lic"></i> Export CSV</a>@endif
            <a href="/analytics" class="btn-manage" style="background:#0284c7;"><i data-lucide="bar-chart-3" class="lic"></i> Analytics</a>
            <a href="/talent" class="btn-manage" style="background:#7c3aed;"><i data-lucide="sparkles" class="lic"></i> Find Talent</a>
            <a href="/jobs/post" class="btn-manage" style="background:var(--dark);">+ Post a Job</a>
        </div>
    </div>

    @if($jobs->isEmpty())
    <div class="empty">
        <div style="font-size:2rem;margin-bottom:.5rem;"><i data-lucide="user" class="lic"></i>‍<i data-lucide="briefcase" class="lic"></i></div>
        <div style="font-weight:700;margin-bottom:.3rem;">No job postings yet</div>
        <div style="font-size:.85rem;color:var(--muted);margin-bottom:1rem;">Post a job for your company to start receiving applications.</div>
        <a href="/jobs" class="btn-manage">Browse Jobs →</a>
    </div>
    @else
    @foreach($jobs as $job)
    @php $c = $counts[$job->id] ?? null; @endphp
    <div class="job-card">
        <div style="flex:1;min-width:200px;">
            <div class="job-title">{{ $job->title_en ?: $job->title_fr }}</div>
            <div class="job-meta">
                {{ $job->company_name ?? 'My company' }} ·
                <span class="status-badge" style="background:{{ $job->status==='open'?'#d1fae5':'#f3f4f6' }};color:{{ $job->status==='open'?'#065f46':'#6b7280' }};">{{ ucfirst($job->status) }}</span>
                · posted {{ date('d M Y', strtotime($job->created_at)) }}
            </div>
        </div>
        <div class="counts">
            <div class="count-box"><div class="count-num">{{ $c->total ?? 0 }}</div><div class="count-lbl">Total</div></div>
            <div class="count-box"><div class="count-num" style="color:#d97706;">{{ $c->pending ?? 0 }}</div><div class="count-lbl">New</div></div>
            <div class="count-box"><div class="count-num" style="color:#0284c7;">{{ $c->progressing ?? 0 }}</div><div class="count-lbl">In Progress</div></div>
            <a href="/jobs/{{ $job->id }}/applications" class="btn-manage">Review →</a>
        </div>
    </div>
    @endforeach
    @endif
</div>
@include('partials.footer')
</body>
</html>
