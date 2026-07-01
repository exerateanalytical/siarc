<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Market Insights — Galerie virtuelle de l'artisanat du Cameroun</title></head>
<body>
@include('partials.nav')
<style>
.hero{background:linear-gradient(135deg,var(--dark),var(--mid));padding:2.5rem 2rem;text-align:center;color:#fff;}
.hero h1{font-size:1.8rem;font-weight:800;margin-bottom:.4rem;}
.hero p{color:#aab;font-size:.9rem;}
.search-bar{max-width:500px;margin:.8rem auto 0;display:flex;gap:.4rem;}
.search-bar input{flex:1;padding:.55rem 1rem;border-radius:7px;border:none;font-size:.9rem;background:rgba(255,255,255,.15);color:#fff;}
.search-bar input::placeholder{color:rgba(255,255,255,.5);}
.search-bar button{padding:.55rem 1.2rem;background:var(--green);color:#fff;border:none;border-radius:7px;font-weight:600;cursor:pointer;}
.main{max-width:1100px;margin:0 auto;padding:1.5rem;display:grid;grid-template-columns:1fr 280px;gap:1.5rem;}
@media(max-width:760px){.main{grid-template-columns:1fr;}}
.posts{display:flex;flex-direction:column;gap:1rem;}
.post-card{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden;display:flex;gap:0;transition:transform .15s;}
.post-card:hover{transform:translateY(-2px);box-shadow:var(--shadow-hover);}
.post-body{padding:1.1rem;flex:1;}
.post-cat{font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--green);margin-bottom:.3rem;}
.post-title{font-size:1rem;font-weight:700;margin-bottom:.35rem;line-height:1.4;}
.post-excerpt{font-size:.82rem;color:var(--muted);line-height:1.55;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
.post-meta{font-size:.75rem;color:var(--muted);margin-top:.6rem;display:flex;gap:.8rem;}
.sidebar-card{background:var(--white);border-radius:var(--radius);padding:1.1rem;box-shadow:var(--shadow);margin-bottom:1rem;}
.sidebar-card h3{font-size:.88rem;font-weight:700;margin-bottom:.7rem;}
.cat-link{display:block;padding:.3rem 0;font-size:.82rem;color:var(--text);border-bottom:1px solid var(--border);}
.cat-link:hover{color:var(--green);}
.empty{text-align:center;padding:3rem;color:var(--muted);}
</style>

<div class="hero">
    <h1>Market Insights</h1>
    <p>Investment research, company news, and economic analysis for Cameroon</p>
    <form class="search-bar" method="GET" action="/blog">
        <input type="text" name="q" placeholder="Search articles…" value="{{ $q }}">
        <button type="submit">Search</button>
    </form>
</div>

<div class="main">
    <div>
        @if($q)
        <p style="font-size:.84rem;color:var(--muted);margin-bottom:.8rem;">{{ $posts->count() }} result(s) for "{{ $q }}" · <a href="/blog">Clear</a></p>
        @endif
        @if($posts->isEmpty())
        <div class="empty"><div style="font-size:2.5rem;margin-bottom:.6rem"><i data-lucide="newspaper" class="lic"></i></div><p>No articles found.</p></div>
        @else
        <div class="posts">
            @foreach($posts as $post)
            <a class="post-card" href="/blog/{{ $post->slug }}">
                <div class="post-body">
                    @if($post->category_id)
                    <div class="post-cat">{{ $post->category_id }}</div>
                    @endif
                    <div class="post-title">{{ $post->title_en }}</div>
                    <div class="post-excerpt">{{ strip_tags($post->excerpt_en ?: $post->body_en) }}</div>
                    <div class="post-meta">
                        <span>{{ $post->author_id ? 'Author' : 'Galerie virtuelle de l\'artisanat du Cameroun' }}</span>
                        <span>{{ $post->published_at ? date('d M Y', strtotime($post->published_at)) : '' }}</span>
                        <span><i data-lucide="eye" class="lic"></i> {{ number_format($post->view_count ?? 0) }}</span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
        @endif
    </div>
    <div>
        <div class="sidebar-card">
            <h3>Categories</h3>
            <a class="cat-link" href="/blog" style="{{ !$category ? 'color:var(--green);font-weight:700' : '' }}">All Articles</a>
            @foreach($categories as $cat)
            <a class="cat-link" href="/blog?category={{ $cat->slug }}" style="{{ $category===$cat->slug ? 'color:var(--green);font-weight:700' : '' }}">{{ $cat->name_en }}</a>
            @endforeach
        </div>
        <div class="sidebar-card">
            <h3>Quick Links</h3>
            <a class="cat-link" href="/offerings">View Offerings →</a>
            <a class="cat-link" href="/how-it-works">How to Invest →</a>
            <a class="cat-link" href="/help">Help Centre →</a>
        </div>
    </div>
</div>
@include('partials.footer')
</body>
</html>
