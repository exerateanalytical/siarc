<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Tender & Procurement Portal — Galerie virtuelle de l'artisanat du Cameroun</title>
<meta name="description" content="Public and private tenders, RFQs, and procurement opportunities from Cameroonian companies.">
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:1100px;margin:0 auto;padding:1.5rem;}
.hero{background:linear-gradient(135deg,#0a1628,#1a2d4a);border-radius:var(--radius);padding:2.5rem 2rem;color:#fff;margin-bottom:2rem;position:relative;overflow:hidden;}
.hero::after{content:'';position:absolute;right:-40px;bottom:-40px;width:250px;height:250px;border-radius:50%;background:rgba(0,122,51,.15);}
.hero-title{font-size:1.8rem;font-weight:900;margin-bottom:.5rem;}
.hero-sub{color:#aab;font-size:.9rem;max-width:560px;}
.h-stats{display:flex;gap:2rem;margin-top:1.5rem;flex-wrap:wrap;}
.h-stat-val{font-size:1.4rem;font-weight:800;color:var(--yellow);}
.h-stat-lbl{font-size:.72rem;color:#8899aa;}
.filters{display:flex;gap:.6rem;flex-wrap:wrap;margin-bottom:1.2rem;align-items:center;}
.fi{padding:.42rem .85rem;border:1px solid var(--border);border-radius:7px;font-size:.83rem;outline:none;background:#fff;color:var(--text);}
.fi:focus{border-color:var(--green);}
.post-btn{padding:.42rem 1.1rem;background:var(--green);color:#fff;border:none;border-radius:7px;font-size:.83rem;font-weight:700;cursor:pointer;text-decoration:none;display:inline-block;}
.post-btn:hover{background:#00962e;}
.results-bar{display:flex;justify-content:space-between;align-items:center;font-size:.82rem;color:var(--muted);margin-bottom:.9rem;}
.tender-list{display:flex;flex-direction:column;gap:.8rem;}
.tender-card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);padding:1.3rem;border:1px solid var(--border);display:flex;gap:1rem;align-items:flex-start;transition:box-shadow .2s;}
.tender-card:hover{box-shadow:0 4px 20px rgba(0,0,0,.12);}
.tc-icon{width:48px;height:48px;border-radius:9px;background:linear-gradient(135deg,var(--dark),var(--mid));display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;}
.tc-body{flex:1;min-width:0;}
.tc-title{font-weight:800;font-size:.95rem;color:var(--text);margin-bottom:.3rem;}
.tc-company{font-size:.78rem;color:var(--muted);margin-bottom:.5rem;}
.tc-meta{display:flex;gap:1rem;font-size:.75rem;color:var(--muted);flex-wrap:wrap;}
.badge{display:inline-block;padding:2px 9px;border-radius:99px;font-size:.68rem;font-weight:700;border:1px solid var(--border);}
.badge-open{background:#d4edda;color:#166534;border-color:#b8dbc4;}
.badge-closed{background:#f8d7da;color:#721c24;border-color:#f5c6cb;}
.badge-awarded{background:#cce5ff;color:#0056b3;border-color:#b3d7ff;}
.badge-cat{background:var(--light-bg);color:var(--muted);}
.tc-deadline{font-size:.75rem;font-weight:700;color:var(--text);}
.tc-footer{margin-top:.7rem;display:flex;gap:.5rem;align-items:center;}
.btn-sm{padding:.3rem .8rem;border-radius:6px;font-size:.76rem;font-weight:600;display:inline-block;}
.btn-green{background:var(--green);color:#fff;}
.btn-outline{border:1px solid var(--border);color:var(--text);}
.empty{text-align:center;padding:3rem;color:var(--muted);}
.cat-icons{goods:'package',services:'settings',works:'hard-hat',consultancy:'briefcase',ict:'laptop',agriculture:'wheat',construction:'hammer',other:'clipboard-list'}
.modal-backdrop{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:200;align-items:center;justify-content:center;}
.modal-backdrop.show{display:flex;}
.modal{background:#fff;border-radius:12px;padding:2rem;width:100%;max-width:560px;max-height:90vh;overflow-y:auto;}
.modal-title{font-size:1.05rem;font-weight:800;color:var(--text);margin-bottom:1.2rem;}
.form-group{margin-bottom:.9rem;}
.form-label{display:block;font-size:.8rem;font-weight:600;margin-bottom:.3rem;color:var(--text);}
.form-control{width:100%;padding:.5rem .75rem;border:1px solid var(--border);border-radius:7px;font-size:.85rem;outline:none;font-family:inherit;color:var(--text);box-sizing:border-box;}
.form-control:focus{border-color:var(--green);}
textarea.form-control{resize:vertical;min-height:90px;}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:.75rem;}
.form-footer{display:flex;gap:.75rem;justify-content:flex-end;margin-top:1.2rem;}
.btn-cancel{padding:.5rem 1.2rem;border:1px solid var(--border);background:#fff;color:var(--text);border-radius:7px;font-size:.85rem;font-weight:600;cursor:pointer;}
.btn-send{padding:.5rem 1.4rem;background:var(--green);color:#fff;border-radius:7px;font-size:.85rem;font-weight:700;cursor:pointer;border:none;}
@media(max-width:640px){.tender-card{flex-direction:column;}.h-stats{gap:1rem;}.form-row{grid-template-columns:1fr;}}
</style>

@php
$authUser = session('auth_user');
$q        = request('q','');
$category = request('category','');
$status   = request('status','open');
$type     = request('type','');
$query = DB::table('tenders')
    ->join('companies','tenders.company_id','=','companies.id')
    ->whereNull('tenders.deleted_at')
    ->where('tenders.is_public',1);
if($q) $query->where('tenders.title','like',"%$q%");
if($category) $query->where('tenders.category',$category);
if($status) $query->where('tenders.status',$status);
if($type) $query->where('tenders.type',$type);
$tenders = $query->select('tenders.*','companies.name as co_name','companies.slug as co_slug')
    ->orderBy('tenders.deadline')->paginate(20);
$total   = DB::table('tenders')->where('is_public',1)->whereNull('deleted_at')->count();
$openCount  = DB::table('tenders')->where('is_public',1)->where('status','open')->whereNull('deleted_at')->count();
$closingThisWeek = DB::table('tenders')->where('status','open')->where('deadline','<=', now()->addDays(7))->whereNull('deleted_at')->count();
$catIcons = ['goods'=>'package','services'=>'settings','works'=>'hard-hat','consultancy'=>'briefcase','ict'=>'laptop','agriculture'=>'wheat','construction'=>'hammer','other'=>'clipboard-list'];
$typeLabels = ['open'=>'Open Tender','restricted'=>'Restricted','rfq'=>'RFQ','rfp'=>'RFP','rfi'=>'RFI','expression_of_interest'=>'EOI','sole_source'=>'Sole Source'];
$myCompanies = $authUser ? DB::table('company_users')
    ->join('companies','company_users.company_id','=','companies.id')
    ->where('company_users.user_id',$authUser['id'])
    ->where('company_users.status','approved')
    ->whereNull('companies.deleted_at')
    ->select('companies.id','companies.name')->get() : collect();
@endphp

<div class="page">
    <div class="hero">
        <div style="position:relative;z-index:1;">
            <div style="display:inline-block;background:rgba(0,122,51,.3);border:1px solid rgba(0,122,51,.5);color:var(--yellow);padding:3px 12px;border-radius:99px;font-size:.72rem;font-weight:700;margin-bottom:.7rem;letter-spacing:.5px;">PROCUREMENT PORTAL</div>
            <div class="hero-title">Tender & Procurement Hub</div>
            <div class="hero-sub">Discover public and private tenders, submit bids, and win contracts from Cameroonian companies and government agencies.</div>
            <div class="h-stats">
                <div><div class="h-stat-val">{{ $total }}</div><div class="h-stat-lbl">Total Tenders</div></div>
                <div><div class="h-stat-val">{{ $openCount }}</div><div class="h-stat-lbl">Open Now</div></div>
                <div><div class="h-stat-val">{{ $closingThisWeek }}</div><div class="h-stat-lbl">Closing This Week</div></div>
            </div>
        </div>
    </div>

    <form method="GET" action="/tenders">
        <div class="filters">
            <input class="fi" type="text" name="q" value="{{ $q }}" placeholder="Search tenders…" style="min-width:200px;">
            <select class="fi" name="category" onchange="this.form.submit()">
                <option value="">All categories</option>
                @foreach(['goods','services','works','consultancy','ict','agriculture','construction','other'] as $c)
                    <option value="{{ $c }}" {{ $category===$c?'selected':'' }}>{{ ucfirst($c) }}</option>
                @endforeach
            </select>
            <select class="fi" name="status" onchange="this.form.submit()">
                <option value="">All statuses</option>
                @foreach(['open','closed','awarded','cancelled'] as $s)
                    <option value="{{ $s }}" {{ $status===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <select class="fi" name="type" onchange="this.form.submit()">
                <option value="">All types</option>
                @foreach($typeLabels as $k=>$v)
                    <option value="{{ $k }}" {{ $type===$k?'selected':'' }}>{{ $v }}</option>
                @endforeach
            </select>
            <button type="submit" class="post-btn" style="background:var(--mid);">Search</button>
            @if($authUser && $myCompanies->count() > 0)
                <button type="button" class="post-btn" onclick="document.getElementById('postModal').classList.add('show')">+ Post Tender</button>
            @endif
        </div>
    </form>

    <div class="results-bar">
        <span>Showing <strong>{{ $tenders->count() }}</strong> tenders</span>
        @if($q || $category || $status || $type)
            <a href="/tenders" style="font-size:.8rem;color:var(--muted);">Clear filters</a>
        @endif
    </div>

    @if($tenders->isEmpty())
        <div class="empty">No tenders found. <a href="/tenders" style="color:var(--green);">Clear filters →</a></div>
    @else
        <div class="tender-list">
        @foreach($tenders as $t)
            <div class="tender-card">
                <div class="tc-icon"><i data-lucide="{{ $catIcons[$t->category]??'clipboard-list' }}" class="lic"></i></div>
                <div class="tc-body">
                    <div style="display:flex;align-items:flex-start;gap:.6rem;flex-wrap:wrap;margin-bottom:.25rem;">
                        <div class="tc-title"><a href="/tenders/{{ $t->id }}" style="color:var(--text);">{{ $t->title }}</a></div>
                        <span class="badge badge-{{ $t->status }}">{{ ucfirst($t->status) }}</span>
                        <span class="badge badge-cat">{{ $typeLabels[$t->type]??ucfirst($t->type) }}</span>
                    </div>
                    <div class="tc-company">{{ $t->co_name }}{{ $t->location ? ' · '.$t->location : '' }}</div>
                    <div class="tc-meta">
                        <span><i data-lucide="calendar" class="lic"></i> Deadline: <strong class="tc-deadline">{{ date('d M Y',strtotime($t->deadline)) }}</strong></span>
                        @if($t->budget_estimate)<span><i data-lucide="banknote" class="lic"></i> Budget: {{ number_format($t->budget_estimate/1000000,1) }}M {{ $t->currency }}</span>@endif
                        <span><i data-lucide="eye" class="lic"></i> {{ number_format($t->view_count) }} views</span>
                        <span><i data-lucide="files" class="lic"></i> {{ $t->bid_count }} bids</span>
                        <span class="badge badge-cat">{{ ucfirst($t->category) }}</span>
                    </div>
                    <div class="tc-footer">
                        <a href="/tenders/{{ $t->id }}" class="btn-sm btn-green">View Details</a>
                        @if($t->status === 'open')<a href="/tenders/{{ $t->id }}#bid" class="btn-sm btn-outline">Submit Bid</a>@endif
                    </div>
                </div>
            </div>
        @endforeach
        </div>
        <div style="margin-top:1.5rem;">{{ $tenders->withQueryString()->links() }}</div>
    @endif
</div>

{{-- Post Tender Modal --}}
@if($authUser && $myCompanies->count() > 0)
<div class="modal-backdrop" id="postModal">
    <div class="modal">
        <div class="modal-title">Post a Tender</div>
        <form method="POST" action="/tenders">
            @csrf
            <div class="form-group">
                <label class="form-label">Procuring Company</label>
                <select class="form-control" name="company_id" required>
                    @foreach($myCompanies as $mc)<option value="{{ $mc->id }}">{{ $mc->name }}</option>@endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Tender Title</label>
                <input type="text" class="form-control" name="title" placeholder="e.g. Supply of Office Equipment 2026" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select class="form-control" name="category" required>
                        @foreach(['goods','services','works','consultancy','ict','agriculture','construction','other'] as $c)
                            <option value="{{ $c }}">{{ ucfirst($c) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Type</label>
                    <select class="form-control" name="type" required>
                        @foreach($typeLabels as $k=>$v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Budget Estimate (XAF)</label>
                    <input type="number" class="form-control" name="budget_estimate" placeholder="e.g. 50000000">
                </div>
                <div class="form-group">
                    <label class="form-label">Deadline</label>
                    <input type="date" class="form-control" name="deadline" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Location</label>
                <input type="text" class="form-control" name="location" placeholder="e.g. Douala, Littoral">
            </div>
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea class="form-control" name="description" required placeholder="Scope of work, requirements, and specifications…"></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Eligibility Requirements</label>
                <textarea class="form-control" name="eligibility" placeholder="Who can bid? Any prequalification criteria?"></textarea>
            </div>
            <div class="form-footer">
                <button type="button" class="btn-cancel" onclick="document.getElementById('postModal').classList.remove('show')">Cancel</button>
                <button type="submit" class="btn-send">Publish Tender →</button>
            </div>
        </form>
    </div>
</div>
@endif

@include('partials.footer')
</body>
</html>
