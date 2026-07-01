<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Help Centre — Galerie virtuelle de l'artisanat du Cameroun</title></head>
<body>
@include('partials.nav')
<style>
.hero{background:linear-gradient(135deg,var(--dark),var(--mid));padding:2.5rem 2rem;text-align:center;color:#fff;}
.hero h1{font-size:1.8rem;font-weight:800;margin-bottom:.4rem;}
.search-bar{max-width:500px;margin:.8rem auto 0;display:flex;gap:.4rem;}
.search-bar input{flex:1;padding:.55rem 1rem;border-radius:7px;border:none;font-size:.9rem;background:rgba(255,255,255,.15);color:#fff;}
.search-bar input::placeholder{color:rgba(255,255,255,.5);}
.search-bar button{padding:.55rem 1.2rem;background:var(--green);color:#fff;border:none;border-radius:7px;font-weight:600;cursor:pointer;}
.main{max-width:1000px;margin:0 auto;padding:1.5rem;}
.popular{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:.8rem;margin-bottom:2rem;}
.pop-card{background:var(--white);border-radius:var(--radius);padding:1.1rem;box-shadow:var(--shadow);border-left:3px solid var(--green);}
.pop-card h3{font-size:.88rem;font-weight:700;margin-bottom:.3rem;}
.pop-card p{font-size:.78rem;color:var(--muted);margin:0;}
.section-label{font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--muted);margin-bottom:.8rem;}
.article-list{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden;margin-bottom:1.5rem;}
.article-row{display:flex;align-items:center;gap:.8rem;padding:.75rem 1rem;border-bottom:1px solid var(--border);transition:background .12s;}
.article-row:last-child{border-bottom:none;}
.article-row:hover{background:var(--light-bg);}
.article-row-title{font-size:.88rem;font-weight:600;flex:1;}
.article-row-views{font-size:.75rem;color:var(--muted);white-space:nowrap;}
.contact-box{background:var(--white);border-radius:var(--radius);padding:1.5rem;box-shadow:var(--shadow);text-align:center;margin-top:1.5rem;}
.contact-box h2{font-size:1rem;font-weight:700;margin-bottom:.4rem;}
.contact-box p{font-size:.83rem;color:var(--muted);margin-bottom:.8rem;}
.contact-box a{display:inline-block;padding:.55rem 1.3rem;background:var(--green);color:#fff;border-radius:7px;font-weight:700;font-size:.85rem;text-decoration:none;}
.empty{text-align:center;padding:3rem;color:var(--muted);}
</style>

<div class="hero">
    <h1>Help Centre</h1>
    <p style="color:#aab">Find answers about investing, accounts, jobs, and the platform</p>
    <form class="search-bar" method="GET" action="/help">
        <input type="text" name="q" placeholder="Search help articles…" value="{{ $q }}">
        <button type="submit">Search</button>
    </form>
</div>

<div class="main">
    @if($q)
    <p style="font-size:.84rem;color:var(--muted);margin-bottom:.8rem;">{{ $articles->count() }} result(s) for "{{ $q }}" · <a href="/help">Clear</a></p>
    @endif

    @if(!$q)
    <div class="section-label">Most Read</div>
    <div class="popular">
        @foreach($articles->sortByDesc('view_count')->take(4) as $art)
        <a class="pop-card" href="/help/{{ $art->slug }}">
            <h3>{{ $art->title_en }}</h3>
            <p>{{ Str::limit(strip_tags($art->body_en ?? ''), 80) }}</p>
        </a>
        @endforeach
    </div>
    <div class="section-label">All Articles</div>
    @endif

    @if($articles->isEmpty())
    <div class="empty"><div style="font-size:2.5rem;margin-bottom:.6rem"><i data-lucide="search" class="lic"></i></div><p>No articles found for "{{ $q }}".</p><a href="/help">Back to Help Centre</a></div>
    @else
    <div class="article-list">
        @foreach($articles as $art)
        <a class="article-row" href="/help/{{ $art->slug }}">
            <div class="article-row-title">{{ $art->title_en }}</div>
            <div class="article-row-views"><i data-lucide="eye" class="lic"></i> {{ number_format($art->view_count ?? 0) }}</div>
        </a>
        @endforeach
    </div>
    @endif

    <div class="contact-box">
        <h2>Didn't find what you need?</h2>
        <p>Our support team responds to tickets within 24 hours on business days.</p>
        <a href="/support">Open a Support Ticket</a>
    </div>
</div>
@include('partials.footer')
</body>
</html>
