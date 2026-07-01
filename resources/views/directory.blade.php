<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Company Directory — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.hero{background:linear-gradient(135deg,var(--dark) 0%,var(--mid) 100%);padding:2.5rem 2rem 2rem;text-align:center;color:#fff;}
.hero h1{font-size:1.8rem;font-weight:800;margin-bottom:.3rem;}
.hero p{color:#aab;font-size:.95rem;margin-bottom:1.4rem;}
.search-form{display:flex;gap:.5rem;max-width:660px;margin:0 auto .9rem;background:rgba(255,255,255,.08);padding:.45rem;border-radius:11px;}
.search-form input{flex:1;padding:.6rem 1rem;border:none;border-radius:7px;font-size:.92rem;background:rgba(255,255,255,.15);color:#fff;outline:none;}
.search-form input::placeholder{color:rgba(255,255,255,.45);}
.search-form input:focus{background:rgba(255,255,255,.22);}
.btn-search{padding:.6rem 1.4rem;background:var(--green);color:#fff;border:none;border-radius:7px;font-weight:600;cursor:pointer;font-size:.88rem;}
.btn-search:hover{background:#00962e;}
.filters-row{display:flex;gap:.5rem;justify-content:center;flex-wrap:wrap;}
.filter-select{padding:.4rem .85rem;border:1px solid rgba(255,255,255,.2);border-radius:7px;background:rgba(255,255,255,.1);color:#fff;font-size:.82rem;cursor:pointer;outline:none;}
.filter-select option{background:var(--dark);color:#fff;}
.clear-link{color:var(--yellow);font-size:.82rem;display:flex;align-items:center;gap:3px;}
.stats-bar{background:var(--white);border-bottom:1px solid var(--border);padding:.6rem 2rem;display:flex;align-items:center;gap:1.2rem;flex-wrap:wrap;}
.stat{display:flex;align-items:center;gap:.35rem;font-size:.82rem;}
.stat-dot{width:8px;height:8px;border-radius:50%;}
.stats-right{margin-left:auto;font-size:.8rem;color:var(--muted);}
.main{max-width:1280px;margin:0 auto;padding:1.5rem;}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(290px,1fr));gap:1.1rem;}
.card{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden;display:flex;flex-direction:column;transition:transform .17s,box-shadow .17s;position:relative;}
.card:hover{transform:translateY(-3px);box-shadow:var(--shadow-hover);}
.card-featured{border-top:3px solid var(--yellow);}
.featured-badge{position:absolute;top:11px;right:11px;background:var(--yellow);color:#7a5a00;font-size:.63rem;font-weight:800;padding:2px 7px;border-radius:99px;text-transform:uppercase;letter-spacing:.4px;}
.card-header{padding:1.1rem 1.1rem .5rem;display:flex;gap:.75rem;align-items:flex-start;}
.logo{width:48px;height:48px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;font-weight:800;color:#fff;flex-shrink:0;text-transform:uppercase;}
.company-name{font-size:.95rem;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;line-height:1.3;}
.company-trade{font-size:.77rem;color:var(--muted);margin-top:1px;}
.card-body{padding:.3rem 1.1rem .7rem;flex:1;}
.company-desc{font-size:.8rem;color:var(--muted);line-height:1.5;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
.card-tags{padding:.35rem 1.1rem .45rem;display:flex;gap:.35rem;flex-wrap:wrap;}
.tag{font-size:.7rem;padding:2px 7px;border-radius:99px;font-weight:600;}
.tag-legal{background:#eef2f7;color:#4a5568;}
.tag-region{background:#e8f5e9;color:#2e7d32;}
.card-footer{padding:.6rem 1.1rem;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;}
.vbadge{font-size:.7rem;font-weight:700;padding:2px 8px;border-radius:99px;text-transform:uppercase;letter-spacing:.2px;}
.vs-certified{background:#d4edda;color:#007a33;}
.vs-verified{background:#cce5ff;color:#0056b3;}
.vs-basic{background:#fff3cd;color:#856404;}
.vs-unverified{background:#f8f9fa;color:#6c757d;}
.card-views{font-size:.73rem;color:var(--muted);}
.watchlist-btn{background:none;border:none;cursor:pointer;padding:0 2px;display:inline-flex;align-items:center;}
.watchlist-btn [data-lucide],.watchlist-btn svg{width:18px;height:18px;color:#ccc;}
.watchlist-btn.saved [data-lucide],.watchlist-btn.saved svg{color:#e03131;fill:#e03131;}
.pagination{display:flex;justify-content:center;gap:.35rem;margin-top:2rem;flex-wrap:wrap;}
.page-btn{padding:.42rem .82rem;border:1px solid var(--border);border-radius:7px;background:var(--white);color:var(--text);font-size:.83rem;transition:background .14s;}
.page-btn:hover{background:var(--light-bg);}
.page-btn.active{background:var(--green);color:#fff;border-color:var(--green);font-weight:600;}
.empty-state{text-align:center;padding:4rem 2rem;color:var(--muted);}
@media(max-width:600px){.hero{padding:1.6rem 1rem;}.hero h1{font-size:1.45rem;line-height:1.2;}.hero p{font-size:.85rem;margin-bottom:1rem;}.search-form{flex-direction:column;max-width:100%;}.filters-row{gap:.4rem;}.filter-select{flex:1 1 calc(50% - .25rem);min-width:0;}.grid{grid-template-columns:1fr;}.main{padding:1rem;}.stats-bar{padding:.6rem 1rem;gap:.7rem;}.stats-right{margin-left:0;width:100%;}}
</style>

<div class="hero">
    <h1>Galerie virtuelle de l'artisanat du Cameroun</h1>
    <p>Search {{ number_format($total) }} registered companies across all 10 regions</p>
    <form class="search-form" method="GET" action="/">
        <input type="text" name="q" placeholder="Search by name, RCCM, keyword…" value="{{ $search }}">
        @if($region)<input type="hidden" name="region" value="{{ $region }}">@endif
        @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
        <button type="submit" class="btn-search">Search</button>
    </form>
    <div class="filters-row">
        <form method="GET" action="/" id="ff">
            @if($search)<input type="hidden" name="q" value="{{ $search }}">@endif
            <select class="filter-select" name="region" onchange="ff.submit()">
                <option value="">All Regions</option>
                @foreach($regions as $r)<option value="{{ $r->id }}" {{ $region == $r->id ? 'selected' : '' }}>{{ $r->name_en }}</option>@endforeach
            </select>
            <select class="filter-select" name="industry" onchange="ff.submit()">
                <option value="">All Industries</option>
                @foreach($industries as $ind)<option value="{{ $ind->id }}" {{ $industry == $ind->id ? 'selected' : '' }}>{{ $ind->name_en }}</option>@endforeach
            </select>
            <select class="filter-select" name="status" onchange="ff.submit()">
                <option value="">All Status</option>
                <option value="certified" {{ $status==='certified'?'selected':'' }}>Certified</option>
                <option value="verified"  {{ $status==='verified'?'selected':'' }}>Verified</option>
                <option value="basic"     {{ $status==='basic'?'selected':'' }}>Basic</option>
                <option value="unverified"{{ $status==='unverified'?'selected':'' }}>Unverified</option>
            </select>
            <select class="filter-select" name="sort" onchange="ff.submit()">
                <option value="featured" {{ $sort==='featured'?'selected':'' }}>Featured First</option>
                <option value="views"    {{ $sort==='views'?'selected':'' }}>Most Viewed</option>
                <option value="rating"   {{ $sort==='rating'?'selected':'' }}>Top Rated</option>
                <option value="newest"   {{ $sort==='newest'?'selected':'' }}>Newest</option>
                <option value="name"     {{ $sort==='name'?'selected':'' }}>A–Z</option>
            </select>
        </form>
        @if($search||$region||$status||$industry)<a href="/" class="clear-link"><i data-lucide="x" class="lic"></i> Clear</a>@endif
    </div>
</div>

<div class="stats-bar">
    <div class="stat"><span class="stat-dot" style="background:#007a33"></span>Certified</div>
    <div class="stat"><span class="stat-dot" style="background:#0056b3"></span>Verified</div>
    <div class="stat"><span class="stat-dot" style="background:#e67e22"></span>Basic</div>
    <div class="stats-right">Showing {{ $companies->count() }} of {{ number_format($total) }} companies{{ $search ? ' · "'.$search.'"' : '' }}</div>
</div>

<div class="main">
    @if($companies->isEmpty())
        <div class="empty-state"><i data-lucide="search-x" style="width:42px;height:42px;color:var(--muted);margin-bottom:.6rem;"></i><h3>No companies found</h3><p>Try a different search or clear filters.</p></div>
    @else
        <div class="grid">
            @foreach($companies as $c)
                @php
                    $ini = strtoupper(substr($c->trade_name ?: $c->name, 0, 2));
                    $clrs = ['#007a33','#ce1126','#0056b3','#7b2d8b','#c0392b','#16a085','#d35400','#2c3e50'];
                    $clr = $clrs[crc32($c->id) % count($clrs)];
                    $lm = ['sarl'=>'SARL','sa'=>'SA','snc'=>'SNC','scs'=>'SCS','ge'=>'GE','association'=>'ASBL','cooperative'=>'COOP','other'=>'EP'];
                    $vsC = match($c->verification_status){
                        'certified'=>'vs-certified','verified'=>'vs-verified','basic'=>'vs-basic',default=>'vs-unverified'};
                    $vsI = match($c->verification_status){'certified'=>'badge-check','verified'=>'check','basic'=>'circle-dot',default=>'minus'};
                @endphp
                <a class="card {{ $c->is_featured?'card-featured':'' }}" href="/companies/{{ $c->slug }}">
                    @if($c->is_featured)<span class="featured-badge"><i data-lucide="star" style="width:10px;height:10px;display:inline;vertical-align:-1px;"></i> Featured</span>@endif
                    <div class="card-header">
                        <div class="logo" style="background:{{ $clr }}">{{ $ini }}</div>
                        <div style="min-width:0;flex:1">
                            <div class="company-name">{{ $c->name }}</div>
                            @if($c->trade_name && $c->trade_name !== $c->name)<div class="company-trade">{{ $c->trade_name }}</div>@endif
                        </div>
                    </div>
                    <div class="card-body"><p class="company-desc">{{ $c->description_en ?: $c->description_fr }}</p></div>
                    <div class="card-tags">
                        <span class="tag tag-legal">{{ $lm[$c->legal_form] ?? strtoupper($c->legal_form) }}</span>
                        @if($c->region_name)<span class="tag tag-region"><i data-lucide="map-pin" style="width:11px;height:11px;display:inline;vertical-align:-1px;"></i> {{ $c->city_name ?: $c->region_name }}</span>@endif
                        @if($c->incorporation_date)<span class="tag tag-legal">Est. {{ date('Y',strtotime($c->incorporation_date)) }}</span>@endif
                    </div>
                    <div class="card-footer">
                        <span class="vbadge {{ $vsC }}"><i data-lucide="{{ $vsI }}" style="width:12px;height:12px;display:inline;vertical-align:-2px;"></i> {{ ucfirst($c->verification_status) }}</span>
                        <div style="display:flex;align-items:center;gap:.5rem;">
                            <span class="card-views"><i data-lucide="eye" style="width:13px;height:13px;display:inline;vertical-align:-2px;"></i> {{ number_format($c->view_count) }}</span>
                            @if(session('auth_user'))
                            <button class="watchlist-btn {{ in_array($c->id, $watchlistIds??[]) ? 'saved' : '' }}" data-company-id="{{ $c->id }}" onclick="event.preventDefault();toggleWatchlist(this,'{{ $c->id }}')" title="Save to watchlist"><i data-lucide="heart"></i></button>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
        @if($totalPages > 1)
            <div class="pagination">
                @if($page>1)<a class="page-btn" href="?{{ http_build_query(array_merge(request()->query(),['page'=>$page-1])) }}">← Prev</a>@endif
                @for($i=max(1,$page-2);$i<=min($totalPages,$page+2);$i++)
                    <a class="page-btn {{ $i===$page?'active':'' }}" href="?{{ http_build_query(array_merge(request()->query(),['page'=>$i])) }}">{{ $i }}</a>
                @endfor
                @if($page<$totalPages)<a class="page-btn" href="?{{ http_build_query(array_merge(request()->query(),['page'=>$page+1])) }}">Next →</a>@endif
            </div>
        @endif
    @endif
</div>
<script>
function toggleWatchlist(btn, companyId) {
    var token = '{{ csrf_token() }}';
    btn.style.opacity = '0.5';
    fetch('/watchlist/toggle', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':token,'Accept':'application/json'},
        body: JSON.stringify({company_id: companyId})
    })
    .then(r => r.json())
    .then(data => {
        btn.style.opacity = '1';
        if (data.watching || data.added) { btn.classList.add('saved'); }
        else { btn.classList.remove('saved'); }
    })
    .catch(() => { btn.style.opacity = '1'; });
}
</script>
@include('partials.footer')
</body>
</html>
