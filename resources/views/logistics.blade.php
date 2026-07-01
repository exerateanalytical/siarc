<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Logistics Exchange — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:1100px;margin:0 auto;padding:1.5rem;}
.hero{background:linear-gradient(135deg,#0c4a6e,#0284c7);border-radius:var(--radius);padding:2.5rem 2rem;margin-bottom:1.5rem;color:#fff;}
.hero-title{font-size:1.8rem;font-weight:900;margin-bottom:.4rem;}
.hero-sub{font-size:.9rem;color:#bae6fd;margin-bottom:1.2rem;}
.btn-white{padding:.55rem 1.3rem;background:#fff;color:#0c4a6e;border-radius:8px;font-weight:700;font-size:.88rem;border:none;cursor:pointer;}
.btn-outline{padding:.55rem 1.3rem;border:1px solid rgba(255,255,255,.3);color:#fff;border-radius:8px;font-weight:600;font-size:.88rem;cursor:pointer;background:none;text-decoration:none;}
.tabs{display:flex;gap:.5rem;margin-bottom:1.2rem;border-bottom:2px solid var(--border);}
.tab{padding:.6rem 1.2rem;font-size:.88rem;font-weight:700;cursor:pointer;color:var(--muted);border-bottom:3px solid transparent;margin-bottom:-2px;text-decoration:none;}
.tab.active{color:#0284c7;border-bottom-color:#0284c7;}
.filter-bar{display:flex;gap:.5rem;margin-bottom:1.2rem;flex-wrap:wrap;}
.filter-input{padding:.42rem .75rem;border:1px solid var(--border);border-radius:7px;font-size:.82rem;outline:none;}
.list-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(330px,1fr));gap:1rem;}
.list-card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);border:1px solid var(--border);overflow:hidden;transition:box-shadow .15s;}
.list-card:hover{box-shadow:var(--shadow-hover);}
.list-route{padding:1rem 1.1rem;display:flex;align-items:center;gap:.6rem;border-bottom:1px solid var(--border);}
.route-city{font-weight:800;font-size:.95rem;color:var(--text);}
.route-arrow{color:#0284c7;font-size:1.1rem;}
.list-body{padding:.9rem 1.1rem;}
.type-pill{display:inline-block;padding:2px 10px;border-radius:99px;font-size:.7rem;font-weight:800;}
.pill-load{background:#fef3c7;color:#92400e;}
.pill-capacity{background:#d1fae5;color:#065f46;}
.list-title{font-weight:700;font-size:.88rem;color:var(--text);margin:.4rem 0 .3rem;}
.list-meta{display:flex;flex-wrap:wrap;gap:.5rem;font-size:.74rem;color:var(--muted);margin-top:.4rem;}
.meta-chip{background:var(--light-bg);border:1px solid var(--border);border-radius:6px;padding:1px 7px;}
.list-foot{padding:.7rem 1.1rem;background:var(--light-bg);border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;}
.list-price{font-weight:800;color:#0284c7;font-size:.9rem;}
/* modal */
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:200;align-items:center;justify-content:center;}
.modal-overlay.open{display:flex;}
.modal{background:#fff;border-radius:var(--radius);padding:1.5rem;width:min(540px,95vw);max-height:90vh;overflow-y:auto;}
.form-group{margin-bottom:.8rem;}
.form-label{display:block;font-size:.8rem;font-weight:600;margin-bottom:.3rem;}
.form-control{width:100%;padding:.45rem .75rem;border:1px solid var(--border);border-radius:7px;font-size:.85rem;outline:none;font-family:inherit;box-sizing:border-box;}
.form-control:focus{border-color:#0284c7;}
textarea.form-control{resize:vertical;min-height:70px;}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:.6rem;}
@media(max-width:600px){.form-row{grid-template-columns:1fr;}}
</style>

@php
$authUser = webUser();
$tab = request('type','load');
if (!in_array($tab,['load','capacity'])) $tab = 'load';
$origin = request('origin','');
$dest   = request('dest','');
$query = DB::table('logistics_listings')
    ->leftJoin('companies','logistics_listings.company_id','=','companies.id')
    ->select('logistics_listings.*','companies.name as company_name','companies.slug as company_slug')
    ->where('logistics_listings.type',$tab)
    ->where('logistics_listings.status','open');
if ($origin) $query->where('logistics_listings.origin_city','like',"%$origin%");
if ($dest)   $query->where('logistics_listings.destination_city','like',"%$dest%");
$listings = $query->orderByDesc('logistics_listings.created_at')->get();
$loadCount = DB::table('logistics_listings')->where('type','load')->where('status','open')->count();
$capCount  = DB::table('logistics_listings')->where('type','capacity')->where('status','open')->count();

$cargoIcons = ['general'=>'package','perishable'=>'leaf','refrigerated'=>'snowflake','hazardous'=>'radiation','bulk'=>'wheat','liquid'=>'fuel','livestock'=>'beef','containers'=>'ship','oversized'=>'hard-hat','fragile'=>'bell','other'=>'package'];
$vehicleLabels = ['van'=>'Van','pickup'=>'Pickup','truck_small'=>'Small Truck','truck_medium'=>'Medium Truck','truck_large'=>'Large Truck','trailer'=>'Trailer','refrigerated_truck'=>'Reefer Truck','tanker'=>'Tanker','flatbed'=>'Flatbed','container_truck'=>'Container Truck','any'=>'Any Vehicle'];
@endphp

<div class="page">
    <div class="hero">
        <div class="hero-title"><i data-lucide="truck" class="lic"></i> Logistics Exchange</div>
        <div class="hero-sub">Cameroon's freight marketplace — post loads, offer transport capacity, and move goods efficiently</div>
        @if($authUser)
        <button class="btn-white" onclick="document.getElementById('postModal').classList.add('open')">+ Post a Listing</button>
        @else
        <a href="/auth/login" class="btn-outline">Sign In to Post</a>
        @endif
    </div>

    <div class="tabs">
        <a class="tab {{ $tab==='load'?'active':'' }}" href="/logistics?type=load"><i data-lucide="package" class="lic"></i> Loads Needing Transport ({{ $loadCount }})</a>
        <a class="tab {{ $tab==='capacity'?'active':'' }}" href="/logistics?type=capacity"><i data-lucide="truck" class="lic"></i> Available Capacity ({{ $capCount }})</a>
    </div>

    <form method="GET" action="/logistics" class="filter-bar">
        <input type="hidden" name="type" value="{{ $tab }}">
        <input type="text" name="origin" value="{{ $origin }}" class="filter-input" placeholder="Origin city…">
        <input type="text" name="dest" value="{{ $dest }}" class="filter-input" placeholder="Destination city…">
        <button type="submit" style="padding:.42rem 1.1rem;background:#0284c7;color:#fff;border:none;border-radius:7px;font-size:.82rem;font-weight:600;cursor:pointer;">Search</button>
        <span style="font-size:.78rem;color:var(--muted);align-self:center;margin-left:auto;">{{ $listings->count() }} listings</span>
    </form>

    @if($listings->isEmpty())
    <div style="text-align:center;padding:3rem;background:#fff;border-radius:var(--radius);border:1px solid var(--border);">
        <div style="font-size:2rem;margin-bottom:.5rem;"><i data-lucide="truck" class="lic"></i></div>
        <div style="font-weight:700;margin-bottom:.3rem;">No {{ $tab==='load'?'loads':'capacity offers' }} found</div>
        <div style="font-size:.85rem;color:var(--muted);">Try different cities or post your own listing.</div>
    </div>
    @else
    <div class="list-grid">
        @foreach($listings as $l)
        <div class="list-card">
            <div class="list-route">
                <div class="route-city">{{ $l->origin_city }}</div>
                <div class="route-arrow">→</div>
                <div class="route-city">{{ $l->destination_city }}</div>
            </div>
            <div class="list-body">
                <span class="type-pill {{ $l->type==='load'?'pill-load':'pill-capacity' }}">{{ $l->type==='load'?'LOAD':'CAPACITY' }}</span>
                <a href="/logistics/{{ $l->id }}" style="color:var(--text);"><div class="list-title">{{ $l->title }}</div></a>
                <div class="list-meta">
                    <span class="meta-chip"><i data-lucide="{{ $cargoIcons[$l->cargo_type]??'package' }}" class="lic"></i> {{ ucfirst(str_replace('_',' ',$l->cargo_type)) }}</span>
                    <span class="meta-chip"><i data-lucide="truck" class="lic"></i> {{ $vehicleLabels[$l->vehicle_type]??'Any' }}</span>
                    {{ $l->weight_kg ? '' : '' }}
                    @if($l->weight_kg)<span class="meta-chip"><i data-lucide="scale" class="lic"></i> {{ number_format($l->weight_kg/1000,1) }}t</span>@endif
                    @if($l->available_date)<span class="meta-chip"><i data-lucide="calendar" class="lic"></i> {{ date('d M', strtotime($l->available_date)) }}</span>@endif
                </div>
            </div>
            <div class="list-foot">
                @if($l->price)<span class="list-price">{{ number_format($l->price) }} {{ $l->currency }}</span>@else<span style="font-size:.78rem;color:var(--muted);">Price negotiable</span>@endif
                <a href="/logistics/{{ $l->id }}" style="padding:.3rem .8rem;background:#0284c7;color:#fff;border-radius:6px;font-size:.75rem;font-weight:700;">{{ $l->type==='load'?'Bid →':'Book →' }}</a>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

@if($authUser)
<div class="modal-overlay" id="postModal">
    <div class="modal">
        <div style="font-weight:800;font-size:1rem;margin-bottom:1rem;"><i data-lucide="truck" class="lic"></i> Post a Logistics Listing</div>
        <form method="POST" action="/logistics">
            @csrf
            <div class="form-group"><label class="form-label">Listing Type *</label>
                <select class="form-control" name="type" required>
                    <option value="load"><i data-lucide="package" class="lic"></i> I have a LOAD needing transport</option>
                    <option value="capacity"><i data-lucide="truck" class="lic"></i> I have CAPACITY available</option>
                </select>
            </div>
            <div class="form-group"><label class="form-label">Title *</label><input type="text" class="form-control" name="title" required placeholder="e.g. Cocoa beans Bafoussam to Douala"></div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Origin City *</label><input type="text" class="form-control" name="origin_city" required></div>
                <div class="form-group"><label class="form-label">Destination City *</label><input type="text" class="form-control" name="destination_city" required></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Cargo Type</label>
                    <select class="form-control" name="cargo_type">@foreach($cargoIcons as $k=>$icon)<option value="{{ $k }}">{{ $icon }} {{ ucfirst(str_replace('_',' ',$k)) }}</option>@endforeach</select>
                </div>
                <div class="form-group"><label class="form-label">Vehicle Type</label>
                    <select class="form-control" name="vehicle_type">@foreach($vehicleLabels as $k=>$v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Weight (kg)</label><input type="number" class="form-control" name="weight_kg" min="0"></div>
                <div class="form-group"><label class="form-label">Price (XAF)</label><input type="number" class="form-control" name="price" min="0"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Available / Pickup Date</label><input type="date" class="form-control" name="available_date"></div>
                <div class="form-group"><label class="form-label">Contact Phone</label><input type="text" class="form-control" name="contact_phone" placeholder="+237 …"></div>
            </div>
            <div class="form-group"><label class="form-label">Description</label><textarea class="form-control" name="description" placeholder="Cargo details, requirements, timing…"></textarea></div>
            <div style="display:flex;gap:.6rem;justify-content:flex-end;margin-top:.5rem;">
                <button type="button" style="padding:.6rem 1.2rem;border:1px solid var(--border);background:#fff;border-radius:8px;font-weight:600;cursor:pointer;" onclick="document.getElementById('postModal').classList.remove('open')">Cancel</button>
                <button type="submit" style="padding:.6rem 1.5rem;background:#0284c7;color:#fff;border:none;border-radius:8px;font-weight:700;cursor:pointer;">Post Listing →</button>
            </div>
        </form>
    </div>
</div>
@endif
@include('partials.footer')
</body>
</html>
