<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>{{ $assoc->acronym ?? $assoc->name_en }} — Business Associations — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:1000px;margin:0 auto;padding:1.5rem;}
.back{font-size:.82rem;color:var(--muted);margin-bottom:1rem;display:inline-flex;align-items:center;gap:.3rem;}
.back:hover{color:var(--green);}
.assoc-hero{background:linear-gradient(135deg,var(--dark),var(--mid));border-radius:var(--radius);padding:2rem;color:#fff;margin-bottom:1.5rem;display:flex;gap:1.5rem;align-items:center;}
.assoc-logo-lg{width:80px;height:80px;border-radius:14px;background:rgba(255,255,255,.1);display:flex;align-items:center;justify-content:center;font-weight:900;font-size:1.6rem;color:var(--yellow);flex-shrink:0;}
.assoc-name-lg{font-size:1.5rem;font-weight:900;line-height:1.2;}
.assoc-fullname{font-size:.9rem;color:#aab;margin-top:.3rem;}
.assoc-tags{display:flex;gap:.5rem;flex-wrap:wrap;margin-top:.75rem;}
.tag{padding:3px 12px;border-radius:99px;font-size:.72rem;font-weight:700;background:rgba(255,255,255,.12);color:#dde;}
.grid2{display:grid;grid-template-columns:1fr 280px;gap:1.5rem;}
.card{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);margin-bottom:1rem;}
.card-title{padding:.8rem 1.1rem;font-weight:700;font-size:.88rem;border-bottom:1px solid var(--border);background:var(--light-bg);}
.card-body{padding:1.1rem;}
.info-row{display:flex;gap:.5rem;padding:.4rem 0;border-bottom:1px solid var(--border);font-size:.84rem;}
.info-row:last-child{border-bottom:none;}
.info-lbl{color:var(--muted);min-width:120px;flex-shrink:0;}
.info-val{color:var(--text);font-weight:500;}
.related-card{padding:.8rem 1rem;display:flex;gap:.75rem;align-items:center;border-bottom:1px solid var(--border);}
.related-card:last-child{border-bottom:none;}
.rel-logo{width:36px;height:36px;border-radius:8px;background:var(--dark);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.72rem;color:var(--yellow);flex-shrink:0;}
.rel-name{font-size:.82rem;font-weight:600;color:var(--text);}
.rel-sub{font-size:.72rem;color:var(--muted);margin-top:1px;}
.btn-primary{display:inline-block;padding:.5rem 1.2rem;background:var(--green);color:#fff;border-radius:7px;font-size:.85rem;font-weight:600;}
.btn-primary:hover{background:#00962e;}
.btn-outline{display:inline-block;padding:.5rem 1.2rem;border:1px solid var(--border);color:var(--text);border-radius:7px;font-size:.85rem;font-weight:600;}
.btn-outline:hover{background:var(--light-bg);}
@media(max-width:700px){.grid2{grid-template-columns:1fr;}.assoc-hero{flex-direction:column;text-align:center;}}
</style>

@php
    $sectorLabels = ['agriculture'=>'Agriculture','commerce'=>'Commerce','industry'=>'Industry','services'=>'Services','technology'=>'Technology','finance'=>'Finance','health'=>'Health','construction'=>'Construction','transport'=>'Transport','mining'=>'Mining','tourism'=>'Tourism','energy'=>'Energy','legal'=>'Legal','education'=>'Education','agri-food'=>'Agri-food','ict'=>'ICT','other'=>'Other'];
@endphp

<div class="page">
    <a class="back" href="/associations">← Back to Associations</a>

    <div class="assoc-hero">
        <div class="assoc-logo-lg">{{ $assoc->acronym ? strtoupper(substr($assoc->acronym,0,2)) : strtoupper(substr($assoc->name_en,0,2)) }}</div>
        <div>
            <div class="assoc-name-lg">{{ $assoc->acronym ?? $assoc->name_en }}</div>
            @if($assoc->acronym)<div class="assoc-fullname">{{ $assoc->name_en }}</div>@endif
            @if($assoc->name_fr && $assoc->name_fr !== $assoc->name_en)<div class="assoc-fullname" style="font-size:.8rem;opacity:.7;">{{ $assoc->name_fr }}</div>@endif
            <div class="assoc-tags">
                <span class="tag">{{ $sectorLabels[$assoc->sector]??ucfirst($assoc->sector) }}</span>
                @if($assoc->city)<span class="tag"><i data-lucide="map-pin" class="lic"></i> {{ $assoc->city }}</span>@endif
                @if($assoc->founded_year)<span class="tag">Est. {{ $assoc->founded_year }}</span>@endif
                @if($assoc->member_count)<span class="tag"><i data-lucide="users" class="lic"></i> {{ number_format($assoc->member_count) }} members</span>@endif
            </div>
        </div>
    </div>

    <div class="grid2">
        <div>
            @if($assoc->description_en)
            <div class="card">
                <div class="card-title">About</div>
                <div class="card-body" style="font-size:.88rem;line-height:1.65;color:var(--text);">{{ $assoc->description_en }}</div>
            </div>
            @endif

            @if($assoc->description_fr && $assoc->description_fr !== $assoc->description_en)
            <div class="card">
                <div class="card-title">À propos (Français)</div>
                <div class="card-body" style="font-size:.88rem;line-height:1.65;color:var(--text);">{{ $assoc->description_fr }}</div>
            </div>
            @endif

            @if($related->count() > 0)
            <div class="card">
                <div class="card-title">Related Associations</div>
                @foreach($related as $r)
                <div class="related-card">
                    <div class="rel-logo">{{ strtoupper(substr($r->acronym??$r->name_en,0,2)) }}</div>
                    <div>
                        <div class="rel-name"><a href="/associations/{{ $r->slug }}" style="color:var(--text);">{{ $r->acronym ? $r->acronym.' — '.$r->name_en : $r->name_en }}</a></div>
                        <div class="rel-sub">{{ $sectorLabels[$r->sector]??ucfirst($r->sector) }}{{ $r->member_count?' · '.number_format($r->member_count).' members':'' }}</div>
                    </div>
                </div>
                @endforeach
                <div style="padding:.6rem 1rem;"><a href="/associations?sector={{ $assoc->sector }}" style="font-size:.78rem;color:var(--green);">View all {{ $sectorLabels[$assoc->sector]??$assoc->sector }} associations →</a></div>
            </div>
            @endif
        </div>

        <div>
            <div class="card">
                <div class="card-title">Contact & Info</div>
                <div class="card-body">
                    @if($assoc->city || $assoc->region_name)
                    <div class="info-row"><span class="info-lbl">Location</span><span class="info-val">{{ $assoc->city }}{{ $assoc->city && $assoc->region_name?', ':'' }}{{ $assoc->region_name??'' }}</span></div>
                    @endif
                    @if($assoc->founded_year)
                    <div class="info-row"><span class="info-lbl">Founded</span><span class="info-val">{{ $assoc->founded_year }}</span></div>
                    @endif
                    @if($assoc->member_count)
                    <div class="info-row"><span class="info-lbl">Members</span><span class="info-val">{{ number_format($assoc->member_count) }}+</span></div>
                    @endif
                    @if($assoc->email)
                    <div class="info-row"><span class="info-lbl">Email</span><span class="info-val"><a href="mailto:{{ $assoc->email }}" style="color:var(--green);">{{ $assoc->email }}</a></span></div>
                    @endif
                    @if($assoc->phone)
                    <div class="info-row"><span class="info-lbl">Phone</span><span class="info-val"><a href="tel:{{ $assoc->phone }}" style="color:var(--green);">{{ $assoc->phone }}</a></span></div>
                    @endif
                    @if($assoc->website)
                    <div class="info-row"><span class="info-lbl">Website</span><span class="info-val"><a href="{{ $assoc->website }}" target="_blank" rel="noopener" style="color:var(--green);">{{ parse_url($assoc->website,PHP_URL_HOST) }}</a></span></div>
                    @endif
                    <div class="info-row"><span class="info-lbl">Sector</span><span class="info-val">{{ $sectorLabels[$assoc->sector]??ucfirst($assoc->sector) }}</span></div>
                </div>
            </div>

            <div class="card">
                <div class="card-title">Discover</div>
                <div class="card-body" style="display:flex;flex-direction:column;gap:.5rem;">
                    <a href="/associations" class="btn-outline" style="text-align:center;display:block;">All Associations</a>
                    <a href="/associations?sector={{ $assoc->sector }}" class="btn-outline" style="text-align:center;display:block;">{{ $sectorLabels[$assoc->sector]??ucfirst($assoc->sector) }} Sector</a>
                    <a href="/collabcam" class="btn-primary" style="text-align:center;display:block;">Collaborate via CollabCam</a>
                    <a href="/" class="btn-outline" style="text-align:center;display:block;">Browse Companies</a>
                </div>
            </div>
        </div>
    </div>
</div>
@include('partials.footer')
</body>
</html>
