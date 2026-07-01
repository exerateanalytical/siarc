<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>{{ $job->title_en }} at {{ $job->company_name }} — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.main{max-width:900px;margin:2rem auto;padding:0 1.5rem 3rem;display:grid;grid-template-columns:1fr 300px;gap:1.5rem;}
@media(max-width:720px){.main{grid-template-columns:1fr;}}
.breadcrumb{font-size:.78rem;color:var(--muted);margin-bottom:1rem;grid-column:1/-1;}
.breadcrumb a{color:var(--muted);}
.job-header{background:var(--white);border-radius:var(--radius);padding:1.4rem;box-shadow:var(--shadow);margin-bottom:1rem;}
.logo{width:56px;height:56px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;font-weight:800;color:#fff;margin-bottom:.8rem;}
h1{font-size:1.4rem;font-weight:800;margin-bottom:.25rem;}
.company-link{font-size:.88rem;color:var(--green);font-weight:600;text-decoration:none;}
.tags{display:flex;gap:.4rem;flex-wrap:wrap;margin-top:.7rem;}
.tag{font-size:.72rem;padding:3px 9px;border-radius:99px;font-weight:600;}
.tag-type{background:#e8f5e9;color:#2e7d32;}.tag-loc{background:#eef2f7;color:#4a5568;}.tag-dep{background:#fef3cd;color:#7a5900;}.tag-salary{background:#e3f2fd;color:#0056b3;}
.desc-card{background:var(--white);border-radius:var(--radius);padding:1.3rem;box-shadow:var(--shadow);}
.desc-card h2{font-size:1rem;font-weight:700;margin-bottom:.7rem;}
.desc-body{font-size:.88rem;color:var(--muted);line-height:1.7;}
.desc-body p{margin-bottom:.7rem;}
.desc-body ul,.desc-body ol{padding-left:1.2rem;margin-bottom:.7rem;}
.desc-body li{margin-bottom:.25rem;}
.sidebar-card{background:var(--white);border-radius:var(--radius);padding:1.2rem;box-shadow:var(--shadow);margin-bottom:1rem;}
.sidebar-card h3{font-size:.88rem;font-weight:700;margin-bottom:.8rem;}
.apply-btn{display:block;padding:.7rem;background:var(--green);color:#fff;border:none;border-radius:8px;font-size:.92rem;font-weight:700;cursor:pointer;text-align:center;width:100%;margin-bottom:.5rem;}
.apply-btn:disabled,.apply-btn.submitted{background:#aaa;cursor:default;}
.info-row{display:flex;justify-content:space-between;font-size:.82rem;padding:.4rem 0;border-bottom:1px solid var(--border);}
.info-row:last-child{border-bottom:none;}
.info-label{color:var(--muted);}
.info-val{font-weight:600;}
.cover-ta{width:100%;padding:.6rem .8rem;border:1px solid var(--border);border-radius:7px;font-size:.85rem;resize:vertical;min-height:120px;font-family:inherit;box-sizing:border-box;}
.cover-ta:focus{outline:none;border-color:var(--green);}
.form-note{font-size:.75rem;color:var(--muted);margin-top:.3rem;}
.similar-item{display:block;padding:.6rem 0;border-bottom:1px solid var(--border);font-size:.82rem;}
.similar-item:last-child{border-bottom:none;}
.similar-co{font-size:.73rem;color:var(--muted);}
.success-box{background:#d4edda;border-radius:var(--radius);padding:.8rem 1rem;font-size:.85rem;color:#155724;margin-bottom:.8rem;}
</style>

<div class="main">
    <div class="breadcrumb"><a href="/">Home</a> › <a href="/jobs">Jobs</a> › {{ $job->title_en }}</div>

    <div>
        <div class="job-header">
            @php $clrs=['#007a33','#ce1126','#0056b3','#7b2d8b','#c0392b','#16a085']; $clr=$clrs[crc32($job->company_slug??'')%count($clrs)]; @endphp
            <div class="logo" style="background:{{ $clr }}">{{ strtoupper(substr($job->company_name??'?',0,2)) }}</div>
            <h1>{{ $job->title_en }}</h1>
            <a class="company-link" href="/companies/{{ $job->company_slug }}">{{ $job->company_name }}</a>
            <div class="tags">
                @php $typeLabel=['full_time'=>'Full-time','part_time'=>'Part-time','contract'=>'Contract','internship'=>'Internship','remote'=>'Remote'][$job->type]??ucfirst($job->type); @endphp
                <span class="tag tag-type">{{ $typeLabel }}</span>
                @if($job->location)<span class="tag tag-loc"><i data-lucide="map-pin" style="width:11px;height:11px;display:inline;vertical-align:-1px;"></i> {{ $job->location }}</span>@endif
                @if($job->department)<span class="tag tag-dep">{{ $job->department }}</span>@endif
                @if($job->salary_min)<span class="tag tag-salary">{{ number_format($job->salary_min) }}{{ $job->salary_max?' – '.number_format($job->salary_max):'+' }} XAF/mo</span>@endif
            </div>
        </div>

        <div class="desc-card">
            <h2>Job Description</h2>
            <div class="desc-body">
                @if($job->description_en)
                    {!! nl2br(e($job->description_en)) !!}
                @else
                    <p style="color:var(--muted);">No description provided.</p>
                @endif
            </div>
        </div>
    </div>

    <div>
        @if(session('auth_user'))
        <form method="POST" action="/jobs/{{ $job->id }}/save" style="margin-bottom:.7rem;">
            @csrf
            <button type="submit" style="width:100%;padding:.6rem;border-radius:8px;font-size:.85rem;font-weight:700;cursor:pointer;border:1px solid {{ ($isSaved ?? false) ? 'var(--green)' : 'var(--border)' }};background:{{ ($isSaved ?? false) ? '#e8f5e9' : '#fff' }};color:{{ ($isSaved ?? false) ? 'var(--green)' : 'var(--text)' }};">
                {{ ($isSaved ?? false) ? '★ Saved — tap to remove' : '☆ Save this job' }}
            </button>
        </form>
        @endif
        <div class="sidebar-card">
            @if($applied)
            <div class="success-box"><i data-lucide="check-circle-2" style="width:15px;height:15px;display:inline;vertical-align:-2px;"></i> You applied on {{ date('d M Y', strtotime($applied->created_at)) }}<br><small>Status: {{ ucfirst($applied->status) }}</small></div>
            @elseif(session('auth_user'))
            @if(session('success'))<div class="success-box">{{ session('success') }}</div>@endif
            @if(session('error'))<div style="background:#f8d7da;border-radius:var(--radius);padding:.8rem 1rem;font-size:.85rem;color:#721c24;margin-bottom:.8rem;">{{ session('error') }}</div>@endif
            <form method="POST" action="/jobs/{{ $job->id }}/apply">
                @csrf
                <label style="font-size:.8rem;font-weight:600;display:block;margin-bottom:.4rem;">Cover Letter <span style="color:var(--red)">*</span></label>
                <textarea class="cover-ta" name="cover_letter" placeholder="Introduce yourself and explain why you're the right candidate for this role (min. 50 characters)…" required minlength="50">{{ old('cover_letter') }}</textarea>
                @error('cover_letter')<div style="color:var(--red);font-size:.75rem;margin-top:.2rem;">{{ $message }}</div>@enderror
                <div class="form-note">Your profile and contact email will be shared with the company.</div>
                <button type="submit" class="apply-btn" style="margin-top:.7rem;">Apply Now →</button>
            </form>
            <a href="/cover-letters?job_title={{ urlencode($job->title_en) }}&company={{ urlencode($job->company_name) }}" style="display:block;text-align:center;margin-top:.6rem;font-size:.8rem;color:var(--green);font-weight:600;"><i data-lucide="pen-line" style="width:13px;height:13px;display:inline;vertical-align:-2px;"></i> Need help? Build a cover letter →</a>
            @else
            <p style="font-size:.85rem;color:var(--muted);margin-bottom:.7rem;">Log in to apply for this position.</p>
            <a class="apply-btn" href="/login?next=/jobs/{{ $job->id }}" style="text-decoration:none;display:block;text-align:center;">Log In to Apply</a>
            <a href="/register" style="display:block;text-align:center;margin-top:.4rem;font-size:.82rem;color:var(--green);">Create free account →</a>
            @endif
        </div>

        <div class="sidebar-card">
            <h3>Job Details</h3>
            <div class="info-row"><span class="info-label">Type</span><span class="info-val">{{ $typeLabel }}</span></div>
            <div class="info-row"><span class="info-label">Location</span><span class="info-val">{{ $job->location ?? 'N/A' }}</span></div>
            @if($job->department)<div class="info-row"><span class="info-label">Department</span><span class="info-val">{{ $job->department }}</span></div>@endif
            @if($job->salary_min)<div class="info-row"><span class="info-label">Salary</span><span class="info-val">{{ number_format($job->salary_min) }}{{ $job->salary_max?' – '.number_format($job->salary_max):'+' }} XAF</span></div>@endif
            @if($job->deadline)<div class="info-row"><span class="info-label">Deadline</span><span class="info-val">{{ date('d M Y', strtotime($job->deadline)) }}</span></div>@endif
            <div class="info-row"><span class="info-label">Posted</span><span class="info-val">{{ $job->created_at ? date('d M Y', strtotime($job->created_at)) : '' }}</span></div>
            <div class="info-row"><span class="info-label">Views</span><span class="info-val">{{ number_format($job->view_count ?? 0) }}</span></div>
        </div>

        @if($similar->count())
        <div class="sidebar-card">
            <h3>Similar Jobs</h3>
            @foreach($similar as $s)
            <a class="similar-item" href="/jobs/{{ $s->id }}">
                <div>{{ $s->title_en }}</div>
                <div class="similar-co">{{ $s->company_name }}</div>
            </a>
            @endforeach
        </div>
        @endif
    </div>
</div>
@include('partials.footer')
</body>
</html>
