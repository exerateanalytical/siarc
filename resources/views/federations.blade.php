<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Industry Federations & Ecosystems — Galerie virtuelle de l'artisanat du Cameroun</title>
<meta name="description" content="Governed business ecosystems for Cameroon's key sectors — cocoa, timber, ICT, palm oil, and more.">
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:1100px;margin:0 auto;padding:1.5rem;}
.hero{background:linear-gradient(135deg,#0a0a2e,#1a1a4e);border-radius:var(--radius);padding:2.5rem 2rem;color:#fff;margin-bottom:2rem;position:relative;overflow:hidden;}
.hero::before{content:'';position:absolute;right:-60px;bottom:-60px;width:300px;height:300px;border-radius:50%;background:rgba(252,209,22,.06);}
.hero-title{font-size:1.8rem;font-weight:900;margin-bottom:.5rem;}
.hero-sub{color:#aab;font-size:.9rem;max-width:600px;}
.h-stats{display:flex;gap:2rem;margin-top:1.5rem;flex-wrap:wrap;}
.h-stat-val{font-size:1.4rem;font-weight:800;color:var(--yellow);}
.h-stat-lbl{font-size:.72rem;color:#8899aa;}
.what-box{background:linear-gradient(135deg,rgba(0,122,51,.08),rgba(0,122,51,.03));border:1px solid rgba(0,122,51,.15);border-radius:var(--radius);padding:1.5rem;margin-bottom:1.5rem;}
.what-title{font-weight:800;font-size:1rem;color:var(--text);margin-bottom:.5rem;}
.what-text{font-size:.85rem;color:var(--muted);line-height:1.65;}
.what-features{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:.6rem;margin-top:.9rem;}
.what-feat{padding:.5rem .8rem;background:#fff;border-radius:7px;border:1px solid var(--border);font-size:.78rem;font-weight:600;color:var(--text);}
.fed-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:1.2rem;}
.fed-card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);padding:1.5rem;border:1px solid var(--border);transition:box-shadow .2s,transform .15s;position:relative;}
.fed-card:hover{box-shadow:0 6px 24px rgba(0,0,0,.12);transform:translateY(-2px);}
.fed-header{display:flex;gap:.9rem;align-items:flex-start;margin-bottom:1rem;}
.fed-logo{width:56px;height:56px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:1rem;flex-shrink:0;letter-spacing:-.5px;}
.fed-name{font-weight:800;font-size:1rem;color:var(--text);line-height:1.25;}
.fed-acronym{font-size:.78rem;color:var(--muted);margin-top:2px;}
.fed-sector{display:inline-block;padding:2px 9px;border-radius:99px;font-size:.68rem;font-weight:700;border:1px solid currentColor;margin-top:.3rem;}
.fed-desc{font-size:.82rem;color:var(--muted);line-height:1.55;margin-bottom:.9rem;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;}
.fed-meta{display:flex;gap:1.2rem;font-size:.75rem;color:var(--muted);margin-bottom:.9rem;}
.fed-footer{padding-top:.75rem;border-top:1px solid var(--border);display:flex;gap:.5rem;align-items:center;}
.btn-sm{padding:.32rem .8rem;border-radius:6px;font-size:.76rem;font-weight:600;display:inline-block;}
.btn-green{background:var(--green);color:#fff;}
.btn-outline{border:1px solid var(--border);color:var(--text);}
.featured-badge{position:absolute;top:.8rem;right:.8rem;background:var(--yellow);color:var(--dark);font-size:.64rem;font-weight:800;padding:2px 8px;border-radius:99px;}
.sector-colors{cocoa:'#7c5c1e',timber:'#3a7c1e',palm_oil:'#d97706',ict:'#0284c7',finance:'#7c3aed',health:'#dc2626',construction:'#b45309',transport:'#0891b2',mining:'#374151',tourism:'#059669',energy:'#d97706',textile:'#7c3aed',agri_food:'#166534',other:'#4b5563'}
@media(max-width:640px){.fed-grid{grid-template-columns:1fr;}.what-features{grid-template-columns:1fr 1fr;}.h-stats{gap:1rem;}}
</style>

@php
$authUser = session('auth_user');
$feds = DB::table('federations')
    ->whereNull('deleted_at')
    ->where('status','active')
    ->where('is_public',1)
    ->orderByRaw('is_featured DESC')
    ->orderBy('name')
    ->get();
$totalMembers = DB::table('federation_members')->where('status','active')->count();
$sectorColors = ['cocoa'=>'#7c5c1e','timber'=>'#3a7c1e','palm_oil'=>'#d97706','ict'=>'#0284c7','finance'=>'#7c3aed','health'=>'#dc2626','construction'=>'#b45309','transport'=>'#0891b2','mining'=>'#374151','tourism'=>'#059669','energy'=>'#ca8a04','textile'=>'#7c3aed','agri_food'=>'#166534','other'=>'#4b5563'];
$sectorIcons = ['cocoa'=>'candy','timber'=>'trees','palm_oil'=>'palmtree','ict'=>'laptop','finance'=>'landmark','health'=>'heart-pulse','construction'=>'hard-hat','transport'=>'truck','mining'=>'pickaxe','tourism'=>'plane','energy'=>'zap','textile'=>'shopping-basket','agri_food'=>'wheat','other'=>'building-2'];
@endphp

<div class="page">
    <div class="hero">
        <div style="position:relative;z-index:1;">
            <div style="display:inline-block;background:rgba(252,209,22,.15);border:1px solid rgba(252,209,22,.3);color:var(--yellow);padding:3px 12px;border-radius:99px;font-size:.72rem;font-weight:700;margin-bottom:.7rem;">FEDERATION MODE</div>
            <div class="hero-title">Industry Federations & Ecosystems</div>
            <div class="hero-sub">Governed business networks for entire value chains. Companies, associations, government bodies, and support organizations collaborating within a shared industry ecosystem.</div>
            <div class="h-stats">
                <div><div class="h-stat-val">{{ $feds->count() }}</div><div class="h-stat-lbl">Active Federations</div></div>
                <div><div class="h-stat-val">{{ number_format($totalMembers) }}</div><div class="h-stat-lbl">Member Companies</div></div>
                <div><div class="h-stat-val">{{ $feds->where('is_featured',1)->count() }}</div><div class="h-stat-lbl">Featured Ecosystems</div></div>
            </div>
        </div>
    </div>

    <div class="what-box">
        <div class="what-title"><i data-lucide="link" class="lic"></i> What is a Federation?</div>
        <div class="what-text">A Federation is a governed ecosystem where all participants in an industry value chain collaborate around shared goals. Unlike a simple collaboration between two companies, a Federation is infrastructure for an entire sector — with shared governance, joint procurement, cross-company projects, and industry analytics.</div>
        <div class="what-features">
            <div class="what-feat"><i data-lucide="landmark" class="lic"></i> Shared Governance</div>
            <div class="what-feat"><i data-lucide="package" class="lic"></i> Joint Procurement</div>
            <div class="what-feat"><i data-lucide="bar-chart-3" class="lic"></i> Sector Analytics</div>
            <div class="what-feat"><i data-lucide="clipboard-list" class="lic"></i> Common Document Library</div>
            <div class="what-feat"><i data-lucide="megaphone" class="lic"></i> Industry Announcements</div>
            <div class="what-feat"><i data-lucide="handshake" class="lic"></i> Cross-Company Projects</div>
            <div class="what-feat"><i data-lucide="globe" class="lic"></i> Export Coordination</div>
            <div class="what-feat"><i data-lucide="microscope" class="lic"></i> Working Groups</div>
        </div>
    </div>

    @if($feds->isEmpty())
        <div style="text-align:center;padding:3rem;color:var(--muted);">No federations yet. <a href="/support" style="color:var(--green);">Contact us to create one →</a></div>
    @else
        <div class="fed-grid">
        @foreach($feds as $f)
            @php
            $color = $sectorColors[$f->sector]??'#374151';
            $icon  = $sectorIcons[$f->sector]??'building-2';
            @endphp
            <div class="fed-card">
                @if($f->is_featured)<div class="featured-badge"><i data-lucide="star" class="lic"></i> Featured</div>@endif
                <div class="fed-header">
                    <div class="fed-logo" style="background:{{ $color }}22;color:{{ $color }};">{{ $icon }}</div>
                    <div style="flex:1;min-width:0;">
                        <div class="fed-name">{{ $f->name }}</div>
                        @if($f->acronym)<div class="fed-acronym">{{ $f->acronym }}</div>@endif
                        <span class="fed-sector" style="color:{{ $color }};border-color:{{ $color }}22;background:{{ $color }}11;">{{ ucfirst(str_replace('_',' ',$f->sector)) }}</span>
                    </div>
                </div>
                <div class="fed-desc">{{ $f->description }}</div>
                <div class="fed-meta">
                    <span><i data-lucide="building-2" class="lic"></i> {{ number_format($f->member_count) }} members</span>
                    <span><i data-lucide="eye" class="lic"></i> {{ number_format($f->view_count) }} views</span>
                </div>
                <div class="fed-footer">
                    <a href="/federations/{{ $f->slug }}" class="btn-sm btn-green">Enter Federation</a>
                    @if($authUser)<a href="/federations/{{ $f->slug }}/join" class="btn-sm btn-outline">Request Membership</a>@endif
                </div>
            </div>
        @endforeach
        </div>
    @endif

    <div style="background:var(--light-bg);border-radius:var(--radius);padding:1.8rem;margin-top:2rem;text-align:center;">
        <div style="font-weight:800;font-size:1.05rem;color:var(--text);margin-bottom:.5rem;">Want to create a Federation for your industry?</div>
        <div style="font-size:.85rem;color:var(--muted);margin-bottom:1rem;">We work with industry leaders, associations, and government agencies to establish governed ecosystems. Contact us to get started.</div>
        <a href="/support" style="display:inline-block;padding:.6rem 1.5rem;background:var(--green);color:#fff;border-radius:8px;font-weight:700;font-size:.9rem;">Contact Us to Create a Federation →</a>
    </div>
</div>
@include('partials.footer')
</body>
</html>
