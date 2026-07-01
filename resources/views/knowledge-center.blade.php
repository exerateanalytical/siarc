<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Knowledge Center — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:1100px;margin:0 auto;padding:1.5rem;}
.hero{background:linear-gradient(135deg,#0c2340,#1e3a5f);border-radius:var(--radius);padding:2.5rem 2rem;margin-bottom:1.5rem;color:#fff;}
.hero-title{font-size:1.8rem;font-weight:900;margin-bottom:.4rem;}
.hero-sub{font-size:.9rem;color:#93c5fd;}
.cat-tabs{display:flex;gap:.4rem;flex-wrap:wrap;margin-bottom:1rem;}
.cat-tab{padding:.3rem .9rem;border-radius:99px;font-size:.78rem;font-weight:600;cursor:pointer;border:1px solid var(--border);background:#fff;color:var(--muted);transition:all .15s;}
.cat-tab.active,.cat-tab:hover{background:var(--dark);color:#fff;border-color:var(--dark);}
.section-title{font-weight:800;font-size:1rem;color:var(--text);margin:1.2rem 0 .8rem;}
.featured-strip{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1rem;margin-bottom:1rem;}
.feat-card{background:linear-gradient(135deg,#fff,#f8fafc);border-radius:var(--radius);box-shadow:var(--shadow);border:1px solid var(--border);border-left:4px solid var(--green);padding:1.2rem;transition:box-shadow .15s;}
.feat-card:hover{box-shadow:var(--shadow-hover);}
.feat-badge{display:inline-block;padding:1px 8px;border-radius:99px;font-size:.66rem;font-weight:800;background:var(--yellow);color:var(--dark);margin-bottom:.5rem;}
.res-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1rem;}
.res-card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);border:1px solid var(--border);padding:1.1rem;transition:box-shadow .15s;display:flex;flex-direction:column;}
.res-card:hover{box-shadow:var(--shadow-hover);}
.res-format-icon{font-size:1.5rem;margin-bottom:.5rem;}
.res-title{font-weight:800;font-size:.92rem;color:var(--text);margin-bottom:.35rem;line-height:1.3;}
.res-desc{font-size:.8rem;color:var(--muted);line-height:1.5;margin-bottom:.7rem;flex:1;}
.res-meta-row{display:flex;justify-content:space-between;align-items:center;margin-top:auto;}
.badge{display:inline-block;padding:2px 9px;border-radius:99px;font-size:.7rem;font-weight:700;border:1px solid var(--border);background:var(--light-bg);color:var(--muted);}
.badge-free{background:#d1fae5;color:#065f46;border-color:#6ee7b7;}
.res-stats{font-size:.72rem;color:var(--muted);}
</style>

@php
$q        = request('q','');
$category = request('category','');
$query = DB::table('knowledge_resources')->where('is_published',1);
if ($q)        $query->where('title','like',"%$q%");
if ($category) $query->where('category',$category);
$resources = $query->orderByRaw('is_featured DESC')->orderBy('title')->get();
$featured = (!$q && !$category) ? $resources->where('is_featured',1)->take(3) : collect();
$featuredIds = $featured->pluck('id')->all();
$rest = $resources->whereNotIn('id',$featuredIds);

$catLabels = ['template'=>'Template','guide'=>'Guide','regulation'=>'Regulation','standard'=>'Standard','case_study'=>'Case Study','report'=>'Report','whitepaper'=>'Whitepaper','tool'=>'Tool','checklist'=>'Checklist','training'=>'Training','faq'=>'FAQ','other'=>'Other'];
$formatIcons = ['article'=>'file-text','document'=>'pen-line','spreadsheet'=>'bar-chart-3','presentation'=>'files','pdf'=>'book','video'=>'clapperboard','link'=>'link','tool'=>'wrench','checklist'=>'check-circle-2'];
@endphp

<div class="page">
    <div class="hero">
        <div class="hero-title"><i data-lucide="book-open" class="lic"></i> Knowledge Center</div>
        <div class="hero-sub">Templates, guides, regulations &amp; tools for Cameroonian businesses</div>
    </div>

    <div style="display:flex;gap:.5rem;margin-bottom:1rem;flex-wrap:wrap;">
        <form method="GET" action="/knowledge" style="display:flex;gap:.5rem;flex:1;flex-wrap:wrap;">
            <input type="text" name="q" value="{{ $q }}" placeholder="Search templates, guides, regulations…" style="flex:1;min-width:200px;padding:.45rem .8rem;border:1px solid var(--border);border-radius:7px;font-size:.85rem;outline:none;">
            @if($category)<input type="hidden" name="category" value="{{ $category }}">@endif
            <button type="submit" style="padding:.45rem 1.1rem;background:var(--green);color:#fff;border:none;border-radius:7px;font-size:.83rem;font-weight:600;cursor:pointer;">Search</button>
        </form>
        <span style="font-size:.78rem;color:var(--muted);align-self:center;">{{ $resources->count() }} resources</span>
    </div>

    <div class="cat-tabs">
        <a class="cat-tab {{ !$category?'active':'' }}" href="/knowledge">All</a>
        @foreach($catLabels as $k=>$v)<a class="cat-tab {{ $category===$k?'active':'' }}" href="/knowledge?category={{ $k }}{{ $q?'&q='.urlencode($q):'' }}">{{ $v }}</a>@endforeach
    </div>

    @if($featured->count() > 0)
    <div class="section-title"><i data-lucide="star" class="lic"></i> Featured Resources</div>
    <div class="featured-strip">
        @foreach($featured as $r)
        <a href="/knowledge/{{ $r->slug }}" class="feat-card">
            <span class="feat-badge">Featured</span>
            <div style="font-size:.7rem;color:var(--muted);font-weight:600;margin-bottom:.3rem;"><i data-lucide="{{ $formatIcons[$r->format]??'file-text' }}" class="lic"></i> {{ $catLabels[$r->category]??ucfirst($r->category) }}</div>
            <div class="res-title">{{ $r->title }}</div>
            <div class="res-desc">{{ Str::limit($r->description??'',120) }}</div>
            <div class="res-meta-row">
                <span class="badge badge-free">{{ $r->is_free ? 'FREE' : 'PREMIUM' }}</span>
                <span class="res-stats"><i data-lucide="eye" class="lic"></i> {{ number_format($r->view_count) }} · <i data-lucide="download" class="lic"></i> {{ number_format($r->download_count) }}</span>
            </div>
        </a>
        @endforeach
    </div>
    @endif

    @if($rest->count() > 0)
    <div class="section-title">{{ $featured->count() > 0 ? 'All Resources' : ($category ? ($catLabels[$category]??'').'s' : 'Resources') }}</div>
    <div class="res-grid">
        @foreach($rest as $r)
        <a href="/knowledge/{{ $r->slug }}" class="res-card">
            <div class="res-format-icon"><i data-lucide="{{ $formatIcons[$r->format]??'file-text' }}" class="lic"></i></div>
            <div class="res-title">{{ $r->title }}</div>
            <div class="res-desc">{{ Str::limit($r->description??'',110) }}</div>
            <div style="margin-bottom:.6rem;"><span class="badge">{{ $catLabels[$r->category]??ucfirst($r->category) }}</span></div>
            <div class="res-meta-row">
                <span class="badge badge-free">{{ $r->is_free ? 'FREE' : 'PREMIUM' }}</span>
                <span class="res-stats"><i data-lucide="eye" class="lic"></i> {{ number_format($r->view_count) }}</span>
            </div>
        </a>
        @endforeach
    </div>
    @elseif($featured->count() === 0)
    <div style="text-align:center;padding:3rem;background:#fff;border-radius:var(--radius);border:1px solid var(--border);">
        <div style="font-size:2rem;margin-bottom:.5rem;"><i data-lucide="book-open" class="lic"></i></div>
        <div style="font-weight:700;margin-bottom:.3rem;">No resources found</div>
        <div style="font-size:.85rem;color:var(--muted);">Try a different category or search term.</div>
    </div>
    @endif
</div>
@include('partials.footer')
</body>
</html>
