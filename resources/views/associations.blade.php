<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Business Associations Directory — Galerie virtuelle de l'artisanat du Cameroun</title>
<meta name="description" content="Directory of business associations, chambers of commerce, and professional federations in Cameroon.">
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:1100px;margin:0 auto;padding:1.5rem;}
.hero{background:linear-gradient(135deg,var(--dark),var(--mid));border-radius:var(--radius);padding:2.5rem 2rem;color:#fff;margin-bottom:2rem;position:relative;overflow:hidden;}
.hero::before{content:'';position:absolute;right:-60px;top:-60px;width:300px;height:300px;border-radius:50%;background:rgba(255,255,255,.04);}
.hero-title{font-size:1.8rem;font-weight:900;margin-bottom:.5rem;}
.hero-sub{color:#aab;font-size:.95rem;max-width:560px;}
.hero-stats{display:flex;gap:2rem;margin-top:1.5rem;}
.h-stat{text-align:center;}
.h-stat-val{font-size:1.5rem;font-weight:800;color:var(--yellow);}
.h-stat-lbl{font-size:.75rem;color:#8899aa;margin-top:2px;}
.filters{display:flex;gap:.75rem;flex-wrap:wrap;margin-bottom:1.5rem;align-items:center;}
.filter-input{padding:.45rem .85rem;border:1px solid var(--border);border-radius:7px;font-size:.85rem;outline:none;background:#fff;color:var(--text);}
.filter-input:focus{border-color:var(--green);}
.sector-chips{display:flex;gap:.4rem;flex-wrap:wrap;}
.chip{padding:.3rem .75rem;border-radius:99px;font-size:.75rem;font-weight:600;cursor:pointer;border:1px solid var(--border);background:#fff;color:var(--muted);transition:all .15s;text-decoration:none;display:inline-block;}
.chip:hover,.chip.active{background:var(--dark);color:#fff;border-color:var(--dark);}
.chip-green.active{background:var(--green);border-color:var(--green);}
.results-meta{font-size:.82rem;color:var(--muted);margin-bottom:1rem;}
.assoc-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:1.2rem;}
.assoc-card{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);padding:1.4rem;border:1px solid var(--border);transition:box-shadow .2s,transform .15s;position:relative;}
.assoc-card:hover{box-shadow:var(--shadow-hover);transform:translateY(-2px);}
.assoc-header{display:flex;gap:.9rem;align-items:flex-start;margin-bottom:.85rem;}
.assoc-logo{width:52px;height:52px;border-radius:10px;background:linear-gradient(135deg,var(--dark),var(--mid));display:flex;align-items:center;justify-content:center;font-weight:900;font-size:1.1rem;color:var(--yellow);flex-shrink:0;letter-spacing:-.5px;}
.assoc-name{font-weight:800;font-size:.95rem;color:var(--text);line-height:1.3;}
.assoc-acronym{font-size:.78rem;color:var(--muted);margin-top:2px;}
.assoc-sector-badge{display:inline-block;padding:2px 10px;border-radius:99px;font-size:.68rem;font-weight:700;background:var(--light-bg);color:var(--muted);border:1px solid var(--border);margin-top:.35rem;}
.assoc-desc{font-size:.82rem;color:var(--muted);line-height:1.55;margin-bottom:.9rem;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;}
.assoc-meta{display:flex;gap:1.2rem;font-size:.75rem;color:var(--muted);flex-wrap:wrap;}
.assoc-meta-item{display:flex;align-items:center;gap:.3rem;}
.featured-badge{position:absolute;top:.85rem;right:.85rem;background:var(--yellow);color:var(--dark);font-size:.65rem;font-weight:800;padding:2px 8px;border-radius:99px;}
.assoc-footer{margin-top:.9rem;padding-top:.75rem;border-top:1px solid var(--border);display:flex;gap:.5rem;align-items:center;}
.btn-sm{padding:.35rem .8rem;border-radius:6px;font-size:.75rem;font-weight:600;display:inline-block;}
.btn-primary{background:var(--green);color:#fff;}
.btn-primary:hover{background:#00962e;}
.btn-outline{border:1px solid var(--border);color:var(--text);}
.btn-outline:hover{background:var(--light-bg);}
.empty{text-align:center;padding:3rem;color:var(--muted);font-size:.9rem;}
@media(max-width:640px){.hero-stats{gap:1rem;}.assoc-grid{grid-template-columns:1fr;}}
</style>

@php
    $q = request('q','');
    $sector = request('sector','');
    $query = DB::table('associations')
        ->where('is_active',1)
        ->whereNull('deleted_at');
    if($q) $query->where(function($x) use ($q){$x->where('name_en','like',"%$q%")->orWhere('name_fr','like',"%$q%")->orWhere('acronym','like',"%$q%")->orWhere('description_en','like',"%$q%");});
    if($sector) $query->where('sector',$sector);
    $total   = DB::table('associations')->where('is_active',1)->whereNull('deleted_at')->count();
    $assocs  = $query->orderByRaw('is_featured DESC')->orderBy('name_en')->get();
    $sectors = DB::table('associations')->where('is_active',1)->whereNull('deleted_at')->select('sector')->distinct()->orderBy('sector')->pluck('sector');
    $sectorLabels = ['agriculture'=>'Agriculture','commerce'=>'Commerce','industry'=>'Industry','services'=>'Services','technology'=>'Technology','finance'=>'Finance','health'=>'Health','construction'=>'Construction','transport'=>'Transport','mining'=>'Mining','tourism'=>'Tourism','energy'=>'Energy','legal'=>'Legal','education'=>'Education','agri-food'=>'Agri-food','ict'=>'ICT','other'=>'Other'];
    $sectorColors = ['agriculture'=>'#166534','commerce'=>'#0056b3','industry'=>'#8b2252','finance'=>'#854d0e','health'=>'#7c3aed','construction'=>'#92400e','transport'=>'#0e7490','ict'=>'#0369a1','energy'=>'#b45309','other'=>'#64748b'];
@endphp

<div class="page">
    <div class="hero">
        <div style="position:relative;z-index:1;">
            <div class="hero-title">Cameroon Business Associations</div>
            <div class="hero-sub">Connect with industry bodies, chambers of commerce, and professional federations that shape the Cameroonian business landscape.</div>
            <div class="hero-stats">
                <div class="h-stat"><div class="h-stat-val">{{ $total }}</div><div class="h-stat-lbl">Associations</div></div>
                <div class="h-stat"><div class="h-stat-val">{{ $sectors->count() }}</div><div class="h-stat-lbl">Sectors</div></div>
                <div class="h-stat"><div class="h-stat-val">{{ number_format(DB::table('associations')->where('is_active',1)->whereNull('deleted_at')->sum('member_count')) }}+</div><div class="h-stat-lbl">Total Members</div></div>
            </div>
        </div>
    </div>

    <form method="GET" action="/associations">
        <div class="filters">
            <input class="filter-input" type="text" name="q" value="{{ $q }}" placeholder="Search associations…" style="min-width:220px;">
            <div class="sector-chips">
                <a href="/associations{{ $sector ? '?q='.urlencode($q) : '' }}" class="chip {{ !$sector?'active':'' }}">All sectors</a>
                @foreach($sectors as $s)
                    <a href="/associations?sector={{ $s }}{{ $q?'&q='.urlencode($q):'' }}" class="chip chip-green {{ $sector===$s?'active':'' }}">{{ $sectorLabels[$s]??ucfirst($s) }}</a>
                @endforeach
            </div>
        </div>
    </form>

    <div class="results-meta">
        Showing <strong>{{ $assocs->count() }}</strong>{{ $assocs->count()===$total?'':' of '.$total }} associations{{ $sector?' in '.($sectorLabels[$sector]??$sector):'' }}{{ $q?' matching "'.e($q).'"':'' }}
    </div>

    @if($assocs->isEmpty())
        <div class="empty">No associations found for your search. <a href="/associations" style="color:var(--green);">Clear filters →</a></div>
    @else
        <div class="assoc-grid">
            @foreach($assocs as $a)
            <div class="assoc-card">
                @if($a->is_featured)<div class="featured-badge">Featured</div>@endif
                <div class="assoc-header">
                    <div class="assoc-logo">{{ $a->acronym ? strtoupper(substr($a->acronym,0,2)) : strtoupper(substr($a->name_en,0,2)) }}</div>
                    <div style="flex:1;min-width:0;">
                        <div class="assoc-name">{{ $a->acronym ? $a->acronym.' — '.$a->name_en : $a->name_en }}</div>
                        @if($a->name_fr && $a->name_fr !== $a->name_en)
                        <div class="assoc-acronym">{{ $a->name_fr }}</div>
                        @endif
                        <span class="assoc-sector-badge">{{ $sectorLabels[$a->sector]??ucfirst($a->sector) }}</span>
                    </div>
                </div>
                @if($a->description_en)
                <p class="assoc-desc">{{ $a->description_en }}</p>
                @endif
                <div class="assoc-meta">
                    @if($a->city || $a->region_id)
                    <div class="assoc-meta-item"><i data-lucide="map-pin" class="lic"></i> {{ trim(($a->city??'').($a->city && ($a->region_name??'') ? ', ' : '').($a->region_name??'')) }}</div>
                    @endif
                    @if($a->member_count)
                    <div class="assoc-meta-item"><i data-lucide="users" class="lic"></i> {{ number_format($a->member_count) }} members</div>
                    @endif
                    @if($a->founded_year)
                    <div class="assoc-meta-item"><i data-lucide="calendar" class="lic"></i> Est. {{ $a->founded_year }}</div>
                    @endif
                </div>
                <div class="assoc-footer">
                    <a href="/associations/{{ $a->slug }}" class="btn-sm btn-primary">View details</a>
                    @if($a->website)
                    <a href="{{ $a->website }}" target="_blank" rel="noopener" class="btn-sm btn-outline">Website ↗</a>
                    @endif
                    @if($a->email)
                    <a href="mailto:{{ $a->email }}" class="btn-sm btn-outline" style="margin-left:auto;"><i data-lucide="mail" class="lic"></i> Contact</a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>
@include('partials.footer')
</body>
</html>
