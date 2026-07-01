<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Investment Marketplace — Galerie virtuelle de l'artisanat du Cameroun</title>
<meta name="description" content="Find investment opportunities and connect with investors, banks, and development finance institutions in Cameroon.">
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:1100px;margin:0 auto;padding:1.5rem;}
.hero{background:linear-gradient(135deg,#0c1f0c,#1a3a1a);border-radius:var(--radius);padding:2.5rem 2rem;color:#fff;margin-bottom:2rem;position:relative;overflow:hidden;}
.hero::after{content:'';position:absolute;right:-50px;top:-50px;width:280px;height:280px;border-radius:50%;background:rgba(252,209,22,.06);}
.hero-title{font-size:1.8rem;font-weight:900;margin-bottom:.5rem;}
.hero-sub{color:#aab;font-size:.9rem;max-width:560px;}
.h-stats{display:flex;gap:2rem;margin-top:1.5rem;flex-wrap:wrap;}
.h-stat-val{font-size:1.4rem;font-weight:800;color:var(--yellow);}
.h-stat-lbl{font-size:.72rem;color:#8899aa;}
.type-tabs{display:flex;gap:.4rem;flex-wrap:wrap;margin-bottom:1.2rem;}
.type-tab{padding:.35rem .9rem;border-radius:99px;font-size:.78rem;font-weight:600;cursor:pointer;border:1px solid var(--border);background:#fff;color:var(--muted);text-decoration:none;transition:all .15s;}
.type-tab.active,.type-tab:hover{background:var(--dark);color:#fff;border-color:var(--dark);}
.filters{display:flex;gap:.6rem;flex-wrap:wrap;margin-bottom:1rem;align-items:center;}
.fi{padding:.42rem .85rem;border:1px solid var(--border);border-radius:7px;font-size:.83rem;outline:none;background:#fff;color:var(--text);}
.fi:focus{border-color:var(--green);}
.post-btn{padding:.42rem 1.1rem;background:var(--green);color:#fff;border:none;border-radius:7px;font-size:.83rem;font-weight:700;cursor:pointer;text-decoration:none;display:inline-block;}
.seek-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(330px,1fr));gap:1.1rem;}
.seek-card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);padding:1.4rem;border:1px solid var(--border);transition:box-shadow .2s,transform .15s;position:relative;}
.seek-card:hover{box-shadow:0 6px 24px rgba(0,0,0,.12);transform:translateY(-2px);}
.seek-type{display:inline-block;padding:3px 10px;border-radius:99px;font-size:.68rem;font-weight:700;background:var(--light-bg);color:var(--muted);border:1px solid var(--border);margin-bottom:.5rem;}
.seek-title{font-weight:800;font-size:.95rem;color:var(--text);line-height:1.35;margin-bottom:.3rem;}
.seek-co{font-size:.78rem;color:var(--muted);margin-bottom:.7rem;}
.seek-amount{font-size:1.15rem;font-weight:900;color:var(--green);}
.seek-equity{font-size:.75rem;color:var(--muted);margin-top:1px;}
.seek-meta{display:flex;gap:.8rem;font-size:.74rem;color:var(--muted);margin-top:.7rem;flex-wrap:wrap;}
.seek-desc{font-size:.82rem;color:var(--muted);line-height:1.5;margin:.6rem 0;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
.seek-footer{margin-top:.9rem;padding-top:.75rem;border-top:1px solid var(--border);display:flex;gap:.5rem;}
.btn-sm{padding:.32rem .8rem;border-radius:6px;font-size:.76rem;font-weight:600;display:inline-block;}
.btn-green{background:var(--green);color:#fff;}
.btn-outline{border:1px solid var(--border);color:var(--text);}
.featured-badge{position:absolute;top:.75rem;right:.75rem;background:var(--yellow);color:var(--dark);font-size:.64rem;font-weight:800;padding:2px 8px;border-radius:99px;}
.empty{text-align:center;padding:3rem;color:var(--muted);}
.modal-backdrop{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:200;align-items:center;justify-content:center;}
.modal-backdrop.show{display:flex;}
.modal{background:#fff;border-radius:12px;padding:2rem;width:100%;max-width:580px;max-height:90vh;overflow-y:auto;}
.modal-title{font-size:1.05rem;font-weight:800;margin-bottom:1.2rem;}
.form-group{margin-bottom:.9rem;}
.form-label{display:block;font-size:.8rem;font-weight:600;margin-bottom:.3rem;}
.form-control{width:100%;padding:.5rem .75rem;border:1px solid var(--border);border-radius:7px;font-size:.85rem;outline:none;font-family:inherit;box-sizing:border-box;}
.form-control:focus{border-color:var(--green);}
textarea.form-control{resize:vertical;min-height:80px;}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:.75rem;}
.form-footer{display:flex;gap:.75rem;justify-content:flex-end;margin-top:1.2rem;}
.btn-cancel{padding:.5rem 1.2rem;border:1px solid var(--border);background:#fff;border-radius:7px;font-size:.85rem;font-weight:600;cursor:pointer;}
.btn-send{padding:.5rem 1.4rem;background:var(--green);color:#fff;border-radius:7px;font-size:.85rem;font-weight:700;cursor:pointer;border:none;}
@media(max-width:640px){.seek-grid{grid-template-columns:1fr;}.h-stats{gap:1rem;}.form-row{grid-template-columns:1fr;}}
</style>

@php
$authUser = session('auth_user');
$q        = request('q','');
$type     = request('type','');
$sector   = request('sector','');
$query = DB::table('invest_seeks')
    ->join('companies','invest_seeks.company_id','=','companies.id')
    ->whereNull('invest_seeks.deleted_at')
    ->where('invest_seeks.status','open');
if($q) $query->where('invest_seeks.title','like',"%$q%");
if($type) $query->where('invest_seeks.type',$type);
if($sector) $query->where('invest_seeks.sector',$sector);
$seeks = $query->select('invest_seeks.*','companies.name as co_name','companies.slug as co_slug','companies.verification_status as co_verified')
    ->orderByRaw('is_featured DESC')->orderByDesc('invest_seeks.created_at')->paginate(12);
$total = DB::table('invest_seeks')->where('status','open')->whereNull('deleted_at')->count();
$totalValue = DB::table('invest_seeks')->where('status','open')->whereNull('deleted_at')->sum('amount_sought');
$typeLabels = ['equity'=>'Equity','debt'=>'Debt','grant'=>'Grant','convertible_note'=>'Convertible Note','revenue_sharing'=>'Revenue Sharing','joint_venture'=>'Joint Venture','angel'=>'Angel Round','seed'=>'Seed','series_a'=>'Series A','series_b'=>'Series B','ipo_prep'=>'IPO Prep','government_fund'=>'Gov. Fund','development_finance'=>'Dev. Finance'];
$sectorLabels = ['agriculture'=>'Agriculture','industry'=>'Industry','ict'=>'ICT','finance'=>'Finance','health'=>'Health','construction'=>'Construction','transport'=>'Transport','mining'=>'Mining','tourism'=>'Tourism','energy'=>'Energy','education'=>'Education','other'=>'Other'];
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
            <div style="display:inline-block;background:rgba(252,209,22,.15);border:1px solid rgba(252,209,22,.3);color:var(--yellow);padding:3px 12px;border-radius:99px;font-size:.72rem;font-weight:700;margin-bottom:.7rem;">INVESTMENT MARKETPLACE</div>
            <div class="hero-title">Connect Capital with Opportunity</div>
            <div class="hero-sub">Cameroonian companies seeking investment. Investors, banks, development finance, angel rounds, government funds, and equity partnerships.</div>
            <div class="h-stats">
                <div><div class="h-stat-val">{{ $total }}</div><div class="h-stat-lbl">Open Opportunities</div></div>
                <div><div class="h-stat-val">{{ number_format($totalValue/1000000000,1) }}B</div><div class="h-stat-lbl">XAF Sought</div></div>
                <div><div class="h-stat-val">{{ count($typeLabels) }}</div><div class="h-stat-lbl">Investment Types</div></div>
            </div>
        </div>
    </div>

    <div class="type-tabs">
        <a href="/invest-hub" class="type-tab {{ !$type?'active':'' }}">All Types</a>
        @foreach($typeLabels as $k=>$v)
            <a href="/invest-hub?type={{ $k }}{{ $q?'&q='.urlencode($q):'' }}" class="type-tab {{ $type===$k?'active':'' }}">{{ $v }}</a>
        @endforeach
    </div>

    <form method="GET" action="/invest-hub">
        <div class="filters">
            <input type="hidden" name="type" value="{{ $type }}">
            <input class="fi" type="text" name="q" value="{{ $q }}" placeholder="Search investment opportunities…" style="min-width:220px;">
            <select class="fi" name="sector" onchange="this.form.submit()">
                <option value="">All sectors</option>
                @foreach($sectorLabels as $k=>$v)<option value="{{ $k }}" {{ $sector===$k?'selected':'' }}>{{ $v }}</option>@endforeach
            </select>
            <button type="submit" class="post-btn" style="background:var(--mid);">Search</button>
            @if($authUser && $myCompanies->count() > 0)
                <button type="button" class="post-btn" onclick="document.getElementById('postModal').classList.add('show')">+ List Your Opportunity</button>
            @endif
        </div>
    </form>

    @if($seeks->isEmpty())
        <div class="empty">No investment opportunities found. <a href="/invest-hub" style="color:var(--green);">Clear filters →</a></div>
    @else
        <div class="seek-grid">
        @foreach($seeks as $s)
            <div class="seek-card">
                @if($s->is_featured)<div class="featured-badge"><i data-lucide="star" class="lic"></i> Featured</div>@endif
                <span class="seek-type">{{ $typeLabels[$s->type]??ucfirst(str_replace('_',' ',$s->type)) }}</span>
                <div class="seek-title"><a href="/invest-hub/{{ $s->id }}" style="color:var(--text);">{{ $s->title }}</a></div>
                <div class="seek-co">
                    <a href="/companies/{{ $s->co_slug }}" style="color:var(--green);font-weight:600;">{{ $s->co_name }}</a>
                    {{ $s->co_verified==='verified' ? ' · <i data-lucide="check" class="lic"></i> Verified' : '' }}
                    · {{ ucfirst($s->sector) }}
                </div>
                <div class="seek-amount">{{ number_format($s->amount_sought/1000000,1) }}M {{ $s->currency }}</div>
                @if($s->equity_offered)<div class="seek-equity">{{ $s->equity_offered }}% equity offered</div>@endif
                <div class="seek-desc">{{ $s->description }}</div>
                <div class="seek-meta">
                    <span><i data-lucide="eye" class="lic"></i> {{ number_format($s->view_count) }}</span>
                    <span><i data-lucide="handshake" class="lic"></i> {{ $s->interest_count }} expressions</span>
                    @if($s->deadline)<span><i data-lucide="calendar" class="lic"></i> {{ date('d M Y',strtotime($s->deadline)) }}</span>@endif
                </div>
                <div class="seek-footer">
                    <a href="/invest-hub/{{ $s->id }}" class="btn-sm btn-green">View Details</a>
                    <a href="/invest-hub/{{ $s->id }}#express" class="btn-sm btn-outline">Express Interest</a>
                </div>
            </div>
        @endforeach
        </div>
        <div style="margin-top:1.5rem;">{{ $seeks->withQueryString()->links() }}</div>
    @endif
</div>

@if($authUser && $myCompanies->count() > 0)
<div class="modal-backdrop" id="postModal">
    <div class="modal">
        <div class="modal-title">List Investment Opportunity</div>
        <form method="POST" action="/invest-hub">
            @csrf
            <div class="form-group">
                <label class="form-label">Company Seeking Investment</label>
                <select class="form-control" name="company_id" required>
                    @foreach($myCompanies as $mc)<option value="{{ $mc->id }}">{{ $mc->name }}</option>@endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Opportunity Title</label>
                <input type="text" class="form-control" name="title" placeholder="e.g. Series A — AgriTech Expansion" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Investment Type</label>
                    <select class="form-control" name="type" required>
                        @foreach($typeLabels as $k=>$v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Sector</label>
                    <select class="form-control" name="sector" required>
                        @foreach($sectorLabels as $k=>$v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Amount Sought (XAF)</label>
                    <input type="number" class="form-control" name="amount_sought" required placeholder="e.g. 500000000">
                </div>
                <div class="form-group">
                    <label class="form-label">Equity Offered (%)</label>
                    <input type="number" class="form-control" name="equity_offered" step="0.1" placeholder="e.g. 25">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea class="form-control" name="description" required placeholder="Describe your company, market opportunity, and growth strategy…"></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Use of Funds</label>
                <textarea class="form-control" name="use_of_funds" placeholder="e.g. Equipment 40%, Working capital 30%, Expansion 30%"></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Traction / Key Metrics</label>
                <textarea class="form-control" name="traction" placeholder="Revenue, customers, growth rate…"></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Deadline</label>
                <input type="date" class="form-control" name="deadline">
            </div>
            <div class="form-footer">
                <button type="button" class="btn-cancel" onclick="document.getElementById('postModal').classList.remove('show')">Cancel</button>
                <button type="submit" class="btn-send">Publish →</button>
            </div>
        </form>
    </div>
</div>
@endif

@include('partials.footer')
</body>
</html>
