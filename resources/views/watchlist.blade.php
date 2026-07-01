<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>My Watchlist — Galerie virtuelle de l'artisanat du Cameroun</title></head>
<body>
@include('partials.nav')
<style>
.page{max-width:960px;margin:0 auto;padding:1.5rem;}
.page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:1.2rem;}
.page-header h1{font-size:1.3rem;font-weight:800;}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1rem;}
.card{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden;position:relative;}
.card-header{padding:1rem 1rem .5rem;display:flex;gap:.7rem;align-items:flex-start;}
.logo{width:44px;height:44px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1rem;font-weight:800;color:#fff;flex-shrink:0;}
.company-name{font-size:.92rem;font-weight:700;line-height:1.3;}
.card-body{padding:.2rem 1rem .6rem;font-size:.78rem;color:var(--muted);line-height:1.5;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
.card-footer{padding:.6rem 1rem;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;}
.rm-btn{background:none;border:1px solid var(--border);border-radius:6px;padding:.3rem .7rem;font-size:.73rem;cursor:pointer;color:var(--muted);}
.rm-btn:hover{border-color:var(--red);color:var(--red);}
.vbadge{font-size:.68rem;padding:2px 7px;border-radius:99px;font-weight:700;text-transform:uppercase;}
.vs-certified{background:#d4edda;color:#007a33;}.vs-verified{background:#cce5ff;color:#0056b3;}.vs-basic{background:#fff3cd;color:#856404;}.vs-unverified{background:#f8f9fa;color:#6c757d;}
.added{font-size:.71rem;color:var(--muted);}
.empty{text-align:center;padding:4rem 2rem;color:var(--muted);}
</style>

<div class="page">
    <div class="page-header">
        <h1>My Watchlist <span style="font-size:.85rem;color:var(--muted);font-weight:400;">({{ $watchlist->count() }})</span></h1>
        <a href="/" style="font-size:.83rem;color:var(--green);">+ Add Companies</a>
    </div>

    @if(session('success'))<div style="background:#d4edda;border-radius:var(--radius);padding:.7rem 1rem;font-size:.84rem;color:#155724;margin-bottom:1rem;">{{ session('success') }}</div>@endif

    @if($watchlist->isEmpty())
    <div class="empty">
        <div style="font-size:3rem;margin-bottom:.7rem"><i data-lucide="star" class="lic"></i></div>
        <h3>Your watchlist is empty</h3>
        <p style="margin:.4rem 0 1rem;">Browse companies and click the watchlist button to save them here.</p>
        <a href="/" style="display:inline-block;padding:.55rem 1.3rem;background:var(--green);color:#fff;border-radius:7px;font-weight:700;font-size:.85rem;text-decoration:none;">Browse Companies</a>
    </div>
    @else
    <div class="grid">
        @foreach($watchlist as $c)
        @php
            $clrs=['#007a33','#ce1126','#0056b3','#7b2d8b','#c0392b','#16a085','#d35400'];
            $clr=$clrs[crc32($c->id)%count($clrs)];
            $ini=strtoupper(substr($c->trade_name?:$c->name,0,2));
            $vsC=match($c->verification_status??'unverified'){'certified'=>'vs-certified','verified'=>'vs-verified','basic'=>'vs-basic',default=>'vs-unverified'};
        @endphp
        <div class="card">
            <a href="/companies/{{ $c->slug }}" style="text-decoration:none;color:inherit;">
                <div class="card-header">
                    <div class="logo" style="background:{{ $clr }}">{{ $ini }}</div>
                    <div>
                        <div class="company-name">{{ $c->name }}</div>
                        @if($c->trade_name && $c->trade_name!==$c->name)<div style="font-size:.75rem;color:var(--muted);">{{ $c->trade_name }}</div>@endif
                    </div>
                </div>
                <div class="card-body">{{ $c->description_en ?: $c->description_fr }}</div>
            </a>
            <div class="card-footer">
                <span class="vbadge {{ $vsC }}">{{ ucfirst($c->verification_status??'unverified') }}</span>
                <form method="POST" action="/watchlist/toggle" style="display:inline;">
                    @csrf
                    <input type="hidden" name="company_id" value="{{ $c->id }}">
                    <button type="submit" class="rm-btn"><i data-lucide="x" class="lic"></i> Remove</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@include('partials.footer')
</body>
</html>
