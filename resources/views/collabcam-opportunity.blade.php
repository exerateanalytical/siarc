<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>{{ $opp->title_en }} — CollabCam — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:1000px;margin:0 auto;padding:1.5rem;}
.back{font-size:.82rem;color:var(--muted);margin-bottom:1rem;display:inline-flex;align-items:center;gap:.3rem;}
.back:hover{color:var(--green);}
.grid2{display:grid;grid-template-columns:1fr 300px;gap:1.5rem;}
.card{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);margin-bottom:1rem;}
.card-title{padding:.8rem 1.1rem;font-weight:700;font-size:.88rem;border-bottom:1px solid var(--border);background:var(--light-bg);}
.card-body{padding:1.2rem;}
.opp-type-badge{padding:4px 14px;border-radius:99px;font-size:.75rem;font-weight:700;background:var(--light-bg);color:var(--muted);border:1px solid var(--border);display:inline-block;margin-bottom:.75rem;}
.opp-title-lg{font-size:1.4rem;font-weight:900;color:var(--text);line-height:1.3;margin-bottom:.6rem;}
.opp-desc-full{font-size:.88rem;color:var(--text);line-height:1.7;}
.meta-grid{display:grid;grid-template-columns:1fr 1fr;gap:.6rem;margin-top:1rem;}
.meta-item{padding:.6rem .8rem;background:var(--light-bg);border-radius:8px;}
.meta-lbl{font-size:.7rem;color:var(--muted);font-weight:600;margin-bottom:2px;}
.meta-val{font-size:.85rem;font-weight:700;color:var(--text);}
.respond-box{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);padding:1.3rem;margin-bottom:1rem;border:2px solid var(--green);}
.respond-title{font-weight:800;font-size:.95rem;color:var(--text);margin-bottom:1rem;}
.form-group{margin-bottom:.9rem;}
.form-label{display:block;font-size:.8rem;font-weight:600;margin-bottom:.3rem;color:var(--text);}
.form-control{width:100%;padding:.5rem .75rem;border:1px solid var(--border);border-radius:7px;font-size:.85rem;outline:none;font-family:inherit;color:var(--text);}
.form-control:focus{border-color:var(--green);}
textarea.form-control{resize:vertical;min-height:100px;}
.btn-primary{width:100%;padding:.6rem;background:var(--green);color:#fff;border:none;border-radius:8px;font-size:.9rem;font-weight:700;cursor:pointer;}
.btn-primary:hover{background:#00962e;}
.info-row{display:flex;justify-content:space-between;padding:.4rem 0;border-bottom:1px solid var(--border);font-size:.83rem;}
.info-row:last-child{border-bottom:none;}
.info-lbl{color:var(--muted);}
.info-val{font-weight:600;color:var(--text);}
.resp-row{padding:.75rem 0;border-bottom:1px solid var(--border);display:flex;gap:.75rem;align-items:flex-start;}
.resp-row:last-child{border-bottom:none;}
.resp-co-logo{width:36px;height:36px;border-radius:8px;background:var(--dark);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.72rem;color:var(--yellow);flex-shrink:0;}
.resp-status{display:inline-block;padding:1px 8px;border-radius:99px;font-size:.67rem;font-weight:700;}
.rs-pending{background:#fff3cd;color:#856404;}
.rs-shortlisted{background:#cce5ff;color:#0056b3;}
.rs-accepted{background:#d4edda;color:#166534;}
.rs-rejected{background:#f8d7da;color:#721c24;}
</style>

@php
$authUser = session('auth_user');
$typeLabels = [
    'seeking_supplier'=>'Seeking Supplier','seeking_distributor'=>'Seeking Distributor',
    'seeking_manufacturer'=>'Seeking Manufacturer','seeking_investor'=>'Seeking Investor',
    'seeking_logistics'=>'Seeking Logistics','seeking_warehouse'=>'Seeking Warehouse',
    'seeking_technology'=>'Seeking Technology','seeking_research'=>'Seeking R&D Partner',
    'seeking_export'=>'Seeking Export Partner','seeking_joint_venture'=>'Joint Venture',
    'seeking_consultant'=>'Seeking Consultant','seeking_subcontractor'=>'Seeking Subcontractor',
    'seeking_packaging'=>'Seeking Packaging','seeking_processing'=>'Seeking Processing',
    'offering_capacity'=>'Offering Capacity','offering_equipment'=>'Offering Equipment','other'=>'Partnership',
];
$myCompanies = $authUser ? DB::table('company_users')
    ->join('companies','company_users.company_id','=','companies.id')
    ->where('company_users.user_id',$authUser['id'])
    ->where('company_users.status','approved')
    ->whereNull('companies.deleted_at')
    ->select('companies.id','companies.name','companies.slug')->get() : collect();
// Check if already responded
$alreadyResponded = false;
if($authUser && $myCompanies->count() > 0) {
    $myCoIds = $myCompanies->pluck('id')->toArray();
    $alreadyResponded = DB::table('collabcam_opportunity_responses')
        ->where('opportunity_id',$opp->id)->whereIn('company_id',$myCoIds)->exists();
}
$isOwner = $authUser && $myCompanies->pluck('id')->contains($opp->company_id);
$responses = $isOwner ? DB::table('collabcam_opportunity_responses')
    ->join('companies','collabcam_opportunity_responses.company_id','=','companies.id')
    ->where('opportunity_id',$opp->id)
    ->select('collabcam_opportunity_responses.*','companies.name as co_name','companies.slug as co_slug')
    ->get() : collect();
@endphp

<div class="page">
    <a class="back" href="/collabcam/opportunities">← Back to Opportunities</a>

    <div class="grid2">
        <div>
            <div class="card">
                <div class="card-body">
                    <span class="opp-type-badge">{{ $typeLabels[$opp->type]??ucfirst(str_replace('_',' ',$opp->type)) }}</span>
                    @if($opp->is_featured)<span style="background:var(--yellow);color:var(--dark);padding:3px 10px;border-radius:99px;font-size:.7rem;font-weight:800;"><i data-lucide="star" class="lic"></i> Featured</span>@endif
                    <div class="opp-title-lg">{{ $opp->title_en }}</div>
                    <div class="opp-desc-full">{{ $opp->description_en }}</div>
                    <div class="meta-grid">
                        @if($opp->sector)<div class="meta-item"><div class="meta-lbl">Sector</div><div class="meta-val">{{ ucfirst($opp->sector) }}</div></div>@endif
                        @if($opp->budget_range)<div class="meta-item"><div class="meta-lbl">Budget</div><div class="meta-val">{{ $opp->budget_range }}</div></div>@endif
                        @if($opp->location)<div class="meta-item"><div class="meta-lbl">Location</div><div class="meta-val">{{ $opp->location }}</div></div>@endif
                        @if($opp->deadline)<div class="meta-item"><div class="meta-lbl">Deadline</div><div class="meta-val">{{ date('d M Y',strtotime($opp->deadline)) }}</div></div>@endif
                        <div class="meta-item"><div class="meta-lbl">Views</div><div class="meta-val">{{ number_format($opp->view_count) }}</div></div>
                        <div class="meta-item"><div class="meta-lbl">Responses</div><div class="meta-val">{{ $opp->response_count }}</div></div>
                    </div>
                </div>
            </div>

            @if($isOwner && $responses->count() > 0)
            <div class="card">
                <div class="card-title">Responses ({{ $responses->count() }})</div>
                <div class="card-body" style="padding:.5rem 1rem;">
                    @foreach($responses as $r)
                    <div class="resp-row">
                        <div class="resp-co-logo">{{ strtoupper(substr($r->co_name,0,2)) }}</div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-weight:700;font-size:.85rem;color:var(--text);">{{ $r->co_name }} <span class="resp-status rs-{{ $r->status }}">{{ ucfirst($r->status) }}</span></div>
                            <div style="font-size:.8rem;color:var(--muted);margin-top:2px;">{{ Str::limit($r->message,120) }}</div>
                            @if($r->proposed_terms)<div style="font-size:.75rem;color:var(--muted);margin-top:2px;font-style:italic;">Terms: {{ $r->proposed_terms }}</div>@endif
                        </div>
                        @if($r->status === 'pending')
                        <div style="display:flex;flex-direction:column;gap:.3rem;flex-shrink:0;">
                            <form method="POST" action="/collabcam/opportunity-responses/{{ $r->id }}/shortlist">@csrf<button style="padding:.25rem .6rem;background:var(--green);color:#fff;border:none;border-radius:5px;font-size:.7rem;cursor:pointer;font-weight:700;">Shortlist</button></form>
                            <form method="POST" action="/collabcam/opportunity-responses/{{ $r->id }}/reject">@csrf<button style="padding:.25rem .6rem;background:#f8d7da;color:#721c24;border:none;border-radius:5px;font-size:.7rem;cursor:pointer;font-weight:700;">Reject</button></form>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <div>
            @if(!$isOwner)
            @if($alreadyResponded)
            <div class="respond-box" style="border-color:var(--muted);">
                <div style="text-align:center;padding:.5rem;">
                    <div style="font-size:1.5rem;margin-bottom:.5rem;"><i data-lucide="check" class="lic"></i></div>
                    <div style="font-weight:700;color:var(--text);">Response sent!</div>
                    <div style="font-size:.82rem;color:var(--muted);margin-top:.4rem;">Your response has been submitted. The company will review it and get back to you.</div>
                </div>
            </div>
            @elseif($authUser && $myCompanies->count() > 0)
            <div class="respond-box">
                <div class="respond-title">Respond to this Opportunity</div>
                <form method="POST" action="/collabcam/opportunities/{{ $opp->id }}/respond">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Respond as</label>
                        <select class="form-control" name="company_id" required>
                            @foreach($myCompanies as $mc)<option value="{{ $mc->id }}">{{ $mc->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Your message</label>
                        <textarea class="form-control" name="message" placeholder="Introduce your company and explain how you can fulfil this opportunity…" required></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Proposed terms (optional)</label>
                        <input type="text" class="form-control" name="proposed_terms" placeholder="e.g. 3M XAF/month, 6-month contract">
                    </div>
                    <button type="submit" class="btn-primary">Send Response →</button>
                </form>
            </div>
            @elseif($authUser)
            <div class="respond-box" style="border-color:var(--muted);">
                <div style="font-size:.85rem;color:var(--text);margin-bottom:.75rem;">You need a claimed company to respond to opportunities.</div>
                <a href="/" style="display:block;text-align:center;background:var(--green);color:#fff;padding:.55rem;border-radius:7px;font-size:.85rem;font-weight:700;">Browse Companies →</a>
            </div>
            @else
            <div class="respond-box" style="border-color:var(--muted);">
                <div style="font-size:.85rem;color:var(--text);margin-bottom:.75rem;">Sign in to respond to this opportunity.</div>
                <a href="/auth/login" style="display:block;text-align:center;background:var(--green);color:#fff;padding:.55rem;border-radius:7px;font-size:.85rem;font-weight:700;">Sign In →</a>
                <a href="/auth/register" style="display:block;text-align:center;border:1px solid var(--border);color:var(--text);padding:.45rem;border-radius:7px;font-size:.82rem;font-weight:600;margin-top:.5rem;">Register Free →</a>
            </div>
            @endif
            @endif

            <div class="card">
                <div class="card-title">Posted by</div>
                <div class="card-body">
                    <div style="display:flex;gap:.75rem;align-items:center;margin-bottom:.75rem;">
                        <div style="width:44px;height:44px;border-radius:9px;background:linear-gradient(135deg,var(--dark),var(--mid));display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.9rem;color:var(--yellow);flex-shrink:0;">{{ strtoupper(substr($company->name,0,2)) }}</div>
                        <div>
                            <div style="font-weight:800;font-size:.9rem;color:var(--text);">{{ $company->name }}</div>
                            @if($company->verification_status==='verified')<div style="font-size:.7rem;background:#d4edda;color:#166534;padding:1px 8px;border-radius:99px;display:inline-block;font-weight:700;margin-top:2px;"><i data-lucide="check" class="lic"></i> Verified</div>@endif
                        </div>
                    </div>
                    <div class="info-row"><span class="info-lbl">Status</span><span class="info-val">{{ ucfirst($opp->status) }}</span></div>
                    <div class="info-row"><span class="info-lbl">Posted</span><span class="info-val">{{ date('d M Y',strtotime($opp->created_at)) }}</span></div>
                    <div style="margin-top:.9rem;">
                        <a href="/companies/{{ $company->slug }}" style="display:block;text-align:center;border:1px solid var(--border);color:var(--text);padding:.5rem;border-radius:7px;font-size:.83rem;font-weight:600;">View Company →</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('partials.footer')
</body>
</html>
