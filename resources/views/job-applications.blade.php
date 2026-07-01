<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Applications — {{ $job->title_en }} — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:880px;margin:0 auto;padding:1.5rem;}
.back{font-size:.82rem;color:var(--muted);display:inline-flex;gap:.3rem;margin-bottom:1rem;}
.job-head{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);border:1px solid var(--border);padding:1.3rem;margin-bottom:1.2rem;}
.job-title{font-size:1.3rem;font-weight:900;}
.job-sub{font-size:.83rem;color:var(--muted);margin-top:.2rem;}
.filter-tabs{display:flex;gap:.4rem;flex-wrap:wrap;margin:.9rem 0;}
.filter-tab{padding:.25rem .8rem;border-radius:99px;font-size:.76rem;font-weight:600;cursor:pointer;border:1px solid var(--border);background:#fff;color:var(--muted);}
.app-card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);border:1px solid var(--border);padding:1.1rem 1.3rem;margin-bottom:.8rem;}
.app-top{display:flex;justify-content:space-between;align-items:flex-start;gap:.8rem;flex-wrap:wrap;}
.applicant{display:flex;gap:.7rem;align-items:center;}
.av{width:42px;height:42px;border-radius:50%;background:var(--green);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:.9rem;flex-shrink:0;}
.app-name{font-weight:800;font-size:.95rem;}
.app-headline{font-size:.78rem;color:var(--muted);}
.status-badge{font-size:.7rem;font-weight:700;padding:3px 11px;border-radius:99px;}
.cover{font-size:.84rem;color:var(--text);line-height:1.6;margin:.7rem 0;padding:.7rem .9rem;background:var(--light-bg);border-radius:8px;}
.actions{display:flex;gap:.4rem;flex-wrap:wrap;align-items:center;}
.act-btn{padding:.35rem .8rem;border-radius:7px;font-size:.76rem;font-weight:700;cursor:pointer;border:1px solid var(--border);background:#fff;color:var(--text);}
.act-btn:hover{border-color:var(--green);}
.act-btn.danger:hover{border-color:var(--red);color:var(--red);}
.contact{font-size:.76rem;color:var(--muted);margin-top:.3rem;}
.empty{text-align:center;padding:3rem;background:#fff;border-radius:var(--radius);border:1px solid var(--border);}
</style>

@php
$statusColors = [
    'submitted'   => ['#fef3c7','#92400e','New'],
    'shortlisted' => ['#dbeafe','#1e40af','Shortlisted'],
    'interview'   => ['#ede9fe','#5b21b6','Interview'],
    'offered'     => ['#d1fae5','#065f46','Offer'],
    'rejected'    => ['#fee2e2','#991b1b','Rejected'],
    'withdrawn'   => ['#f3f4f6','#6b7280','Withdrawn'],
];
$total = $applications->count();
@endphp

<div class="page">
    <a class="back" href="/recruiter">← Recruiter Dashboard</a>

    <div class="job-head">
        <div class="job-title">{{ $job->title_en ?: $job->title_fr }}</div>
        <div class="job-sub">{{ $company->name ?? '' }} · {{ $total }} application{{ $total!=1?'s':'' }}
            <a href="/jobs/{{ $job->id }}" style="color:var(--green);margin-left:.4rem;">View public posting →</a>
            @if($total > 0)<a href="/jobs/{{ $job->id }}/applications/export" style="color:var(--green);margin-left:.4rem;"><i data-lucide="download" class="lic"></i> Export CSV</a>@endif
        </div>
    </div>

    @if($applications->isEmpty())
    <div class="empty">
        <div style="font-size:2rem;margin-bottom:.5rem;"><i data-lucide="inbox" class="lic"></i></div>
        <div style="font-weight:700;">No applications yet</div>
        <div style="font-size:.85rem;color:var(--muted);">Applications will appear here as candidates apply.</div>
    </div>
    @else
    @foreach($applications as $app)
    @php $sc = $statusColors[$app->status] ?? ['#f3f4f6','#6b7280',ucfirst($app->status)]; @endphp
    <div class="app-card">
        <div class="app-top">
            <div class="applicant">
                <div class="av">{{ strtoupper(substr($app->first_name??'A',0,1).substr($app->last_name??'',0,1)) }}</div>
                <div>
                    <div class="app-name">{{ trim(($app->first_name??'').' '.($app->last_name??'')) ?: 'Candidate' }}</div>
                    @if($app->headline)<div class="app-headline">{{ $app->headline }}</div>@endif
                    <div class="contact"><i data-lucide="mail" class="lic"></i> {{ $app->email }}{{ $app->emp_location ? ' · <i data-lucide="map-pin" class="lic"></i> '.$app->emp_location : '' }} · applied {{ date('d M Y', strtotime($app->created_at)) }}</div>
                    <a href="/messages/{{ $app->user_id }}" style="display:inline-block;margin-top:.4rem;font-size:.76rem;color:var(--green);font-weight:700;"><i data-lucide="message-circle" class="lic"></i> Message candidate</a>
                </div>
            </div>
            <span class="status-badge" style="background:{{ $sc[0] }};color:{{ $sc[1] }};">{{ $sc[2] }}</span>
        </div>

        @if($app->cover_letter)<div class="cover">{{ $app->cover_letter }}</div>@endif
        @if($app->cv_url)<a href="{{ $app->cv_url }}" target="_blank" rel="noopener" style="font-size:.8rem;color:var(--green);font-weight:600;"><i data-lucide="file-text" class="lic"></i> View CV →</a>@endif

        @if(!in_array($app->status,['withdrawn']))
        <div class="actions" style="margin-top:.8rem;">
            <span style="font-size:.74rem;color:var(--muted);">Set status:</span>
            @foreach(['shortlisted'=>'Shortlist','interview'=>'Interview','offered'=>'Make Offer','rejected'=>'Reject'] as $st=>$lbl)
                @if($app->status !== $st)
                <form method="POST" action="/applications/{{ $app->id }}/status" style="display:inline;">
                    @csrf
                    <input type="hidden" name="status" value="{{ $st }}">
                    <button type="submit" class="act-btn {{ $st==='rejected'?'danger':'' }}">{{ $lbl }}</button>
                </form>
                @endif
            @endforeach
        </div>
        @endif
    </div>
    @endforeach
    @endif
</div>
@include('partials.footer')
</body>
</html>
