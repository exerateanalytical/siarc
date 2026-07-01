<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Business Communities — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:1100px;margin:0 auto;padding:1.5rem;}
.hero{background:linear-gradient(135deg,#0f1623,#1e2d3d);border-radius:var(--radius);padding:2.5rem 2rem;margin-bottom:1.5rem;color:#fff;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;}
.hero-left .hero-title{font-size:1.8rem;font-weight:900;margin-bottom:.3rem;}
.hero-left .hero-sub{font-size:.9rem;color:#93c5fd;}
.hero-stats{display:flex;gap:1.5rem;}
.hero-stat{text-align:center;}
.hero-stat-num{font-size:1.4rem;font-weight:900;color:var(--yellow);}
.hero-stat-label{font-size:.72rem;color:#93c5fd;}
.cat-tabs{display:flex;gap:.4rem;flex-wrap:wrap;margin-bottom:1rem;}
.cat-tab{padding:.3rem .9rem;border-radius:99px;font-size:.78rem;font-weight:600;cursor:pointer;border:1px solid var(--border);background:#fff;color:var(--muted);transition:all .15s;}
.cat-tab.active,.cat-tab:hover{background:var(--dark);color:#fff;border-color:var(--dark);}
.comms-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1rem;}
.comm-card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);border:1px solid var(--border);overflow:hidden;transition:box-shadow .15s;}
.comm-card:hover{box-shadow:var(--shadow-hover);}
.comm-banner{height:80px;display:flex;align-items:center;padding:0 1.2rem;gap:.8rem;}
.comm-icon{width:44px;height:44px;border-radius:10px;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0;}
.comm-name{font-weight:800;font-size:1rem;color:#fff;line-height:1.2;}
.comm-tagline{font-size:.75rem;color:rgba(255,255,255,.7);margin-top:2px;}
.comm-body{padding:1rem 1.1rem;}
.comm-desc{font-size:.82rem;color:var(--muted);line-height:1.5;margin-bottom:.7rem;}
.comm-footer{padding:.7rem 1.1rem;background:var(--light-bg);border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;}
.comm-meta{font-size:.75rem;color:var(--muted);}
.btn-join{padding:.3rem .85rem;background:var(--green);color:#fff;border:none;border-radius:6px;font-size:.78rem;font-weight:700;cursor:pointer;}
.create-box{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);padding:1.2rem;border:2px dashed var(--border);text-align:center;display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:200px;}
/* Modal */
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:200;align-items:center;justify-content:center;}
.modal-overlay.open{display:flex;}
.modal{background:#fff;border-radius:var(--radius);padding:1.5rem;width:min(480px,95vw);max-height:90vh;overflow-y:auto;}
.form-group{margin-bottom:.85rem;}
.form-label{display:block;font-size:.8rem;font-weight:600;margin-bottom:.3rem;}
.form-control{width:100%;padding:.45rem .75rem;border:1px solid var(--border);border-radius:7px;font-size:.85rem;outline:none;font-family:inherit;box-sizing:border-box;}
.form-control:focus{border-color:var(--green);}
textarea.form-control{resize:vertical;min-height:75px;}
.sector-icons{agriculture:'wheat',cocoa:'candy',ict:'laptop',finance:'banknote',health:'heart-pulse',general:'globe'}
</style>

@php
$authUser = webUser();
$category = request('category','');
$q = request('q','');
$query = DB::table('communities')->where('status','active');
if ($category) $query->where('category',$category);
if ($q) $query->where('name','like',"%$q%");
$communities = $query->orderByDesc('member_count')->get();
$totalMembers = DB::table('communities')->where('status','active')->sum('member_count');
$totalCommunities = DB::table('communities')->where('status','active')->count();
$catLabels = ['industry'=>'Industry','regional'=>'Regional','professional'=>'Professional','special_interest'=>'Special Interest','alumni'=>'Alumni','government'=>'Government','ngo'=>'NGO','other'=>'Other'];
$sectorIcons = ['ict'=>'laptop','cocoa'=>'candy','agriculture'=>'wheat','finance'=>'banknote','health'=>'heart-pulse','general'=>'globe','construction'=>'hard-hat','tourism'=>'plane','energy'=>'zap','mining'=>'pickaxe','textile'=>'spool','transport'=>'truck','agri_food'=>'salad','timber'=>'trees','palm_oil'=>'palmtree','other'=>'diamond'];
@endphp

<div class="page">
    <div class="hero">
        <div class="hero-left">
            <div class="hero-title"><i data-lucide="message-circle" class="lic"></i> Business Communities</div>
            <div class="hero-sub">Industry groups, regional clubs, and professional networks for Cameroonian businesses</div>
        </div>
        <div class="hero-stats">
            <div class="hero-stat"><div class="hero-stat-num">{{ $totalCommunities }}</div><div class="hero-stat-label">Communities</div></div>
            <div class="hero-stat"><div class="hero-stat-num">{{ number_format($totalMembers) }}</div><div class="hero-stat-label">Members</div></div>
        </div>
    </div>

    <div class="cat-tabs">
        <a class="cat-tab {{ !$category?'active':'' }}" href="/communities">All</a>
        @foreach($catLabels as $k=>$v)<a class="cat-tab {{ $category===$k?'active':'' }}" href="/communities?category={{ $k }}">{{ $v }}</a>@endforeach
    </div>

    <div style="display:flex;gap:.5rem;margin-bottom:1.2rem;flex-wrap:wrap;">
        <form method="GET" action="/communities" style="display:flex;gap:.5rem;">
            <input type="text" name="q" value="{{ $q }}" placeholder="Search communities…" style="padding:.38rem .75rem;border:1px solid var(--border);border-radius:7px;font-size:.82rem;outline:none;min-width:200px;">
            @if($category)<input type="hidden" name="category" value="{{ $category }}">@endif
            <button type="submit" style="padding:.38rem .9rem;background:var(--green);color:#fff;border:none;border-radius:7px;font-size:.82rem;font-weight:600;cursor:pointer;">Search</button>
        </form>
        @if($authUser)
        <button class="btn-join" style="padding:.38rem .9rem;" onclick="document.getElementById('createModal').classList.add('open')">+ Create Community</button>
        @endif
    </div>

    <div class="comms-grid">
        @foreach($communities as $comm)
        <div class="comm-card">
            <div class="comm-banner" style="background:{{ $comm->cover_color }};">
                <div class="comm-icon"><i data-lucide="{{ $sectorIcons[$comm->sector]??'diamond' }}" class="lic"></i></div>
                <div>
                    <div class="comm-name">{{ $comm->name }}</div>
                    @if($comm->tagline)<div class="comm-tagline">{{ $comm->tagline }}</div>@endif
                </div>
            </div>
            <div class="comm-body">
                <div class="comm-desc">{{ Str::limit($comm->description??'',110) }}</div>
                <span style="display:inline-block;padding:2px 8px;border-radius:99px;font-size:.7rem;font-weight:600;background:var(--light-bg);color:var(--muted);border:1px solid var(--border);">{{ $catLabels[$comm->category]??ucfirst($comm->category) }}</span>
            </div>
            <div class="comm-footer">
                <div class="comm-meta">
                    <span><i data-lucide="users" class="lic"></i> {{ number_format($comm->member_count) }} members</span>
                    <span style="margin-left:.7rem;"><i data-lucide="message-circle" class="lic"></i> {{ number_format($comm->post_count) }} posts</span>
                </div>
                <a href="/communities/{{ $comm->slug }}" class="btn-join">View →</a>
            </div>
        </div>
        @endforeach

        @if($authUser)
        <div class="create-box">
            <div style="font-size:2rem;margin-bottom:.5rem;"><i data-lucide="plus" class="lic"></i></div>
            <div style="font-weight:700;margin-bottom:.3rem;">Start a Community</div>
            <div style="font-size:.82rem;color:var(--muted);margin-bottom:.75rem;text-align:center;">Bring together professionals in your industry or region.</div>
            <button class="btn-join" onclick="document.getElementById('createModal').classList.add('open')">Create Community</button>
        </div>
        @endif
    </div>
</div>

@if($authUser)
<div class="modal-overlay" id="createModal">
    <div class="modal">
        <div style="font-weight:800;font-size:1rem;margin-bottom:1rem;"><i data-lucide="message-circle" class="lic"></i> Create a Community</div>
        <form method="POST" action="/communities">
            @csrf
            <div class="form-group"><label class="form-label">Community Name *</label><input type="text" class="form-control" name="name" required></div>
            <div class="form-group"><label class="form-label">Tagline</label><input type="text" class="form-control" name="tagline" placeholder="Short compelling description"></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.6rem;">
                <div class="form-group"><label class="form-label">Category *</label>
                    <select class="form-control" name="category" required>
                        @foreach($catLabels as $k=>$v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group"><label class="form-label">Sector</label>
                    <select class="form-control" name="sector">
                        @foreach($sectorIcons as $k=>$icon)<option value="{{ $k }}">{{ $icon }} {{ ucfirst(str_replace('_',' ',$k)) }}</option>@endforeach
                    </select>
                </div>
            </div>
            <div class="form-group"><label class="form-label">Description</label><textarea class="form-control" name="description" placeholder="What is this community about?"></textarea></div>
            <div class="form-group"><label class="form-label">Cover Color</label>
                <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
                    @foreach(['#007a33','#0284c7','#6d28d9','#be185d','#92400e','#16a34a','#0f1623','#ce1126'] as $col)
                    <label style="cursor:pointer;"><input type="radio" name="cover_color" value="{{ $col }}" style="display:none;">
                    <span style="display:inline-block;width:28px;height:28px;border-radius:50%;background:{{ $col }};border:3px solid transparent;" onclick="this.style.border='3px solid #000'"></span></label>
                    @endforeach
                </div>
            </div>
            <div style="display:flex;gap:.6rem;justify-content:flex-end;margin-top:.75rem;">
                <button type="button" style="padding:.6rem 1.2rem;border:1px solid var(--border);background:#fff;border-radius:8px;font-weight:600;cursor:pointer;" onclick="document.getElementById('createModal').classList.remove('open')">Cancel</button>
                <button type="submit" style="padding:.6rem 1.5rem;background:var(--green);color:#fff;border:none;border-radius:8px;font-weight:700;cursor:pointer;">Create →</button>
            </div>
        </form>
    </div>
</div>
@endif
@include('partials.footer')
</body>
</html>
