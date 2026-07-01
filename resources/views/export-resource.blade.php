<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>{{ $resource->title }} — Export Hub — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:860px;margin:0 auto;padding:1.5rem;}
.back{font-size:.82rem;color:var(--muted);display:inline-flex;gap:.3rem;margin-bottom:1rem;}
.back:hover{color:var(--green);}
.article-card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);padding:2rem;border:1px solid var(--border);}
.article-cat{display:inline-block;padding:2px 10px;border-radius:99px;font-size:.72rem;font-weight:700;background:var(--light-bg);color:var(--muted);border:1px solid var(--border);margin-bottom:.75rem;}
.article-title{font-size:1.5rem;font-weight:900;color:var(--text);line-height:1.3;margin-bottom:.5rem;}
.article-meta{font-size:.78rem;color:var(--muted);margin-bottom:1.5rem;display:flex;gap:1rem;flex-wrap:wrap;}
.article-body{font-size:.9rem;color:var(--text);line-height:1.8;}
.related-title{font-weight:800;font-size:.95rem;color:var(--text);margin:2rem 0 .8rem;}
.related-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:.75rem;}
.related-card{background:#fff;border-radius:8px;padding:.9rem;border:1px solid var(--border);box-shadow:var(--shadow);font-size:.82rem;}
.related-card-title{font-weight:700;color:var(--text);margin-bottom:.25rem;}
.cta-strip{background:linear-gradient(135deg,#0c2340,#1e3a5f);border-radius:var(--radius);padding:1.5rem;color:#fff;text-align:center;margin-top:1.5rem;}
</style>

@php
$catLabels = ['customs'=>'Customs','certification'=>'Certification','trade_agreements'=>'Trade Agreements','markets'=>'Markets','financing'=>'Financing','packaging'=>'Packaging','labelling'=>'Labelling','hs_codes'=>'HS Codes','shipping'=>'Shipping','insurance'=>'Insurance','other'=>'Other'];
@endphp

<div class="page">
    <a class="back" href="/export-hub">← Export Hub</a>

    <div class="article-card">
        <div class="article-cat">{{ $catLabels[$resource->category]??ucfirst(str_replace('_',' ',$resource->category)) }}</div>
        @if($resource->is_featured)<span style="background:var(--yellow);color:var(--dark);padding:2px 8px;border-radius:99px;font-size:.68rem;font-weight:800;margin-left:.4rem;">Featured</span>@endif
        <div class="article-title">{{ $resource->title }}</div>
        <div class="article-meta">
            <span><i data-lucide="eye" class="lic"></i> {{ number_format($resource->view_count) }} reads</span>
            @if($resource->country)<span><i data-lucide="globe" class="lic"></i> {{ $resource->country }}</span>@endif
            <span><i data-lucide="calendar" class="lic"></i> {{ date('d M Y',strtotime($resource->updated_at)) }}</span>
        </div>
        <div class="article-body">{{ $resource->body }}</div>
    </div>

    @if($related->count() > 0)
    <div class="related-title">Related Guides</div>
    <div class="related-grid">
        @foreach($related as $r)
        <div class="related-card">
            <div class="related-card-title"><a href="/export-hub/{{ $r->slug }}" style="color:var(--text);">{{ $r->title }}</a></div>
            <div style="font-size:.75rem;color:var(--muted);">{{ $catLabels[$r->category]??ucfirst($r->category) }}</div>
        </div>
        @endforeach
    </div>
    @endif

    <div class="cta-strip">
        <div style="font-weight:800;font-size:1rem;margin-bottom:.4rem;"><i data-lucide="rocket" class="lic"></i> Check Your Export Readiness</div>
        <div style="font-size:.85rem;color:#93c5fd;margin-bottom:.9rem;">Take our free assessment and get a personalised action plan.</div>
        <a href="/export-hub/assessment" style="display:inline-block;padding:.5rem 1.3rem;background:var(--green);color:#fff;border-radius:7px;font-weight:700;font-size:.88rem;">Start Assessment →</a>
    </div>
</div>
@include('partials.footer')
</body>
</html>
