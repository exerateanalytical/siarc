<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Asset Sharing Marketplace — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:1100px;margin:0 auto;padding:1.5rem;}
.hero{background:linear-gradient(135deg,#7c2d12,#c2410c);border-radius:var(--radius);padding:2.5rem 2rem;margin-bottom:1.5rem;color:#fff;}
.hero-title{font-size:1.8rem;font-weight:900;margin-bottom:.4rem;}
.hero-sub{font-size:.9rem;color:#fed7aa;margin-bottom:1.2rem;}
.btn-white{padding:.55rem 1.3rem;background:#fff;color:#7c2d12;border-radius:8px;font-weight:700;font-size:.88rem;border:none;cursor:pointer;}
.btn-outline{padding:.55rem 1.3rem;border:1px solid rgba(255,255,255,.3);color:#fff;border-radius:8px;font-weight:600;font-size:.88rem;cursor:pointer;background:none;text-decoration:none;}
.cat-tabs{display:flex;gap:.4rem;flex-wrap:wrap;margin-bottom:1.2rem;}
.cat-tab{padding:.3rem .9rem;border-radius:99px;font-size:.78rem;font-weight:600;cursor:pointer;border:1px solid var(--border);background:#fff;color:var(--muted);transition:all .15s;}
.cat-tab.active,.cat-tab:hover{background:#c2410c;color:#fff;border-color:#c2410c;}
.asset-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(290px,1fr));gap:1rem;}
.asset-card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);border:1px solid var(--border);overflow:hidden;transition:box-shadow .15s;display:flex;flex-direction:column;}
.asset-card:hover{box-shadow:var(--shadow-hover);}
.asset-img{height:110px;display:flex;align-items:center;justify-content:center;font-size:3rem;background:linear-gradient(135deg,#fff7ed,#ffedd5);}
.asset-body{padding:1rem 1.1rem;flex:1;}
.badge{display:inline-block;padding:2px 9px;border-radius:99px;font-size:.7rem;font-weight:700;border:1px solid var(--border);background:var(--light-bg);color:var(--muted);}
.badge-avail{background:#d1fae5;color:#065f46;border-color:#6ee7b7;}
.badge-rented{background:#fee2e2;color:#991b1b;border-color:#fca5a5;}
.asset-title{font-weight:800;font-size:.92rem;color:var(--text);margin:.4rem 0 .3rem;line-height:1.3;}
.asset-spec{font-size:.76rem;color:var(--muted);margin-bottom:.3rem;}
.asset-foot{padding:.75rem 1.1rem;background:var(--light-bg);border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;}
.asset-price{font-weight:800;color:#c2410c;font-size:.9rem;}
.price-unit{font-size:.7rem;color:var(--muted);font-weight:600;}
/* modal */
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:200;align-items:center;justify-content:center;}
.modal-overlay.open{display:flex;}
.modal{background:#fff;border-radius:var(--radius);padding:1.5rem;width:min(540px,95vw);max-height:90vh;overflow-y:auto;}
.form-group{margin-bottom:.8rem;}
.form-label{display:block;font-size:.8rem;font-weight:600;margin-bottom:.3rem;}
.form-control{width:100%;padding:.45rem .75rem;border:1px solid var(--border);border-radius:7px;font-size:.85rem;outline:none;font-family:inherit;box-sizing:border-box;}
.form-control:focus{border-color:#c2410c;}
textarea.form-control{resize:vertical;min-height:70px;}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:.6rem;}
@media(max-width:600px){.form-row{grid-template-columns:1fr;}}
</style>

@php
$authUser = webUser();
$category = request('category','');
$q = request('q','');
$query = DB::table('shared_assets')
    ->leftJoin('companies','shared_assets.company_id','=','companies.id')
    ->select('shared_assets.*','companies.name as company_name','companies.slug as company_slug')
    ->where('shared_assets.status','active');
if ($category) $query->where('shared_assets.category',$category);
if ($q) $query->where('shared_assets.title','like',"%$q%");
$assets = $query->orderByDesc('shared_assets.created_at')->get();

$catLabels = ['equipment'=>'Equipment','machinery'=>'Machinery','vehicle'=>'Vehicles','warehouse'=>'Warehouse','office_space'=>'Office Space','land'=>'Land','cold_storage'=>'Cold Storage','lab'=>'Lab','tools'=>'Tools','generator'=>'Generators','event_space'=>'Event Space','other'=>'Other'];
$catIcons = ['equipment'=>'settings','machinery'=>'hard-hat','vehicle'=>'truck','warehouse'=>'factory','office_space'=>'building-2','land'=>'globe','cold_storage'=>'snowflake','lab'=>'microscope','tools'=>'wrench','generator'=>'zap','event_space'=>'tent','other'=>'package'];
$pricingLabels = ['hourly'=>'/hour','daily'=>'/day','weekly'=>'/week','monthly'=>'/month','per_use'=>'/use','negotiable'=>'negotiable'];
@endphp

<div class="page">
    <div class="hero">
        <div class="hero-title"><i data-lucide="factory" class="lic"></i> Asset Sharing Marketplace</div>
        <div class="hero-sub">Rent idle equipment, vehicles, warehouses &amp; space — or earn from yours. Cameroon's circular asset economy.</div>
        @if($authUser)
        <button class="btn-white" onclick="document.getElementById('postModal').classList.add('open')">+ List an Asset</button>
        @else
        <a href="/auth/login" class="btn-outline">Sign In to List</a>
        @endif
    </div>

    <div class="cat-tabs">
        <a class="cat-tab {{ !$category?'active':'' }}" href="/assets">All</a>
        @foreach($catLabels as $k=>$v)<a class="cat-tab {{ $category===$k?'active':'' }}" href="/assets?category={{ $k }}"><i data-lucide="{{ $catIcons[$k] }}" class="lic"></i> {{ $v }}</a>@endforeach
    </div>

    <div style="display:flex;gap:.5rem;margin-bottom:1.2rem;">
        <form method="GET" action="/assets" style="display:flex;gap:.5rem;flex:1;flex-wrap:wrap;">
            <input type="text" name="q" value="{{ $q }}" placeholder="Search assets…" style="flex:1;min-width:200px;padding:.45rem .8rem;border:1px solid var(--border);border-radius:7px;font-size:.85rem;outline:none;">
            @if($category)<input type="hidden" name="category" value="{{ $category }}">@endif
            <button type="submit" style="padding:.45rem 1.1rem;background:#c2410c;color:#fff;border:none;border-radius:7px;font-size:.83rem;font-weight:600;cursor:pointer;">Search</button>
        </form>
        <span style="font-size:.78rem;color:var(--muted);align-self:center;white-space:nowrap;">{{ $assets->count() }} assets</span>
    </div>

    @if($assets->isEmpty())
    <div style="text-align:center;padding:3rem;background:#fff;border-radius:var(--radius);border:1px solid var(--border);">
        <div style="font-size:2rem;margin-bottom:.5rem;"><i data-lucide="factory" class="lic"></i></div>
        <div style="font-weight:700;margin-bottom:.3rem;">No assets found</div>
        <div style="font-size:.85rem;color:var(--muted);">Be the first to list an asset for sharing.</div>
    </div>
    @else
    <div class="asset-grid">
        @foreach($assets as $a)
        <div class="asset-card">
            <div class="asset-img"><i data-lucide="{{ $catIcons[$a->category]??'package' }}" class="lic"></i></div>
            <div class="asset-body">
                <div style="display:flex;gap:.4rem;flex-wrap:wrap;margin-bottom:.2rem;">
                    <span class="badge">{{ $catLabels[$a->category]??ucfirst($a->category) }}</span>
                    <span class="badge {{ $a->availability==='available'?'badge-avail':'badge-rented' }}">{{ ucfirst($a->availability) }}</span>
                </div>
                <a href="/assets/{{ $a->slug }}" style="color:var(--text);"><div class="asset-title">{{ $a->title }}</div></a>
                @if($a->capacity_spec)<div class="asset-spec"><i data-lucide="ruler" class="lic"></i> {{ $a->capacity_spec }}</div>@endif
                @if($a->location_city)<div class="asset-spec"><i data-lucide="map-pin" class="lic"></i> {{ $a->location_city }}</div>@endif
            </div>
            <div class="asset-foot">
                <div>
                    @if($a->price)<span class="asset-price">{{ number_format($a->price) }} {{ $a->currency }}</span> <span class="price-unit">{{ $pricingLabels[$a->pricing_model]??'' }}</span>
                    @else<span style="font-size:.78rem;color:var(--muted);">Negotiable</span>@endif
                </div>
                <a href="/assets/{{ $a->slug }}" style="padding:.3rem .8rem;background:#c2410c;color:#fff;border-radius:6px;font-size:.75rem;font-weight:700;">View →</a>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

@if($authUser)
<div class="modal-overlay" id="postModal">
    <div class="modal">
        <div style="font-weight:800;font-size:1rem;margin-bottom:1rem;"><i data-lucide="factory" class="lic"></i> List an Asset for Sharing</div>
        <form method="POST" action="/assets">
            @csrf
            <div class="form-group"><label class="form-label">Asset Title *</label><input type="text" class="form-control" name="title" required placeholder="e.g. Warehouse space Douala 2000m²"></div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Category *</label><select class="form-control" name="category" required>@foreach($catLabels as $k=>$v)<option value="{{ $k }}"><i data-lucide="{{ $catIcons[$k] }}" class="lic"></i> {{ $v }}</option>@endforeach</select></div>
                <div class="form-group"><label class="form-label">Pricing Model</label><select class="form-control" name="pricing_model">@foreach(['hourly','daily','weekly','monthly','per_use','negotiable'] as $pm)<option value="{{ $pm }}">{{ ucfirst($pm) }}</option>@endforeach</select></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Price (XAF)</label><input type="number" class="form-control" name="price" min="0"></div>
                <div class="form-group"><label class="form-label">City</label><input type="text" class="form-control" name="location_city" placeholder="e.g. Douala"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Capacity / Spec</label><input type="text" class="form-control" name="capacity_spec" placeholder="e.g. 2000 m², 15 tonnes"></div>
                <div class="form-group"><label class="form-label">Condition</label><input type="text" class="form-control" name="condition" placeholder="e.g. Excellent"></div>
            </div>
            <div class="form-group"><label class="form-label">Description</label><textarea class="form-control" name="description" placeholder="Describe the asset, terms, and availability…"></textarea></div>
            <div class="form-group"><label class="form-label">Contact Phone</label><input type="text" class="form-control" name="contact_phone" placeholder="+237 …"></div>
            <div style="display:flex;gap:.6rem;justify-content:flex-end;margin-top:.5rem;">
                <button type="button" style="padding:.6rem 1.2rem;border:1px solid var(--border);background:#fff;border-radius:8px;font-weight:600;cursor:pointer;" onclick="document.getElementById('postModal').classList.remove('open')">Cancel</button>
                <button type="submit" style="padding:.6rem 1.5rem;background:#c2410c;color:#fff;border:none;border-radius:8px;font-weight:700;cursor:pointer;">List Asset →</button>
            </div>
        </form>
    </div>
</div>
@endif
@include('partials.footer')
</body>
</html>
