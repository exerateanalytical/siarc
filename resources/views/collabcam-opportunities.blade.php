<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Opportunities — CollabCam — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:1100px;margin:0 auto;padding:1.5rem;}
.cc-tabs{display:flex;gap:.4rem;background:var(--white);border-radius:var(--radius);padding:.4rem;box-shadow:var(--shadow);margin-bottom:1.5rem;width:fit-content;}
.cc-tab{padding:.4rem 1rem;border-radius:7px;font-size:.82rem;font-weight:600;color:var(--muted);text-decoration:none;transition:all .15s;}
.cc-tab.active{background:var(--dark);color:#fff;}
.page-header{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.5rem;flex-wrap:wrap;}
.page-title{font-size:1.3rem;font-weight:900;color:var(--text);}
.page-sub{font-size:.85rem;color:var(--muted);margin-top:.25rem;}
.btn-post{background:var(--green);color:#fff;padding:.55rem 1.2rem;border-radius:8px;font-size:.85rem;font-weight:700;display:inline-block;flex-shrink:0;}
.btn-post:hover{background:#00962e;}
.filters{display:flex;gap:.75rem;flex-wrap:wrap;margin-bottom:1.2rem;}
.fi{padding:.45rem .85rem;border:1px solid var(--border);border-radius:7px;font-size:.83rem;outline:none;background:#fff;color:var(--text);}
.fi:focus{border-color:var(--green);}
.results-count{font-size:.82rem;color:var(--muted);margin-bottom:1rem;}
.opp-grid{display:flex;flex-direction:column;gap:.85rem;}
.opp-card{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);border:1px solid var(--border);padding:1.3rem;display:flex;gap:1.2rem;align-items:flex-start;border-left:3px solid var(--green);transition:box-shadow .2s;}
.opp-card:hover{box-shadow:var(--shadow-hover);}
.opp-card.featured{border-left-color:var(--yellow);background:linear-gradient(to right,#fffdf0,#fff);}
.opp-body{flex:1;min-width:0;}
.opp-top{display:flex;gap:.6rem;align-items:center;flex-wrap:wrap;margin-bottom:.5rem;}
.opp-type-badge{padding:3px 12px;border-radius:99px;font-size:.7rem;font-weight:700;background:var(--light-bg);color:var(--muted);border:1px solid var(--border);}
.opp-featured-badge{background:var(--yellow);color:var(--dark);padding:2px 8px;border-radius:99px;font-size:.65rem;font-weight:800;}
.opp-title{font-weight:800;font-size:.95rem;color:var(--text);margin-bottom:.3rem;}
.opp-desc{font-size:.82rem;color:var(--muted);line-height:1.5;margin-bottom:.6rem;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
.opp-meta-row{display:flex;gap:1.2rem;flex-wrap:wrap;font-size:.75rem;color:var(--muted);}
.opp-actions{display:flex;flex-direction:column;gap:.5rem;align-items:flex-end;flex-shrink:0;}
.btn-respond{padding:.45rem 1.1rem;background:var(--green);color:#fff;border-radius:7px;font-size:.8rem;font-weight:700;display:inline-block;white-space:nowrap;}
.btn-respond:hover{background:#00962e;}
.btn-detail{padding:.45rem 1.1rem;border:1px solid var(--border);color:var(--text);border-radius:7px;font-size:.8rem;font-weight:600;display:inline-block;white-space:nowrap;background:#fff;}
.btn-detail:hover{background:var(--light-bg);}
/* Post opportunity modal */
.modal{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:300;align-items:center;justify-content:center;padding:1rem;overflow-y:auto;}
.modal.open{display:flex;}
.modal-box{background:#fff;border-radius:14px;padding:1.8rem;width:100%;max-width:560px;box-shadow:0 16px 48px rgba(0,0,0,.2);max-height:90vh;overflow-y:auto;}
.modal-title{font-size:1.1rem;font-weight:800;margin-bottom:1.2rem;}
.form-group{margin-bottom:.9rem;}
.form-label{display:block;font-size:.8rem;font-weight:600;margin-bottom:.3rem;color:var(--text);}
.form-control{width:100%;padding:.5rem .75rem;border:1px solid var(--border);border-radius:7px;font-size:.85rem;outline:none;font-family:inherit;color:var(--text);}
.form-control:focus{border-color:var(--green);}
textarea.form-control{resize:vertical;min-height:80px;}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:.75rem;}
.form-footer{display:flex;gap:.75rem;justify-content:flex-end;margin-top:1rem;}
.btn-cancel{padding:.5rem 1.2rem;border:1px solid var(--border);background:#fff;color:var(--text);border-radius:7px;font-size:.85rem;font-weight:600;cursor:pointer;}
.btn-send{padding:.5rem 1.4rem;background:var(--green);color:#fff;border-radius:7px;font-size:.85rem;font-weight:700;cursor:pointer;border:none;}
.empty{text-align:center;padding:3rem;color:var(--muted);}
</style>

@php
$authUser = session('auth_user');
$typeFilter = request('type','');
$sectorFilter = request('sector','');
$q = request('q','');

$query = DB::table('collabcam_opportunities')
    ->join('companies','collabcam_opportunities.company_id','=','companies.id')
    ->where('collabcam_opportunities.status','active')
    ->whereNull('collabcam_opportunities.deleted_at')
    ->whereNull('companies.deleted_at')
    ->select('collabcam_opportunities.*','companies.name as company_name','companies.slug as company_slug','companies.verification_status as co_verified');

if($typeFilter) $query->where('collabcam_opportunities.type',$typeFilter);
if($sectorFilter) $query->where('collabcam_opportunities.sector',$sectorFilter);
if($q) $query->where(function($x) use($q){$x->where('title_en','like',"%$q%")->orWhere('description_en','like',"%$q%")->orWhere('companies.name','like',"%$q%");});

$opps = $query->orderByRaw('is_featured DESC')->orderByDesc('collabcam_opportunities.created_at')->paginate(15);

$typeLabels = [
    'seeking_supplier'=>'Seeking Supplier','seeking_distributor'=>'Seeking Distributor',
    'seeking_manufacturer'=>'Seeking Manufacturer','seeking_investor'=>'Seeking Investor',
    'seeking_logistics'=>'Seeking Logistics','seeking_warehouse'=>'Seeking Warehouse',
    'seeking_technology'=>'Seeking Technology','seeking_research'=>'Seeking R&D Partner',
    'seeking_export'=>'Seeking Export Partner','seeking_joint_venture'=>'Joint Venture',
    'seeking_consultant'=>'Seeking Consultant','seeking_subcontractor'=>'Seeking Subcontractor',
    'seeking_franchise'=>'Seeking Franchise','seeking_packaging'=>'Seeking Packaging',
    'seeking_processing'=>'Seeking Processing','offering_capacity'=>'Offering Capacity',
    'offering_equipment'=>'Offering Equipment','other'=>'Partnership',
];
$myCompanies = $authUser ? DB::table('company_users')
    ->join('companies','company_users.company_id','=','companies.id')
    ->where('company_users.user_id',$authUser['id'])
    ->where('company_users.status','approved')
    ->whereNull('companies.deleted_at')
    ->select('companies.id','companies.name','companies.slug')->get() : collect();
@endphp

<div class="page">
    <div class="cc-tabs">
        <a href="/collabcam" class="cc-tab">Overview</a>
        <a href="/collabcam/explore" class="cc-tab">Explore Companies</a>
        <a href="/collabcam/opportunities" class="cc-tab active">Opportunities</a>
        @if($authUser)<a href="/collabcam/hub" class="cc-tab">My Collaborations</a>@endif
    </div>

    <div class="page-header">
        <div>
            <div class="page-title">Opportunity Marketplace</div>
            <div class="page-sub">{{ $opps->total() }} active opportunities from Cameroonian companies.</div>
        </div>
        @if($authUser && $myCompanies->count() > 0)
        <button class="btn-post" onclick="document.getElementById('postModal').classList.add('open')">+ Post Opportunity</button>
        @elseif($authUser)
        <a href="/" class="btn-post" style="background:var(--muted);">Claim a company to post</a>
        @else
        <a href="/auth/login" class="btn-post">Sign in to post</a>
        @endif
    </div>

    <form method="GET" action="/collabcam/opportunities">
        <div class="filters">
            <input class="fi" type="text" name="q" value="{{ $q }}" placeholder="Search opportunities…">
            <select class="fi" name="type" onchange="this.form.submit()">
                <option value="">All types</option>
                @foreach($typeLabels as $k=>$v)<option value="{{ $k }}" {{ $typeFilter===$k?'selected':'' }}>{{ $v }}</option>@endforeach
            </select>
            <button type="submit" style="padding:.45rem 1rem;background:var(--green);color:#fff;border:none;border-radius:7px;font-size:.83rem;font-weight:600;cursor:pointer;">Search</button>
            @if($q||$typeFilter||$sectorFilter)<a href="/collabcam/opportunities" style="font-size:.8rem;color:var(--muted);align-self:center;">Clear</a>@endif
        </div>
    </form>

    <div class="results-count">{{ $opps->total() }} opportunities</div>

    @if($opps->isEmpty())
        <div class="empty">
            No opportunities found.
            @if($authUser && $myCompanies->count() > 0)
            <br><button class="btn-post" onclick="document.getElementById('postModal').classList.add('open')" style="margin-top:1rem;border:none;cursor:pointer;">+ Post the first opportunity</button>
            @endif
        </div>
    @else
        <div class="opp-grid">
            @foreach($opps as $o)
            <div class="opp-card {{ $o->is_featured?'featured':'' }}">
                <div class="opp-body">
                    <div class="opp-top">
                        <span class="opp-type-badge">{{ $typeLabels[$o->type]??ucfirst(str_replace('_',' ',$o->type)) }}</span>
                        @if($o->is_featured)<span class="opp-featured-badge"><i data-lucide="star" class="lic"></i> Featured</span>@endif
                        @if($o->sector)<span style="font-size:.7rem;color:var(--muted);">{{ ucfirst($o->sector) }}</span>@endif
                        @if($o->co_verified==='verified')<span style="font-size:.68rem;color:#166534;font-weight:700;background:#d4edda;padding:1px 7px;border-radius:99px;"><i data-lucide="check" class="lic"></i> Verified Co.</span>@endif
                    </div>
                    <div class="opp-title">{{ $o->title_en }}</div>
                    <div class="opp-desc">{{ $o->description_en }}</div>
                    <div class="opp-meta-row">
                        <span><i data-lucide="building-2" class="lic"></i> {{ $o->company_name }}</span>
                        @if($o->location)<span><i data-lucide="map-pin" class="lic"></i> {{ $o->location }}</span>@endif
                        @if($o->budget_range)<span><i data-lucide="banknote" class="lic"></i> {{ $o->budget_range }}</span>@endif
                        @if($o->deadline)<span>⏰ {{ date('d M Y',strtotime($o->deadline)) }}</span>@endif
                        <span><i data-lucide="eye" class="lic"></i> {{ $o->view_count }} views · {{ $o->response_count }} responses</span>
                    </div>
                </div>
                <div class="opp-actions">
                    @if($authUser)
                    <a href="/collabcam/opportunities/{{ $o->id }}" class="btn-respond">Respond →</a>
                    @else
                    <a href="/auth/login" class="btn-respond">Sign in</a>
                    @endif
                    <a href="/collabcam/opportunities/{{ $o->id }}" class="btn-detail">Details</a>
                </div>
            </div>
            @endforeach
        </div>
        <div style="margin-top:1.5rem;">{{ $opps->appends(request()->query())->links() }}</div>
    @endif
</div>

{{-- Post Opportunity Modal --}}
<div class="modal" id="postModal">
    <div class="modal-box">
        <div class="modal-title">Post a Collaboration Opportunity</div>
        <form method="POST" action="/collabcam/opportunities">
            @csrf
            <div class="form-group">
                <label class="form-label">Your company</label>
                <select class="form-control" name="company_id" required>
                    @foreach($myCompanies as $mc)
                    <option value="{{ $mc->id }}">{{ $mc->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Opportunity title</label>
                <input type="text" class="form-control" name="title_en" placeholder="e.g. Seeking cocoa processing partner in Littoral region" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Type</label>
                    <select class="form-control" name="type" required>
                        @foreach($typeLabels as $k=>$v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Sector</label>
                    <input type="text" class="form-control" name="sector" placeholder="e.g. Agriculture, ICT, Logistics…">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea class="form-control" name="description_en" placeholder="Describe what you need, your requirements, and what an ideal partner looks like…" required></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Budget range (optional)</label>
                    <input type="text" class="form-control" name="budget_range" placeholder="e.g. 5M–20M XAF/month">
                </div>
                <div class="form-group">
                    <label class="form-label">Location</label>
                    <input type="text" class="form-control" name="location" placeholder="e.g. Douala, Nationwide">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Deadline (optional)</label>
                <input type="date" class="form-control" name="deadline">
            </div>
            <div class="form-footer">
                <button type="button" class="btn-cancel" onclick="document.getElementById('postModal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn-send">Post Opportunity →</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('postModal').addEventListener('click', function(e) {
    if(e.target === this) this.classList.remove('open');
});
</script>
@include('partials.footer')
</body>
</html>
