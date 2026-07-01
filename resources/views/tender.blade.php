<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>{{ $tender->title }} — Tenders — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:1000px;margin:0 auto;padding:1.5rem;}
.back{font-size:.82rem;color:var(--muted);display:inline-flex;align-items:center;gap:.3rem;margin-bottom:1rem;}
.back:hover{color:var(--green);}
.grid2{display:grid;grid-template-columns:1fr 300px;gap:1.5rem;}
.card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);margin-bottom:1rem;border:1px solid var(--border);}
.card-title{padding:.75rem 1.1rem;font-weight:700;font-size:.88rem;border-bottom:1px solid var(--border);background:var(--light-bg);}
.card-body{padding:1.2rem;}
.tender-title{font-size:1.4rem;font-weight:900;color:var(--text);line-height:1.3;margin-bottom:.6rem;}
.badge{display:inline-block;padding:3px 12px;border-radius:99px;font-size:.75rem;font-weight:700;}
.badge-open{background:#d4edda;color:#166534;}
.badge-closed{background:#f8d7da;color:#721c24;}
.badge-awarded{background:#cce5ff;color:#0056b3;}
.badge-cat{background:var(--light-bg);color:var(--muted);border:1px solid var(--border);}
.info-grid{display:grid;grid-template-columns:1fr 1fr;gap:.6rem;margin-top:1rem;}
.info-item{padding:.6rem .8rem;background:var(--light-bg);border-radius:8px;}
.info-lbl{font-size:.68rem;color:var(--muted);font-weight:600;margin-bottom:2px;}
.info-val{font-size:.88rem;font-weight:700;color:var(--text);}
.section-title{font-weight:700;font-size:.9rem;color:var(--text);margin:.9rem 0 .4rem;}
.desc-text{font-size:.87rem;color:var(--text);line-height:1.7;}
.bid-box{background:#fff;border:2px solid var(--green);border-radius:var(--radius);padding:1.3rem;margin-bottom:1rem;}
.bid-title{font-weight:800;font-size:.95rem;margin-bottom:1rem;color:var(--text);}
.form-group{margin-bottom:.9rem;}
.form-label{display:block;font-size:.8rem;font-weight:600;margin-bottom:.3rem;}
.form-control{width:100%;padding:.5rem .75rem;border:1px solid var(--border);border-radius:7px;font-size:.85rem;outline:none;font-family:inherit;color:var(--text);box-sizing:border-box;}
.form-control:focus{border-color:var(--green);}
textarea.form-control{resize:vertical;min-height:100px;}
.btn-bid{width:100%;padding:.6rem;background:var(--green);color:#fff;border:none;border-radius:8px;font-size:.9rem;font-weight:700;cursor:pointer;}
.btn-bid:hover{background:#00962e;}
.side-card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);padding:1.2rem;margin-bottom:1rem;border:1px solid var(--border);}
.side-row{display:flex;justify-content:space-between;padding:.4rem 0;border-bottom:1px solid var(--border);font-size:.83rem;}
.side-row:last-child{border-bottom:none;}
.side-lbl{color:var(--muted);}
.side-val{font-weight:600;color:var(--text);}
.deadline-warning{background:#fff3cd;border:1px solid #ffc107;border-radius:8px;padding:.6rem .8rem;font-size:.8rem;font-weight:600;color:#856404;margin-bottom:1rem;}
.bid-item{padding:.75rem 0;border-bottom:1px solid var(--border);display:flex;gap:.7rem;align-items:flex-start;}
.bid-item:last-child{border-bottom:none;}
@media(max-width:700px){.grid2{grid-template-columns:1fr;}.info-grid{grid-template-columns:1fr;}}
</style>

@php
$authUser = session('auth_user');
$catIcons = ['goods'=>'package','services'=>'settings','works'=>'hard-hat','consultancy'=>'briefcase','ict'=>'laptop','agriculture'=>'wheat','construction'=>'hammer','other'=>'clipboard-list'];
$typeLabels = ['open'=>'Open Tender','restricted'=>'Restricted','rfq'=>'RFQ','rfp'=>'RFP','rfi'=>'RFI','expression_of_interest'=>'EOI','sole_source'=>'Sole Source'];
$daysLeft = now()->diffInDays($tender->deadline, false);
$myCompanies = $authUser ? DB::table('company_users')
    ->join('companies','company_users.company_id','=','companies.id')
    ->where('company_users.user_id',$authUser['id'])
    ->where('company_users.status','approved')
    ->whereNull('companies.deleted_at')
    ->select('companies.id','companies.name')->get() : collect();
$alreadyBid = $authUser && $myCompanies->count() > 0 &&
    DB::table('tender_bids')->where('tender_id',$tender->id)->whereIn('company_id',$myCompanies->pluck('id')->toArray())->exists();
$isOwner = $authUser && $myCompanies->pluck('id')->contains($tender->company_id);
$bids = $isOwner ? DB::table('tender_bids')
    ->join('companies','tender_bids.company_id','=','companies.id')
    ->where('tender_bids.tender_id',$tender->id)
    ->select('tender_bids.*','companies.name as co_name','companies.slug as co_slug')
    ->orderByDesc('tender_bids.created_at')->get() : collect();
// increment view count
DB::table('tenders')->where('id',$tender->id)->increment('view_count');
@endphp

<div class="page">
    <a class="back" href="/tenders">← Back to Tenders</a>

    <div class="grid2">
        <div>
            <div class="card">
                <div class="card-body">
                    <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:.6rem;">
                        <span class="badge badge-{{ $tender->status }}">{{ ucfirst($tender->status) }}</span>
                        <span class="badge badge-cat"><i data-lucide="{{ $catIcons[$tender->category]??'clipboard-list' }}" class="lic"></i> {{ ucfirst($tender->category) }}</span>
                        <span class="badge badge-cat">{{ $typeLabels[$tender->type]??ucfirst($tender->type) }}</span>
                    </div>
                    <div class="tender-title">{{ $tender->title }}</div>
                    <div style="font-size:.82rem;color:var(--muted);margin-bottom:1rem;">
                        Posted by <a href="/companies/{{ $company->slug }}" style="color:var(--green);font-weight:600;">{{ $company->name }}</a>
                        {{ $tender->location ? ' · '.$tender->location : '' }}
                    </div>
                    @if($daysLeft >= 0 && $daysLeft <= 7 && $tender->status === 'open')
                        <div class="deadline-warning"><i data-lucide="alert-triangle" class="lic"></i> Closing in {{ $daysLeft }} day{{ $daysLeft!=1?'s':'' }} — Submit your bid soon!</div>
                    @elseif($daysLeft < 0)
                        <div class="deadline-warning" style="background:#f8d7da;border-color:#f5c6cb;color:#721c24;"><i data-lucide="x" class="lic"></i> Bidding closed on {{ date('d M Y',strtotime($tender->deadline)) }}</div>
                    @endif
                    <div class="info-grid">
                        <div class="info-item"><div class="info-lbl">Deadline</div><div class="info-val">{{ date('d M Y',strtotime($tender->deadline)) }}</div></div>
                        <div class="info-item"><div class="info-lbl">Budget</div><div class="info-val">{{ $tender->budget_estimate ? number_format($tender->budget_estimate/1000000,1).'M '.$tender->currency : 'Not disclosed' }}</div></div>
                        <div class="info-item"><div class="info-lbl">Bids Received</div><div class="info-val">{{ $tender->bid_count }}</div></div>
                        <div class="info-item"><div class="info-lbl">Views</div><div class="info-val">{{ number_format($tender->view_count+1) }}</div></div>
                    </div>
                    <div class="section-title">Description</div>
                    <div class="desc-text">{{ $tender->description }}</div>
                    @if($tender->eligibility)
                    <div class="section-title">Eligibility Requirements</div>
                    <div class="desc-text">{{ $tender->eligibility }}</div>
                    @endif
                    @if($tender->contact_email)
                    <div style="margin-top:.9rem;font-size:.82rem;"><i data-lucide="mail" class="lic"></i> Questions: <a href="mailto:{{ $tender->contact_email }}" style="color:var(--green);">{{ $tender->contact_email }}</a></div>
                    @endif
                </div>
            </div>

            @if($isOwner && $bids->count() > 0)
            <div class="card">
                <div class="card-title">Bids Received ({{ $bids->count() }})</div>
                <div class="card-body" style="padding:.5rem 1rem;">
                    @foreach($bids as $bid)
                    <div class="bid-item">
                        <div style="width:36px;height:36px;border-radius:8px;background:var(--dark);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.72rem;color:var(--yellow);flex-shrink:0;">{{ strtoupper(substr($bid->co_name,0,2)) }}</div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-weight:700;font-size:.85rem;">{{ $bid->co_name }} <span style="display:inline-block;padding:1px 7px;border-radius:99px;font-size:.67rem;font-weight:700;background:var(--light-bg);color:var(--muted);">{{ ucfirst($bid->status) }}</span></div>
                            <div style="font-size:.78rem;color:var(--muted);margin-top:2px;">{{ $bid->bid_amount ? number_format($bid->bid_amount/1000000,1).'M XAF · ' : '' }}{{ date('d M Y',strtotime($bid->created_at)) }}</div>
                            <div style="font-size:.8rem;color:var(--text);margin-top:4px;">{{ Str::limit($bid->proposal, 120) }}</div>
                            @if($bid->submitted_by)<a href="/messages/{{ $bid->submitted_by }}" style="display:inline-block;margin-top:5px;font-size:.74rem;color:var(--green);font-weight:700;"><i data-lucide="message-circle" class="lic"></i> Message bidder</a>@endif
                        </div>
                        @if($bid->status === 'submitted')
                        <div style="display:flex;flex-direction:column;gap:.3rem;flex-shrink:0;">
                            <form method="POST" action="/tenders/bids/{{ $bid->id }}/shortlist">@csrf<button style="padding:.25rem .6rem;background:var(--green);color:#fff;border:none;border-radius:5px;font-size:.7rem;cursor:pointer;font-weight:700;">Shortlist</button></form>
                            <form method="POST" action="/tenders/bids/{{ $bid->id }}/reject">@csrf<button style="padding:.25rem .6rem;background:#f8d7da;color:#721c24;border:none;border-radius:5px;font-size:.7rem;cursor:pointer;font-weight:700;">Reject</button></form>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <div>
            @if(!$isOwner && $tender->status === 'open')
                @if($alreadyBid)
                <div class="bid-box" style="border-color:var(--muted);">
                    <div style="text-align:center;padding:.5rem;">
                        <div style="font-size:1.5rem;margin-bottom:.5rem;"><i data-lucide="check" class="lic"></i></div>
                        <div style="font-weight:700;color:var(--text);">Bid Submitted!</div>
                        <div style="font-size:.82rem;color:var(--muted);margin-top:.4rem;">Your bid has been submitted and is under review by the procuring entity.</div>
                    </div>
                </div>
                @elseif($authUser && $myCompanies->count() > 0)
                <div class="bid-box" id="bid">
                    <div class="bid-title">Submit Your Bid</div>
                    <form method="POST" action="/tenders/{{ $tender->id }}/bid">
                        @csrf
                        <div class="form-group">
                            <label class="form-label">Bidding as</label>
                            <select class="form-control" name="company_id" required>
                                @foreach($myCompanies as $mc)<option value="{{ $mc->id }}">{{ $mc->name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Bid Amount (XAF)</label>
                            <input type="number" class="form-control" name="bid_amount" placeholder="0">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Technical Approach</label>
                            <textarea class="form-control" name="technical_approach" placeholder="How will you deliver this?"></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Proposal Summary</label>
                            <textarea class="form-control" name="proposal" required placeholder="Summarise your offer, experience, and why you should be selected…"></textarea>
                        </div>
                        <button type="submit" class="btn-bid">Submit Bid →</button>
                    </form>
                </div>
                @elseif($authUser)
                <div class="bid-box" style="border-color:var(--muted);">
                    <div style="font-size:.85rem;color:var(--text);margin-bottom:.75rem;">You need a claimed company to submit bids.</div>
                    <a href="/" style="display:block;text-align:center;background:var(--green);color:#fff;padding:.55rem;border-radius:7px;font-size:.85rem;font-weight:700;">Browse Companies →</a>
                </div>
                @else
                <div class="bid-box" style="border-color:var(--muted);">
                    <div style="font-size:.85rem;color:var(--text);margin-bottom:.75rem;">Sign in to submit a bid.</div>
                    <a href="/auth/login" style="display:block;text-align:center;background:var(--green);color:#fff;padding:.55rem;border-radius:7px;font-size:.85rem;font-weight:700;">Sign In →</a>
                </div>
                @endif
            @endif

            <div class="side-card">
                <div style="font-weight:700;font-size:.88rem;margin-bottom:.8rem;">Tender Information</div>
                <div class="side-row"><span class="side-lbl">Status</span><span class="side-val">{{ ucfirst($tender->status) }}</span></div>
                <div class="side-row"><span class="side-lbl">Category</span><span class="side-val">{{ ucfirst($tender->category) }}</span></div>
                <div class="side-row"><span class="side-lbl">Type</span><span class="side-val">{{ $typeLabels[$tender->type]??ucfirst($tender->type) }}</span></div>
                <div class="side-row"><span class="side-lbl">Deadline</span><span class="side-val" style="{{ $daysLeft<=7&&$daysLeft>=0?'color:#856404;':'' }}">{{ date('d M Y',strtotime($tender->deadline)) }}</span></div>
                @if($tender->budget_estimate)<div class="side-row"><span class="side-lbl">Budget</span><span class="side-val">{{ number_format($tender->budget_estimate/1000000,1) }}M XAF</span></div>@endif
                @if($tender->location)<div class="side-row"><span class="side-lbl">Location</span><span class="side-val">{{ $tender->location }}</span></div>@endif
                <div class="side-row"><span class="side-lbl">Posted</span><span class="side-val">{{ date('d M Y',strtotime($tender->created_at)) }}</span></div>
            </div>

            <div class="side-card">
                <div style="display:flex;gap:.6rem;align-items:center;margin-bottom:.75rem;">
                    <div style="width:40px;height:40px;border-radius:8px;background:linear-gradient(135deg,var(--dark),var(--mid));display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.9rem;color:var(--yellow);flex-shrink:0;">{{ strtoupper(substr($company->name,0,2)) }}</div>
                    <div>
                        <div style="font-weight:800;font-size:.88rem;">{{ $company->name }}</div>
                        @if($company->verification_status==='verified')<div style="font-size:.68rem;background:#d4edda;color:#166534;padding:1px 7px;border-radius:99px;display:inline-block;font-weight:700;margin-top:1px;"><i data-lucide="check" class="lic"></i> Verified</div>@endif
                    </div>
                </div>
                <a href="/companies/{{ $company->slug }}" style="display:block;text-align:center;border:1px solid var(--border);color:var(--text);padding:.45rem;border-radius:7px;font-size:.82rem;font-weight:600;">View Company →</a>
            </div>
        </div>
    </div>
</div>
@include('partials.footer')
</body>
</html>
