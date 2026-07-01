<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>{{ $q ? 'Search: '.$q : 'Search' }} — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:900px;margin:0 auto;padding:1.5rem;}
.search-head{margin-bottom:1.2rem;}
.search-title{font-size:1.4rem;font-weight:900;}
.search-sub{font-size:.85rem;color:var(--muted);margin-top:.2rem;}
.search-box{display:flex;gap:.5rem;margin:1rem 0 1.5rem;}
.search-input{flex:1;padding:.6rem 1rem;border:1px solid var(--border);border-radius:9px;font-size:.95rem;outline:none;}
.search-input:focus{border-color:var(--green);}
.search-btn{padding:.6rem 1.4rem;background:var(--green);color:#fff;border:none;border-radius:9px;font-weight:700;cursor:pointer;}
.group{margin-bottom:1.4rem;}
.group-head{display:flex;align-items:center;gap:.5rem;font-weight:800;font-size:.95rem;color:var(--text);margin-bottom:.6rem;}
.group-count{font-size:.72rem;color:var(--muted);font-weight:600;background:var(--light-bg);padding:1px 8px;border-radius:99px;}
.result-card{background:#fff;border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden;}
.result-row{display:flex;justify-content:space-between;align-items:center;gap:.5rem;padding:.7rem 1rem;border-bottom:1px solid var(--border);text-decoration:none;color:var(--text);transition:background .12s;}
.result-row:last-child{border-bottom:none;}
.result-row:hover{background:var(--light-bg);}
.result-label{font-weight:600;font-size:.88rem;}
.result-meta{font-size:.75rem;color:var(--muted);white-space:nowrap;}
.empty{text-align:center;padding:3rem;background:#fff;border-radius:var(--radius);border:1px solid var(--border);}
</style>

<div class="page">
    <div class="search-head">
        <div class="search-title"><i data-lucide="search" class="lic"></i> Search</div>
        @if($q)<div class="search-sub">{{ $totalResults }} result{{ $totalResults!=1?'s':'' }} for "<strong>{{ $q }}</strong>" across all modules</div>@endif
    </div>

    <form method="GET" action="/search" class="search-box">
        <input type="text" name="q" value="{{ $q }}" class="search-input" placeholder="Search companies, tenders, events, knowledge, innovation, assets…" autofocus>
        <button type="submit" class="search-btn">Search</button>
    </form>

    @if(!$q || strlen($q) < 2)
    <div class="empty">
        <div style="font-size:2rem;margin-bottom:.5rem;"><i data-lucide="search" class="lic"></i></div>
        <div style="font-weight:700;margin-bottom:.3rem;">Search the entire platform</div>
        <div style="font-size:.85rem;color:var(--muted);">Type at least 2 characters to search companies, tenders, investment opportunities, events, knowledge resources, innovation projects, assets, logistics, federations, communities, associations, compliance, and business cards.</div>
    </div>
    @elseif($totalResults === 0)
    <div class="empty">
        <div style="font-size:2rem;margin-bottom:.5rem;"><i data-lucide="help-circle" class="lic"></i></div>
        <div style="font-weight:700;margin-bottom:.3rem;">No results for "{{ $q }}"</div>
        <div style="font-size:.85rem;color:var(--muted);">Try a different term or browse the modules from the navigation bar.</div>
    </div>
    @else
        @foreach($groups as [$label, $icon, $items])
            @if($items->count() > 0)
            <div class="group">
                <div class="group-head">{{ $icon }} {{ $label }} <span class="group-count">{{ $items->count() }}</span></div>
                <div class="result-card">
                    @foreach($items as $it)
                    <a href="{{ $it['url'] }}" class="result-row">
                        <span class="result-label">{{ $it['label'] }}</span>
                        @if($it['meta'])<span class="result-meta">{{ $it['meta'] }}</span>@endif
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        @endforeach
    @endif
</div>
@include('partials.footer')
</body>
</html>
