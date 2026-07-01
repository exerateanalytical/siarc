<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>My Collaborations — CollabCam — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:1100px;margin:0 auto;padding:1.5rem;}
.cc-tabs{display:flex;gap:.4rem;background:var(--white);border-radius:var(--radius);padding:.4rem;box-shadow:var(--shadow);margin-bottom:1.5rem;width:fit-content;}
.cc-tab{padding:.4rem 1rem;border-radius:7px;font-size:.82rem;font-weight:600;color:var(--muted);text-decoration:none;transition:all .15s;}
.cc-tab.active{background:var(--dark);color:#fff;}
.stats-row{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem;}
.stat-card{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);padding:1.1rem;text-align:center;}
.stat-val{font-size:1.6rem;font-weight:800;color:var(--text);}
.stat-lbl{font-size:.75rem;color:var(--muted);margin-top:3px;}
.grid2{display:grid;grid-template-columns:1fr 340px;gap:1.5rem;}
.card{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);margin-bottom:1rem;overflow:hidden;}
.card-title{padding:.8rem 1.1rem;font-weight:700;font-size:.88rem;border-bottom:1px solid var(--border);background:var(--light-bg);display:flex;justify-content:space-between;align-items:center;}
.card-body{padding:1rem;}
.collab-row{padding:.85rem 1rem;border-bottom:1px solid var(--border);display:flex;gap:.85rem;align-items:flex-start;}
.collab-row:last-child{border-bottom:none;}
.collab-icon{width:40px;height:40px;border-radius:9px;background:linear-gradient(135deg,var(--dark),var(--mid));display:flex;align-items:center;justify-content:center;font-size:.9rem;color:var(--yellow);flex-shrink:0;}
.collab-name{font-weight:700;font-size:.88rem;color:var(--text);}
.collab-meta{font-size:.74rem;color:var(--muted);margin-top:2px;}
.status-badge{display:inline-block;padding:1px 8px;border-radius:99px;font-size:.68rem;font-weight:700;}
.st-active{background:#d4edda;color:#166534;}
.st-draft{background:#e2e8f0;color:#64748b;}
.st-paused{background:#fff3cd;color:#856404;}
.st-completed{background:#cce5ff;color:#0056b3;}
.st-terminated{background:#f8d7da;color:#721c24;}
.st-pending{background:#fff3cd;color:#856404;}
.st-accepted{background:#d4edda;color:#166534;}
.st-rejected{background:#f8d7da;color:#721c24;}
.req-row{padding:.75rem 1rem;border-bottom:1px solid var(--border);display:flex;gap:.75rem;align-items:flex-start;}
.req-row:last-child{border-bottom:none;}
.req-info{flex:1;min-width:0;}
.req-subject{font-weight:700;font-size:.85rem;color:var(--text);}
.req-meta{font-size:.74rem;color:var(--muted);margin-top:2px;}
.req-actions{display:flex;gap:.4rem;flex-shrink:0;}
.btn-accept{padding:.3rem .75rem;background:var(--green);color:#fff;border:none;border-radius:6px;font-size:.74rem;font-weight:700;cursor:pointer;}
.btn-accept:hover{background:#00962e;}
.btn-reject{padding:.3rem .75rem;background:var(--light-bg);color:var(--muted);border:1px solid var(--border);border-radius:6px;font-size:.74rem;font-weight:600;cursor:pointer;}
.btn-reject:hover{background:#f8d7da;color:#721c24;border-color:#f5c6cb;}
.opp-row{padding:.75rem 1rem;border-bottom:1px solid var(--border);display:flex;gap:.75rem;align-items:center;}
.opp-row:last-child{border-bottom:none;}
.opp-title-sm{font-weight:700;font-size:.83rem;color:var(--text);flex:1;}
.opp-responses{font-size:.74rem;color:var(--muted);}
.empty-sm{padding:1.2rem;text-align:center;color:var(--muted);font-size:.82rem;}
@media(max-width:700px){.stats-row{grid-template-columns:1fr 1fr;}.grid2{grid-template-columns:1fr;}}
</style>

@php
$authUser = session('auth_user');
$userId = $authUser['id'];

// My companies
$myCompanyIds = DB::table('company_users')
    ->where('user_id',$userId)->where('status','approved')
    ->pluck('company_id')->toArray();

// Active collaborations I'm part of
$collabs = DB::table('collabcam_collaborations')
    ->join('collabcam_collaboration_members as m','collabcam_collaborations.id','=','m.collaboration_id')
    ->whereIn('m.company_id', count($myCompanyIds)?$myCompanyIds:['__none__'])
    ->where('m.status','active')
    ->whereNull('collabcam_collaborations.deleted_at')
    ->select('collabcam_collaborations.*')
    ->distinct()->get();

// Collaboration requests received
$receivedRequests = DB::table('collabcam_requests')
    ->join('companies as fc','collabcam_requests.from_company_id','=','fc.id')
    ->whereIn('collabcam_requests.to_company_id', count($myCompanyIds)?$myCompanyIds:['__none__'])
    ->where('collabcam_requests.status','pending')
    ->select('collabcam_requests.*','fc.name as from_name','fc.slug as from_slug')
    ->orderByDesc('collabcam_requests.created_at')->get();

// Collaboration requests sent
$sentRequests = DB::table('collabcam_requests')
    ->join('companies as tc','collabcam_requests.to_company_id','=','tc.id')
    ->whereIn('collabcam_requests.from_company_id', count($myCompanyIds)?$myCompanyIds:['__none__'])
    ->whereIn('collabcam_requests.status',['pending','accepted','rejected'])
    ->select('collabcam_requests.*','tc.name as to_name','tc.slug as to_slug')
    ->orderByDesc('collabcam_requests.created_at')->limit(10)->get();

// My opportunities
$myOpps = DB::table('collabcam_opportunities')
    ->whereIn('company_id', count($myCompanyIds)?$myCompanyIds:['__none__'])
    ->whereNull('deleted_at')
    ->orderByDesc('created_at')->limit(10)->get();

$typeLabels = ['supply_chain'=>'Supply Chain','joint_venture'=>'Joint Venture','distribution'=>'Distribution','manufacturing'=>'Manufacturing','research'=>'R&D','export'=>'Export','logistics'=>'Logistics','processing'=>'Processing','packaging'=>'Packaging','other'=>'Partnership'];
@endphp

<div class="page">
    <div class="cc-tabs">
        <a href="/collabcam" class="cc-tab">Overview</a>
        <a href="/collabcam/explore" class="cc-tab">Explore Companies</a>
        <a href="/collabcam/opportunities" class="cc-tab">Opportunities</a>
        <a href="/collabcam/hub" class="cc-tab active">My Collaborations</a>
    </div>

    <div style="font-size:1.2rem;font-weight:900;color:var(--text);margin-bottom:1.2rem;">My CollabCam Hub</div>

    @if(count($myCompanyIds) === 0)
    <div style="background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);padding:2.5rem;text-align:center;">
        <div style="font-size:2rem;margin-bottom:.75rem;"><i data-lucide="handshake" class="lic"></i></div>
        <div style="font-weight:800;font-size:1.05rem;margin-bottom:.5rem;">You don't have any companies yet</div>
        <div style="font-size:.85rem;color:var(--muted);margin-bottom:1.5rem;">To use CollabCam, you need a claimed company. Browse our company directory and claim your business listing.</div>
        <a href="/" style="background:var(--green);color:#fff;padding:.65rem 1.5rem;border-radius:8px;font-size:.88rem;font-weight:700;display:inline-block;">Browse Companies →</a>
    </div>
    @else

    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-val">{{ $collabs->count() }}</div>
            <div class="stat-lbl">Active Collabs</div>
        </div>
        <div class="stat-card">
            <div class="stat-val">{{ $receivedRequests->count() }}</div>
            <div class="stat-lbl">Pending Requests</div>
        </div>
        <div class="stat-card">
            <div class="stat-val">{{ $myOpps->where('status','active')->count() }}</div>
            <div class="stat-lbl">Active Opportunities</div>
        </div>
        <div class="stat-card">
            <div class="stat-val">{{ DB::table('collabcam_contracts')->join('collabcam_collaborations','collabcam_contracts.collaboration_id','=','collabcam_collaborations.id')->join('collabcam_collaboration_members as m','collabcam_collaborations.id','=','m.collaboration_id')->whereIn('m.company_id',$myCompanyIds)->whereNull('collabcam_contracts.deleted_at')->distinct()->count('collabcam_contracts.id') }}</div>
            <div class="stat-lbl">Contracts</div>
        </div>
    </div>

    <div class="grid2">
        <div>
            {{-- Active Collaborations --}}
            <div class="card">
                <div class="card-title">Active Collaborations <a href="/collabcam/explore" style="font-size:.75rem;color:var(--green);font-weight:400;">+ New</a></div>
                @if($collabs->isEmpty())
                <div class="empty-sm">No active collaborations yet.<br><a href="/collabcam/explore" style="color:var(--green);">Find companies to collaborate with →</a></div>
                @else
                @foreach($collabs as $c)
                <div class="collab-row">
                    <div class="collab-icon">{{ strtoupper(substr($typeLabels[$c->type]??'C',0,1)) }}</div>
                    <div style="flex:1;min-width:0;">
                        <div class="collab-name">{{ $c->name }}</div>
                        <div class="collab-meta">{{ $typeLabels[$c->type]??ucfirst(str_replace('_',' ',$c->type)) }}@if($c->sector) · {{ ucfirst($c->sector) }}@endif · <span class="status-badge st-{{ $c->status }}">{{ ucfirst($c->status) }}</span></div>
                        @if($c->milestones_total > 0)
                        <div style="margin-top:.4rem;background:var(--light-bg);border-radius:99px;height:4px;overflow:hidden;"><div style="width:{{ round($c->milestones_completed/$c->milestones_total*100) }}%;background:var(--green);height:100%;border-radius:99px;"></div></div>
                        <div style="font-size:.68rem;color:var(--muted);margin-top:2px;">{{ $c->milestones_completed }}/{{ $c->milestones_total }} milestones</div>
                        @endif
                    </div>
                    <a href="/collabcam/workspace/{{ $c->id }}" style="font-size:.78rem;color:var(--green);font-weight:700;white-space:nowrap;flex-shrink:0;align-self:center;">Open →</a>
                </div>
                @endforeach
                @endif
            </div>

            {{-- Received Requests --}}
            <div class="card">
                <div class="card-title">Incoming Requests <span style="background:{{ $receivedRequests->count()?'var(--red)':'var(--border)' }};color:{{ $receivedRequests->count()?'#fff':'var(--muted)' }};padding:1px 8px;border-radius:99px;font-size:.7rem;font-weight:700;">{{ $receivedRequests->count() }}</span></div>
                @if($receivedRequests->isEmpty())
                <div class="empty-sm">No pending requests.</div>
                @else
                @foreach($receivedRequests as $r)
                <div class="req-row">
                    <div class="req-info">
                        <div class="req-subject">{{ $r->subject }}</div>
                        <div class="req-meta">From <a href="/companies/{{ $r->from_slug }}" style="color:var(--green);">{{ $r->from_name }}</a> · {{ $typeLabels[$r->collab_type]??ucfirst(str_replace('_',' ',$r->collab_type)) }} · {{ date('d M',strtotime($r->created_at)) }}</div>
                        <div style="font-size:.74rem;color:var(--muted);margin-top:2px;font-style:italic;">"{{ Str::limit($r->message,100) }}"</div>
                    </div>
                    <div class="req-actions">
                        <form method="POST" action="/collabcam/requests/{{ $r->id }}/accept" style="display:inline;">@csrf<button class="btn-accept"><i data-lucide="check" class="lic"></i> Accept</button></form>
                        <form method="POST" action="/collabcam/requests/{{ $r->id }}/reject" style="display:inline;">@csrf<button class="btn-reject"><i data-lucide="x" class="lic"></i></button></form>
                    </div>
                </div>
                @endforeach
                @endif
            </div>
        </div>

        <div>
            {{-- Sent Requests --}}
            <div class="card">
                <div class="card-title">Sent Requests</div>
                @if($sentRequests->isEmpty())
                <div class="empty-sm">No requests sent yet.<br><a href="/collabcam/explore" style="color:var(--green);">Find companies to collaborate with →</a></div>
                @else
                @foreach($sentRequests as $r)
                <div class="req-row">
                    <div class="req-info">
                        <div class="req-subject" style="font-size:.82rem;">{{ $r->subject }}</div>
                        <div class="req-meta">To <a href="/companies/{{ $r->to_slug }}" style="color:var(--green);">{{ $r->to_name }}</a> · {{ date('d M',strtotime($r->created_at)) }}</div>
                    </div>
                    <span class="status-badge st-{{ $r->status }}" style="flex-shrink:0;">{{ ucfirst($r->status) }}</span>
                </div>
                @endforeach
                @endif
            </div>

            {{-- My Opportunities --}}
            <div class="card">
                <div class="card-title">My Opportunities <a href="/collabcam/opportunities" style="font-size:.75rem;color:var(--green);font-weight:400;">Browse all</a></div>
                @if($myOpps->isEmpty())
                <div class="empty-sm">No opportunities posted yet.</div>
                @else
                @foreach($myOpps as $o)
                <div class="opp-row">
                    <div class="opp-title-sm">{{ Str::limit($o->title_en,45) }}</div>
                    <div class="opp-responses">{{ $o->response_count }} resp.</div>
                    <span class="status-badge st-{{ $o->status }}">{{ ucfirst($o->status) }}</span>
                </div>
                @endforeach
                @endif
            </div>
        </div>
    </div>
    @endif
</div>
@include('partials.footer')
</body>
</html>
