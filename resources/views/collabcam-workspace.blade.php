<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>{{ $collab->name }} — CollabCam Workspace — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:1100px;margin:0 auto;padding:1.5rem;}
.back{font-size:.82rem;color:var(--muted);margin-bottom:1rem;display:inline-flex;align-items:center;gap:.3rem;}
.back:hover{color:var(--green);}
.ws-hero{background:linear-gradient(135deg,var(--dark),var(--mid));border-radius:var(--radius);padding:1.8rem 2rem;color:#fff;margin-bottom:1.5rem;}
.ws-title{font-size:1.3rem;font-weight:900;margin-bottom:.35rem;}
.ws-meta{display:flex;gap:1.2rem;flex-wrap:wrap;font-size:.8rem;color:#99aabb;margin-top:.5rem;}
.ws-badge{padding:3px 12px;border-radius:99px;font-size:.72rem;font-weight:700;background:rgba(255,255,255,.12);color:#dde;}
.ws-badge-active{background:rgba(0,122,51,.35);color:#6ee7b7;}
.ws-progress{margin-top:1.2rem;background:rgba(255,255,255,.12);border-radius:99px;height:6px;overflow:hidden;}
.ws-progress-bar{background:var(--green);height:100%;border-radius:99px;transition:width .5s;}
.ws-progress-text{font-size:.72rem;color:#8899aa;margin-top:.35rem;}
.grid2{display:grid;grid-template-columns:1fr 320px;gap:1.5rem;}
.card{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);margin-bottom:1rem;overflow:hidden;}
.card-title{padding:.8rem 1.1rem;font-weight:700;font-size:.88rem;border-bottom:1px solid var(--border);background:var(--light-bg);display:flex;justify-content:space-between;align-items:center;}
.card-body{padding:1rem;}
/* Members */
.member-row{display:flex;gap:.75rem;align-items:center;padding:.6rem 0;border-bottom:1px solid var(--border);}
.member-row:last-child{border-bottom:none;}
.member-logo{width:38px;height:38px;border-radius:8px;background:linear-gradient(135deg,var(--dark),var(--mid));display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.8rem;color:var(--yellow);flex-shrink:0;}
.member-name{font-weight:700;font-size:.85rem;color:var(--text);}
.member-role{font-size:.72rem;color:var(--muted);}
.member-status{margin-left:auto;flex-shrink:0;}
/* Contracts */
.contract-row{padding:.8rem 0;border-bottom:1px solid var(--border);display:flex;gap:.75rem;align-items:flex-start;}
.contract-row:last-child{border-bottom:none;}
.contract-icon{font-size:1.4rem;flex-shrink:0;}
.contract-title{font-weight:700;font-size:.85rem;color:var(--text);}
.contract-meta{font-size:.73rem;color:var(--muted);margin-top:2px;}
.c-status{display:inline-block;padding:1px 8px;border-radius:99px;font-size:.67rem;font-weight:700;}
.c-draft{background:#e2e8f0;color:#64748b;}
.c-under_review{background:#fff3cd;color:#856404;}
.c-pending_signatures{background:#cce5ff;color:#0056b3;}
.c-signed,.c-active{background:#d4edda;color:#166534;}
.c-expired,.c-terminated{background:#f8d7da;color:#721c24;}
/* Milestones */
.milestone-row{display:flex;gap:.75rem;align-items:flex-start;padding:.65rem 0;border-bottom:1px solid var(--border);}
.milestone-row:last-child{border-bottom:none;}
.milestone-check{width:20px;height:20px;border-radius:50%;border:2px solid var(--border);flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:.75rem;margin-top:1px;}
.milestone-check.done{background:var(--green);border-color:var(--green);color:#fff;}
.milestone-name{font-size:.85rem;font-weight:600;color:var(--text);flex:1;}
.milestone-due{font-size:.72rem;color:var(--muted);}
.m-status{font-size:.68rem;font-weight:700;padding:1px 6px;border-radius:99px;margin-left:auto;}
.m-pending{background:#e2e8f0;color:#64748b;}
.m-in_progress{background:#fff3cd;color:#856404;}
.m-completed{background:#d4edda;color:#166534;}
.m-overdue{background:#f8d7da;color:#721c24;}
/* Add forms */
.form-inline{display:flex;gap:.5rem;flex-wrap:wrap;padding:.75rem 1rem;border-top:1px solid var(--border);background:var(--light-bg);}
.fi-sm{padding:.38rem .65rem;border:1px solid var(--border);border-radius:6px;font-size:.8rem;outline:none;flex:1;min-width:130px;color:var(--text);}
.fi-sm:focus{border-color:var(--green);}
.btn-add{padding:.38rem .9rem;background:var(--green);color:#fff;border:none;border-radius:6px;font-size:.78rem;font-weight:700;cursor:pointer;white-space:nowrap;}
.btn-add:hover{background:#00962e;}
/* Status badges */
.st-active{background:#d4edda;color:#166534;} .st-draft{background:#e2e8f0;color:#64748b;} .st-paused{background:#fff3cd;color:#856404;} .st-pending{background:#fff3cd;color:#856404;} .st-initiator{background:rgba(0,122,51,.12);color:var(--green);}
.status-badge{display:inline-block;padding:1px 8px;border-radius:99px;font-size:.68rem;font-weight:700;}
.empty-sm{padding:1.2rem;text-align:center;color:var(--muted);font-size:.82rem;}
@media(max-width:700px){.grid2{grid-template-columns:1fr;}}
</style>

@php
$authUser = session('auth_user');
$typeLabels = ['supply_chain'=>'Supply Chain','joint_venture'=>'Joint Venture','distribution'=>'Distribution','manufacturing'=>'Manufacturing','research'=>'R&D','export'=>'Export','logistics'=>'Logistics','processing'=>'Processing','packaging'=>'Packaging','agri_value_chain'=>'Agri Value Chain','technology'=>'Technology','other'=>'Partnership'];
$contractTypeLabels = ['nda'=>'NDA','mou'=>'MoU','supply_agreement'=>'Supply Agreement','distribution'=>'Distribution','manufacturing'=>'Manufacturing','joint_venture'=>'Joint Venture','consulting'=>'Consulting','agency'=>'Agency','licensing'=>'Licensing','framework'=>'Framework','service_agreement'=>'Service Agreement','other'=>'Contract'];
$pct = $collab->milestones_total > 0 ? round($collab->milestones_completed / $collab->milestones_total * 100) : 0;
@endphp

<div class="page">
    <a class="back" href="/collabcam/hub">← My Collaborations</a>

    <div class="ws-hero">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
            <div>
                <div class="ws-title">{{ $collab->name }}</div>
                @if($collab->description)
                <div style="font-size:.85rem;color:#aab;margin-top:.3rem;">{{ $collab->description }}</div>
                @endif
                <div class="ws-meta">
                    <span class="ws-badge ws-badge-{{ $collab->status }}">{{ ucfirst($collab->status) }}</span>
                    <span class="ws-badge">{{ $typeLabels[$collab->type]??ucfirst(str_replace('_',' ',$collab->type)) }}</span>
                    @if($collab->sector)<span class="ws-badge">{{ ucfirst($collab->sector) }}</span>@endif
                    @if($collab->start_date)<span>Started {{ date('d M Y',strtotime($collab->start_date)) }}</span>@endif
                    <span>{{ $members->count() }} partners</span>
                </div>
            </div>
            <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
                @if($collab->status === 'active')
                <form method="POST" action="/collabcam/workspace/{{ $collab->id }}/pause">@csrf<button style="background:rgba(255,255,255,.12);color:#fff;border:none;border-radius:7px;padding:.4rem .85rem;font-size:.78rem;font-weight:600;cursor:pointer;">⏸ Pause</button></form>
                @elseif($collab->status === 'paused')
                <form method="POST" action="/collabcam/workspace/{{ $collab->id }}/resume">@csrf<button style="background:var(--green);color:#fff;border:none;border-radius:7px;padding:.4rem .85rem;font-size:.78rem;font-weight:600;cursor:pointer;">▶ Resume</button></form>
                @endif
            </div>
        </div>
        @if($collab->milestones_total > 0)
        <div class="ws-progress"><div class="ws-progress-bar" style="width:{{ $pct }}%;"></div></div>
        <div class="ws-progress-text">{{ $pct }}% complete · {{ $collab->milestones_completed }}/{{ $collab->milestones_total }} milestones done</div>
        @endif
    </div>

    <div class="grid2">
        <div>
            {{-- Contracts --}}
            <div class="card">
                <div class="card-title">Contracts & Agreements <span style="font-size:.72rem;color:var(--muted);">{{ $contracts->count() }} total</span></div>
                <div class="card-body" style="padding:.5rem 1rem;">
                    @if($contracts->isEmpty())
                    <div class="empty-sm">No contracts yet. Add your first agreement below.</div>
                    @else
                    @foreach($contracts as $ct)
                    <div class="contract-row">
                        <div class="contract-icon"><i data-lucide="file-text" class="lic"></i></div>
                        <div style="flex:1;min-width:0;">
                            <div class="contract-title">{{ $ct->title }}</div>
                            <div class="contract-meta">
                                {{ $contractTypeLabels[$ct->type]??ucfirst(str_replace('_',' ',$ct->type)) }}
                                · <span class="c-status c-{{ $ct->status }}">{{ ucfirst(str_replace('_',' ',$ct->status)) }}</span>
                                @if($ct->effective_date) · Effective {{ date('d M Y',strtotime($ct->effective_date)) }}@endif
                                @if($ct->expiry_date) · Expires {{ date('d M Y',strtotime($ct->expiry_date)) }}@endif
                            </div>
                            @if($ct->all_signed)<div style="font-size:.7rem;color:var(--green);font-weight:700;margin-top:2px;"><i data-lucide="check" class="lic"></i> All parties signed</div>@endif
                        </div>
                        <div style="display:flex;flex-direction:column;gap:.3rem;align-items:flex-end;flex-shrink:0;">
                            @if(!$ct->all_signed && $ct->status !== 'terminated')
                            <form method="POST" action="/collabcam/contracts/{{ $ct->id }}/sign">@csrf<button style="padding:.28rem .65rem;background:var(--green);color:#fff;border:none;border-radius:5px;font-size:.7rem;font-weight:700;cursor:pointer;">Sign</button></form>
                            @endif
                        </div>
                    </div>
                    @endforeach
                    @endif
                </div>
                <form method="POST" action="/collabcam/workspace/{{ $collab->id }}/contract" class="form-inline">
                    @csrf
                    <input class="fi-sm" type="text" name="title" placeholder="Contract title…" required style="flex:2;">
                    <select class="fi-sm" name="type">
                        @foreach($contractTypeLabels as $k=>$v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                    </select>
                    <input class="fi-sm" type="date" name="effective_date" style="flex:0.8;">
                    <button class="btn-add" type="submit">+ Add</button>
                </form>
            </div>

            {{-- Milestones --}}
            <div class="card">
                <div class="card-title">Milestones <span style="font-size:.72rem;color:var(--muted);">{{ $milestones->count() }} total</span></div>
                <div class="card-body" style="padding:.5rem 1rem;">
                    @if($milestones->isEmpty())
                    <div class="empty-sm">No milestones yet. Add project milestones below.</div>
                    @else
                    @foreach($milestones->sortBy('sort_order') as $m)
                    <div class="milestone-row">
                        <div class="milestone-check {{ $m->status==='completed'?'done':'' }}">{{ $m->status==='completed'?'check':'' }}</div>
                        <div style="flex:1;min-width:0;">
                            <div class="milestone-name">{{ $m->title }}</div>
                            @if($m->description)<div style="font-size:.74rem;color:var(--muted);margin-top:1px;">{{ $m->description }}</div>@endif
                        </div>
                        @if($m->due_date)<div class="milestone-due">{{ date('d M',strtotime($m->due_date)) }}</div>@endif
                        <span class="m-status m-{{ $m->status }}">{{ ucfirst(str_replace('_',' ',$m->status)) }}</span>
                        @if($m->status !== 'completed')
                        <form method="POST" action="/collabcam/milestones/{{ $m->id }}/complete">@csrf<button style="padding:.22rem .55rem;background:var(--green);color:#fff;border:none;border-radius:5px;font-size:.68rem;cursor:pointer;font-weight:700;"><i data-lucide="check" class="lic"></i></button></form>
                        @endif
                    </div>
                    @endforeach
                    @endif
                </div>
                <form method="POST" action="/collabcam/workspace/{{ $collab->id }}/milestone" class="form-inline">
                    @csrf
                    <input class="fi-sm" type="text" name="title" placeholder="Milestone title…" required style="flex:2;">
                    <input class="fi-sm" type="date" name="due_date" style="flex:0.8;">
                    <button class="btn-add" type="submit">+ Add</button>
                </form>
            </div>
        </div>

        <div>
            {{-- Partners --}}
            <div class="card">
                <div class="card-title">Partners ({{ $members->count() }})</div>
                <div class="card-body" style="padding:.5rem 1rem;">
                    @foreach($members as $m)
                    <div class="member-row">
                        <div class="member-logo">{{ strtoupper(substr($m->company_name,0,2)) }}</div>
                        <div style="flex:1;min-width:0;">
                            <div class="member-name"><a href="/companies/{{ $m->company_slug }}" style="color:var(--text);">{{ $m->company_name }}</a></div>
                            <div class="member-role">{{ ucfirst($m->role) }}</div>
                        </div>
                        <div class="member-status"><span class="status-badge st-{{ $m->member_status }}">{{ ucfirst($m->member_status) }}</span></div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Summary info --}}
            <div class="card">
                <div class="card-title">Collaboration Info</div>
                <div class="card-body">
                    <div style="font-size:.82rem;display:flex;flex-direction:column;gap:.5rem;">
                        <div style="display:flex;justify-content:space-between;"><span style="color:var(--muted);">Type</span><span style="font-weight:600;">{{ $typeLabels[$collab->type]??ucfirst(str_replace('_',' ',$collab->type)) }}</span></div>
                        <div style="display:flex;justify-content:space-between;"><span style="color:var(--muted);">Status</span><span class="status-badge st-{{ $collab->status }}">{{ ucfirst($collab->status) }}</span></div>
                        @if($collab->sector)<div style="display:flex;justify-content:space-between;"><span style="color:var(--muted);">Sector</span><span style="font-weight:600;">{{ ucfirst($collab->sector) }}</span></div>@endif
                        @if($collab->start_date)<div style="display:flex;justify-content:space-between;"><span style="color:var(--muted);">Started</span><span>{{ date('d M Y',strtotime($collab->start_date)) }}</span></div>@endif
                        @if($collab->end_date)<div style="display:flex;justify-content:space-between;"><span style="color:var(--muted);">End date</span><span>{{ date('d M Y',strtotime($collab->end_date)) }}</span></div>@endif
                        <div style="display:flex;justify-content:space-between;"><span style="color:var(--muted);">Contracts</span><span style="font-weight:600;">{{ $contracts->count() }}</span></div>
                        <div style="display:flex;justify-content:space-between;"><span style="color:var(--muted);">Milestones</span><span style="font-weight:600;">{{ $collab->milestones_completed }}/{{ $collab->milestones_total }}</span></div>
                    </div>
                </div>
            </div>

            <div style="text-align:center;margin-top:.5rem;">
                <a href="/collabcam/hub" style="font-size:.82rem;color:var(--muted);">← Back to Hub</a>
                &nbsp;·&nbsp;
                <a href="/collabcam/explore" style="font-size:.82rem;color:var(--green);">Explore more companies</a>
            </div>
        </div>
    </div>
</div>
@include('partials.footer')
</body>
</html>
