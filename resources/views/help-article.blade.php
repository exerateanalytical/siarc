<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>{{ $article->title_en }} — Help Centre — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.main{max-width:780px;margin:2rem auto;padding:0 1.5rem 3rem;}
.breadcrumb{font-size:.78rem;color:var(--muted);margin-bottom:1rem;}
.breadcrumb a{color:var(--muted);}
h1{font-size:1.5rem;font-weight:800;margin-bottom:.5rem;line-height:1.3;}
.meta{font-size:.78rem;color:var(--muted);margin-bottom:1.5rem;}
.body{line-height:1.8;font-size:.9rem;color:var(--text);}
.body h3{font-size:1rem;font-weight:700;margin:1.3rem 0 .4rem;}
.body p{margin-bottom:.8rem;}
.body ul,.body ol{padding-left:1.3rem;margin-bottom:.8rem;}
.body li{margin-bottom:.3rem;}
.body strong{font-weight:700;}
.helpful{background:var(--light-bg);border-radius:var(--radius);padding:1rem 1.2rem;margin:1.5rem 0;display:flex;align-items:center;gap:1rem;flex-wrap:wrap;}
.helpful span{font-size:.85rem;color:var(--muted);}
.helpful button{padding:.35rem .9rem;border-radius:7px;border:1px solid var(--border);background:var(--white);font-size:.8rem;cursor:pointer;}
.helpful button:hover{background:var(--green);color:#fff;border-color:var(--green);}
.related{margin-top:2rem;border-top:1px solid var(--border);padding-top:1.2rem;}
.related h2{font-size:.9rem;font-weight:700;margin-bottom:.7rem;}
.rel-item{display:block;padding:.4rem 0;font-size:.85rem;border-bottom:1px solid var(--border);}
.rel-item:hover{color:var(--green);}
.back{display:inline-block;padding:.5rem 1rem;border:1px solid var(--border);border-radius:7px;font-size:.82rem;margin-bottom:1.2rem;color:var(--text);}
</style>

<div class="main">
    <div class="breadcrumb"><a href="/">Home</a> › <a href="/help">Help Centre</a> › {{ Str::limit($article->title_en,50) }}</div>
    <h1>{{ $article->title_en }}</h1>
    <div class="meta"><i data-lucide="eye" class="lic"></i> {{ number_format($article->view_count ?? 0) }} views</div>

    <div class="body">{!! $article->body_en !!}</div>

    <div class="helpful">
        <span>Was this article helpful?</span>
        <button onclick="this.textContent='<i data-lucide="thumbs-up" class="lic"></i> Yes — thanks!';this.disabled=true;"><i data-lucide="thumbs-up" class="lic"></i> Yes</button>
        <button onclick="this.textContent='<i data-lucide="thumbs-down" class="lic"></i> Noted';this.disabled=true;"><i data-lucide="thumbs-down" class="lic"></i> No</button>
        <a href="/support" style="font-size:.8rem;color:var(--green);margin-left:auto;">Still need help? Open a ticket →</a>
    </div>

    @if($related->count())
    <div class="related">
        <h2>Related Articles</h2>
        @foreach($related as $r)
        <a class="rel-item" href="/help/{{ $r->slug }}">{{ $r->title_en }}</a>
        @endforeach
    </div>
    @endif
</div>
@include('partials.footer')
</body>
</html>
