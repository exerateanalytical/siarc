<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>{{ $resource->title }} — Knowledge Center — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:860px;margin:0 auto;padding:1.5rem;}
.back{font-size:.82rem;color:var(--muted);display:inline-flex;gap:.3rem;margin-bottom:1rem;}
.back:hover{color:var(--green);}
.article-card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);padding:2rem;border:1px solid var(--border);}
.article-cat{display:inline-block;padding:2px 10px;border-radius:99px;font-size:.72rem;font-weight:700;background:var(--light-bg);color:var(--muted);border:1px solid var(--border);}
.article-title{font-size:1.5rem;font-weight:900;color:var(--text);line-height:1.3;margin:.6rem 0 .5rem;}
.article-desc{font-size:.92rem;color:var(--muted);margin-bottom:1rem;line-height:1.6;}
.article-meta{font-size:.78rem;color:var(--muted);margin-bottom:1.5rem;display:flex;gap:1rem;flex-wrap:wrap;padding-bottom:1rem;border-bottom:1px solid var(--border);}
.article-body{font-size:.9rem;color:var(--text);line-height:1.8;white-space:pre-wrap;}
.dl-cta{display:inline-block;margin-top:1.2rem;padding:.55rem 1.4rem;background:var(--green);color:#fff;border-radius:8px;font-weight:700;font-size:.88rem;}
.related-title{font-weight:800;font-size:.95rem;color:var(--text);margin:2rem 0 .8rem;}
.related-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:.75rem;}
.related-card{background:#fff;border-radius:8px;padding:.9rem;border:1px solid var(--border);box-shadow:var(--shadow);font-size:.82rem;}
.related-card-title{font-weight:700;color:var(--text);margin-bottom:.25rem;}
.cta-strip{background:linear-gradient(135deg,#007a33,#00592a);border-radius:var(--radius);padding:1.5rem;color:#fff;text-align:center;margin-top:1.5rem;}
</style>

@php
$catLabels = ['template'=>'Template','guide'=>'Guide','regulation'=>'Regulation','standard'=>'Standard','case_study'=>'Case Study','report'=>'Report','whitepaper'=>'Whitepaper','tool'=>'Tool','checklist'=>'Checklist','training'=>'Training','faq'=>'FAQ','other'=>'Other'];
$formatIcons = ['article'=>'file-text','document'=>'pen-line','spreadsheet'=>'bar-chart-3','presentation'=>'files','pdf'=>'book','video'=>'clapperboard','link'=>'link','tool'=>'wrench','checklist'=>'check-circle-2'];
@endphp

<div class="page">
    <a class="back" href="/knowledge">← Knowledge Center</a>

    <div class="article-card">
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
            <span class="article-cat"><i data-lucide="{{ $formatIcons[$resource->format]??'file-text' }}" class="lic"></i> {{ $catLabels[$resource->category]??ucfirst(str_replace('_',' ',$resource->category)) }}</span>
            @if($resource->is_featured)<span class="article-cat" style="background:var(--yellow);color:var(--dark);border-color:#e6bd0a;"><i data-lucide="star" class="lic"></i> Featured</span>@endif
            <span class="article-cat" style="background:#d1fae5;color:#065f46;border-color:#6ee7b7;">{{ $resource->is_free ? 'FREE' : 'PREMIUM' }}</span>
        </div>
        <div class="article-title">{{ $resource->title }}</div>
        @if($resource->description)<div class="article-desc">{{ $resource->description }}</div>@endif
        <div class="article-meta">
            <span><i data-lucide="eye" class="lic"></i> {{ number_format($resource->view_count) }} views</span>
            <span><i data-lucide="download" class="lic"></i> {{ number_format($resource->download_count) }} downloads</span>
            <span><i data-lucide="calendar" class="lic"></i> Updated {{ date('d M Y', strtotime($resource->updated_at)) }}</span>
        </div>
        @if($resource->body)<div class="article-body">{{ $resource->body }}</div>@endif
        @if($resource->external_url)
        <a class="dl-cta" href="{{ $resource->external_url }}" target="_blank" rel="noopener"><i data-lucide="download" class="lic"></i> Download / Open Resource →</a>
        @endif
    </div>

    @if($related->count() > 0)
    <div class="related-title">Related Resources</div>
    <div class="related-grid">
        @foreach($related as $r)
        <a href="/knowledge/{{ $r->slug }}" class="related-card" style="display:block;">
            <div class="related-card-title">{{ $r->title }}</div>
            <div style="font-size:.75rem;color:var(--muted);"><i data-lucide="{{ $formatIcons[$r->format]??'file-text' }}" class="lic"></i> {{ $catLabels[$r->category]??ucfirst($r->category) }}</div>
        </a>
        @endforeach
    </div>
    @endif

    <div class="cta-strip">
        <div style="font-weight:800;font-size:1rem;margin-bottom:.4rem;"><i data-lucide="message-circle" class="lic"></i> Connect with peers</div>
        <div style="font-size:.85rem;opacity:.9;margin-bottom:.9rem;">Join a business community or attend an event to grow your network.</div>
        <a href="/communities" style="display:inline-block;padding:.5rem 1.3rem;background:#fff;color:var(--green);border-radius:7px;font-weight:700;font-size:.88rem;margin-right:.5rem;">Browse Communities →</a>
        <a href="/events" style="display:inline-block;padding:.5rem 1.3rem;border:1px solid rgba(255,255,255,.4);color:#fff;border-radius:7px;font-weight:700;font-size:.88rem;">See Events →</a>
    </div>
</div>
@include('partials.footer')
</body>
</html>
