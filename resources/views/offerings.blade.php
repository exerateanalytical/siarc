<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Share Offerings — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.hero{background:linear-gradient(135deg,var(--dark) 0%,#1a3a5c 100%);padding:2.5rem 2rem 2rem;text-align:center;color:#fff;}
.hero h1{font-size:1.8rem;font-weight:800;margin-bottom:.3rem;}
.hero p{color:#aab;font-size:.95rem;margin-bottom:1.4rem;}
.search-form{display:flex;gap:.5rem;max-width:660px;margin:0 auto .9rem;background:rgba(255,255,255,.08);padding:.45rem;border-radius:11px;}
.search-form input{flex:1;padding:.6rem 1rem;border:none;border-radius:7px;font-size:.92rem;background:rgba(255,255,255,.15);color:#fff;outline:none;}
.search-form input::placeholder{color:rgba(255,255,255,.45);}
.btn-search{padding:.6rem 1.4rem;background:var(--green);color:#fff;border:none;border-radius:7px;font-weight:600;cursor:pointer;font-size:.88rem;}
.filters-row{display:flex;gap:.5rem;justify-content:center;flex-wrap:wrap;}
.filter-select{padding:.4rem .85rem;border:1px solid rgba(255,255,255,.2);border-radius:7px;background:rgba(255,255,255,.1);color:#fff;font-size:.82rem;cursor:pointer;outline:none;}
.filter-select option{background:var(--dark);color:#fff;}
.clear-link{color:var(--yellow);font-size:.82rem;display:flex;align-items:center;}
.stats-bar{background:var(--white);border-bottom:1px solid var(--border);padding:.6rem 2rem;display:flex;align-items:center;flex-wrap:wrap;gap:.8rem;}
.stat{display:flex;align-items:center;gap:.35rem;font-size:.82rem;}
.stat-dot{width:8px;height:8px;border-radius:50%;}
.stats-right{margin-left:auto;font-size:.8rem;color:var(--muted);}
.main{max-width:1280px;margin:0 auto;padding:1.5rem;}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:1.1rem;}
.card{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden;display:flex;flex-direction:column;transition:transform .17s,box-shadow .17s;}
.card:hover{transform:translateY(-3px);box-shadow:var(--shadow-hover);}
.card-status-bar{height:4px;}
.card-header{padding:1rem 1.1rem .5rem;display:flex;gap:.75rem;align-items:flex-start;}
.logo{width:44px;height:44px;border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;font-weight:800;color:#fff;flex-shrink:0;text-transform:uppercase;}
.offering-title{font-size:.92rem;font-weight:700;line-height:1.35;color:var(--text);}
.offering-company{font-size:.77rem;color:var(--muted);margin-top:2px;}
.card-body{padding:.3rem 1.1rem .5rem;flex:1;}
.offering-desc{font-size:.79rem;color:var(--muted);line-height:1.5;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
.progress-section{padding:.5rem 1.1rem;}
.progress-label{display:flex;justify-content:space-between;font-size:.75rem;color:var(--muted);margin-bottom:4px;}
.progress-bar{height:6px;background:#eee;border-radius:99px;overflow:hidden;}
.progress-fill{height:100%;border-radius:99px;transition:width .3s;}
.card-metrics{padding:.4rem 1.1rem;display:grid;grid-template-columns:1fr 1fr 1fr;gap:.4rem;}
.metric{text-align:center;padding:.35rem;background:var(--light-bg);border-radius:6px;}
.metric-val{font-size:.85rem;font-weight:700;color:var(--text);}
.metric-lbl{font-size:.65rem;color:var(--muted);margin-top:1px;}
.card-footer{padding:.6rem 1.1rem;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;}
.status-badge{font-size:.7rem;font-weight:700;padding:2px 8px;border-radius:99px;text-transform:uppercase;}
.st-open{background:#d4edda;color:#007a33;}
.st-cmf_approved{background:#cce5ff;color:#0056b3;}
.st-pending_cmf{background:#fff3cd;color:#856404;}
.st-closed,.st-completed{background:#f8f9fa;color:#6c757d;}
.st-draft{background:#f8f9fa;color:#aaa;}
.st-paused{background:#ffeeba;color:#856404;}
.inst-badge{font-size:.7rem;font-weight:600;padding:2px 7px;border-radius:99px;background:#eef2f7;color:#4a5568;}
.pagination{display:flex;justify-content:center;gap:.35rem;margin-top:2rem;flex-wrap:wrap;}
.page-btn{padding:.42rem .82rem;border:1px solid var(--border);border-radius:7px;background:var(--white);color:var(--text);font-size:.83rem;transition:background .14s;}
.page-btn:hover{background:var(--light-bg);}
.page-btn.active{background:var(--green);color:#fff;border-color:var(--green);font-weight:600;}
.empty-state{text-align:center;padding:4rem 2rem;color:var(--muted);}
@media(max-width:600px){.grid{grid-template-columns:1fr;}.card-metrics{grid-template-columns:1fr 1fr;}}
</style>

<div class="hero">
    <h1>Share Offerings</h1>
    <p>{{ number_format($total) }} active and recent offerings from Cameroon-registered companies</p>
    <form class="search-form" method="GET" action="/offerings">
        <input type="text" name="q" placeholder="Search by company or offering name…" value="{{ $search }}">
        @if($type)<input type="hidden" name="type" value="{{ $type }}">@endif
        @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
        <button type="submit" class="btn-search">Search</button>
    </form>
    <div class="filters-row">
        <form method="GET" action="/offerings" id="ff">
            @if($search)<input type="hidden" name="q" value="{{ $search }}">@endif
            <select class="filter-select" name="type" onchange="ff.submit()">
                <option value="">All Types</option>
                <option value="ordinary_shares" {{ $type==='ordinary_shares'?'selected':'' }}>Equity / Shares</option>
                <option value="bonds"            {{ $type==='bonds'?'selected':'' }}>Bonds</option>
                <option value="preference_shares"{{ $type==='preference_shares'?'selected':'' }}>Preference Shares</option>
                <option value="convertible_notes"{{ $type==='convertible_notes'?'selected':'' }}>Convertible Notes</option>
            </select>
            <select class="filter-select" name="status" onchange="ff.submit()">
                <option value="">All Status</option>
                <option value="open"         {{ $status==='open'?'selected':'' }}>Open</option>
                <option value="cmf_approved" {{ $status==='cmf_approved'?'selected':'' }}>CMF Approved</option>
                <option value="pending_cmf"  {{ $status==='pending_cmf'?'selected':'' }}>Pending CMF</option>
                <option value="closed"       {{ $status==='closed'?'selected':'' }}>Closed</option>
            </select>
        </form>
        @if($search||$type||$status)<a href="/offerings" class="clear-link"><i data-lucide="x" class="lic"></i> Clear</a>@endif
    </div>
</div>

<div class="stats-bar">
    <div class="stat"><span class="stat-dot" style="background:#007a33"></span>Open</div>
    <div class="stat"><span class="stat-dot" style="background:#0056b3"></span>CMF Approved</div>
    <div class="stat"><span class="stat-dot" style="background:#e67e22"></span>Pending CMF</div>
    <div class="stats-right">{{ number_format($total) }} offerings total</div>
</div>

<div class="main">
    @if($offerings->isEmpty())
        <div class="empty-state"><div style="font-size:3rem;margin-bottom:.8rem"><i data-lucide="bar-chart-3" class="lic"></i></div><h3>No offerings found</h3><p>Try clearing your filters.</p></div>
    @else
        <div class="grid">
            @foreach($offerings as $o)
                @php
                    $ini = strtoupper(substr($o->company_trade ?: $o->company_name, 0, 2));
                    $clrs = ['#007a33','#ce1126','#0056b3','#7b2d8b','#c0392b','#16a085','#d35400','#2c3e50'];
                    $clr = $clrs[crc32($o->company_id) % count($clrs)];
                    $pct = $o->target_amount > 0 ? round($o->amount_raised / $o->target_amount * 100) : 0;
                    $barColor = match($o->status){'open'=>'#007a33','cmf_approved'=>'#0056b3','closed'=>'#95a5a6','completed'=>'#27ae60',default=>'#e67e22'};
                    $stClass = 'st-'.str_replace('_','-',$o->status);
                    $stLabel = match($o->status){
                        'open'=>'Open','cmf_approved'=>'CMF Approved','pending_cmf'=>'Pending CMF',
                        'closed'=>'Closed','completed'=>'Completed','paused'=>'Paused','draft'=>'Draft',
                        'cancelled'=>'Cancelled',default=>ucfirst($o->status)};
                    $instLabel = match($o->instrument_type){
                        'ordinary_shares'=>'Equity','bonds'=>'Bond',
                        'preference_shares'=>'Pref. Share','convertible_notes'=>'Conv. Note',default=>$o->instrument_type};
                @endphp
                <a class="card" href="/offerings/{{ $o->id }}" style="text-decoration:none;color:inherit;">
                    <div class="card-status-bar" style="background:{{ $barColor }}"></div>
                    <div class="card-header">
                        <div class="logo" style="background:{{ $clr }}">{{ $ini }}</div>
                        <div style="flex:1;min-width:0;">
                            <div class="offering-title">{{ $o->title_en }}</div>
                            <div class="offering-company">{{ $o->company_name }}</div>
                        </div>
                    </div>
                    <div class="card-body"><p class="offering-desc">{{ $o->summary_en }}</p></div>
                    @if($o->target_amount > 0)
                        <div class="progress-section">
                            <div class="progress-label">
                                <span>{{ number_format($o->amount_raised/1000000,1) }}M XAF raised</span>
                                <span>{{ $pct }}%</span>
                            </div>
                            <div class="progress-bar"><div class="progress-fill" style="width:{{ min($pct,100) }}%;background:{{ $barColor }}"></div></div>
                            <div style="font-size:.7rem;color:var(--muted);margin-top:3px;">Target: {{ number_format($o->target_amount/1000000,1) }}M XAF</div>
                        </div>
                    @endif
                    <div class="card-metrics">
                        <div class="metric">
                            <div class="metric-val">{{ number_format($o->min_investment/1000) }}K</div>
                            <div class="metric-lbl">Min XAF</div>
                        </div>
                        <div class="metric">
                            <div class="metric-val">{{ number_format($o->share_price) }}</div>
                            <div class="metric-lbl">Unit Price</div>
                        </div>
                        <div class="metric">
                            <div class="metric-val">{{ $o->close_date ? date('M Y',strtotime($o->close_date)) : 'TBD' }}</div>
                            <div class="metric-lbl">Closes</div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <span class="status-badge {{ $stClass }}">{{ $stLabel }}</span>
                        <span class="inst-badge">{{ $instLabel }}</span>
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
@include('partials.footer')
</body>
</html>
