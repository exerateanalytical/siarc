<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>{{ $post->title_en }} — Galerie virtuelle de l'artisanat du Cameroun</title>
<meta name="description" content="{{ strip_tags($post->excerpt_en ?? '') }}">
</head>
<body>
@include('partials.nav')
<style>
.main{max-width:820px;margin:2rem auto;padding:0 1.5rem 3rem;}
.breadcrumb{font-size:.78rem;color:var(--muted);margin-bottom:1rem;}
.breadcrumb a{color:var(--muted);}
h1{font-size:1.7rem;font-weight:800;line-height:1.3;margin-bottom:.5rem;}
.meta{font-size:.8rem;color:var(--muted);margin-bottom:1.5rem;display:flex;gap:1rem;flex-wrap:wrap;}
.article-body{line-height:1.8;font-size:.93rem;color:var(--text);}
.article-body h3{font-size:1.05rem;font-weight:700;margin:1.5rem 0 .5rem;}
.article-body p{margin-bottom:.9rem;}
.article-body ul,.article-body ol{padding-left:1.4rem;margin-bottom:.9rem;}
.article-body li{margin-bottom:.3rem;}
.article-body strong{font-weight:700;}
.share-bar{display:flex;gap:.5rem;margin:1.5rem 0;padding:1rem;background:var(--light-bg);border-radius:var(--radius);align-items:center;}
.share-bar span{font-size:.82rem;color:var(--muted);margin-right:.3rem;}
.share-btn{padding:.35rem .85rem;border-radius:7px;font-size:.78rem;font-weight:600;text-decoration:none;border:1px solid var(--border);color:var(--text);}
.related{margin-top:2.5rem;}
.related h2{font-size:1rem;font-weight:700;margin-bottom:.8rem;}
.related-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:.8rem;}
.related-card{background:var(--white);border-radius:var(--radius);padding:1rem;box-shadow:var(--shadow);}
.related-card-title{font-size:.85rem;font-weight:700;margin-bottom:.3rem;line-height:1.4;}
.related-card-date{font-size:.73rem;color:var(--muted);}
.cta-box{background:linear-gradient(135deg,var(--green),#009040);border-radius:var(--radius);padding:1.5rem;color:#fff;margin:2rem 0;}
.cta-box h3{font-size:1rem;font-weight:700;margin-bottom:.3rem;}
.cta-box p{font-size:.83rem;opacity:.9;margin-bottom:.8rem;}
.cta-box a{display:inline-block;padding:.5rem 1.2rem;background:#fff;color:var(--green);border-radius:7px;font-weight:700;font-size:.85rem;text-decoration:none;}
</style>

<div class="main">
    <div class="breadcrumb"><a href="/">Home</a> › <a href="/blog">Insights</a> › {{ Str::limit($post->title_en,50) }}</div>
    <h1>{{ $post->title_en }}</h1>
    <div class="meta">
        <span>{{ $post->published_at ? date('d F Y', strtotime($post->published_at)) : '' }}</span>
        <span>Galerie virtuelle de l'artisanat du Cameroun Research</span>
        <span><i data-lucide="eye" class="lic"></i> {{ number_format($post->view_count ?? 0) }} views</span>
    </div>

    <div class="article-body">{!! $post->body_en !!}</div>

    <div class="share-bar">
        <span>Share:</span>
        <a class="share-btn" href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(url()->current()) }}" target="_blank" rel="noopener">LinkedIn</a>
        <a class="share-btn" href="https://twitter.com/intent/tweet?text={{ urlencode($post->title_en) }}&url={{ urlencode(url()->current()) }}" target="_blank" rel="noopener">X / Twitter</a>
        <a class="share-btn" onclick="navigator.clipboard.writeText('{{ url()->current() }}');alert('Link copied!');">Copy Link</a>
    </div>

    <div class="cta-box">
        <h3>Ready to invest in Cameroonian companies?</h3>
        <p>Browse CMF-regulated share and bond offerings with as little as 50,000 XAF.</p>
        <a href="/offerings">View Offerings →</a>
    </div>

    @if($related->count())
    <div class="related">
        <h2>Related Articles</h2>
        <div class="related-grid">
            @foreach($related as $r)
            <a class="related-card" href="/blog/{{ $r->slug }}">
                <div class="related-card-title">{{ $r->title_en }}</div>
                <div class="related-card-date">{{ $r->published_at ? date('d M Y', strtotime($r->published_at)) : '' }}</div>
            </a>
            @endforeach
        </div>
    </div>
    @endif
</div>
@include('partials.footer')
</body>
</html>
