<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Digital Business Cards — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:1000px;margin:0 auto;padding:1.5rem;}
.hero{background:linear-gradient(135deg,#0f1623,#334155);border-radius:var(--radius);padding:2.5rem 2rem;margin-bottom:1.5rem;color:#fff;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;}
.hero-title{font-size:1.8rem;font-weight:900;margin-bottom:.4rem;}
.hero-sub{font-size:.9rem;color:#cbd5e1;}
.btn-white{padding:.55rem 1.3rem;background:#fff;color:#0f1623;border-radius:8px;font-weight:700;font-size:.88rem;border:none;cursor:pointer;}
.btn-outline{padding:.55rem 1.3rem;border:1px solid rgba(255,255,255,.3);color:#fff;border-radius:8px;font-weight:600;font-size:.88rem;cursor:pointer;background:none;text-decoration:none;}
.section-title{font-weight:800;font-size:1rem;color:var(--text);margin:1.2rem 0 .8rem;}
.cards-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1rem;}
.biz-card{border-radius:14px;overflow:hidden;box-shadow:var(--shadow);transition:transform .15s,box-shadow .15s;background:#fff;border:1px solid var(--border);}
.biz-card:hover{transform:translateY(-3px);box-shadow:var(--shadow-hover);}
.biz-top{padding:1.3rem 1.2rem;color:#fff;position:relative;}
.biz-avatar{width:52px;height:52px;border-radius:50%;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:1.2rem;font-weight:800;margin-bottom:.6rem;border:2px solid rgba(255,255,255,.4);}
.biz-name{font-size:1.1rem;font-weight:900;line-height:1.2;}
.biz-job{font-size:.8rem;opacity:.9;margin-top:1px;}
.biz-bottom{padding:.9rem 1.2rem;}
.biz-company{font-size:.82rem;font-weight:700;color:var(--text);}
.biz-tagline{font-size:.76rem;color:var(--muted);margin-top:.2rem;line-height:1.4;}
.biz-stats{display:flex;gap:1rem;margin-top:.6rem;font-size:.72rem;color:var(--muted);}
/* modal */
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:200;align-items:center;justify-content:center;}
.modal-overlay.open{display:flex;}
.modal{background:#fff;border-radius:var(--radius);padding:1.5rem;width:min(540px,95vw);max-height:90vh;overflow-y:auto;}
.form-group{margin-bottom:.8rem;}
.form-label{display:block;font-size:.8rem;font-weight:600;margin-bottom:.3rem;}
.form-control{width:100%;padding:.45rem .75rem;border:1px solid var(--border);border-radius:7px;font-size:.85rem;outline:none;font-family:inherit;box-sizing:border-box;}
.form-control:focus{border-color:var(--green);}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:.6rem;}
@media(max-width:600px){.form-row{grid-template-columns:1fr;}}
</style>

@php
$authUser = webUser();
$myCards = $authUser ? DB::table('digital_cards')->where('user_id',$authUser->id)->orderByDesc('created_at')->get() : collect();
$publicCards = DB::table('digital_cards')->where('is_public',1)->orderByDesc('view_count')->limit(24)->get();
$myCompanies = $authUser ? DB::table('company_users')
    ->join('companies','company_users.company_id','=','companies.id')
    ->where('company_users.user_id',$authUser->id)->where('company_users.is_active',1)
    ->whereNull('companies.deleted_at')->select('companies.id','companies.name')->get() : collect();
@endphp

<div class="page">
    <div class="hero">
        <div>
            <div class="hero-title"><i data-lucide="contact" class="lic"></i> Digital Business Cards</div>
            <div class="hero-sub">Create a shareable digital card with a QR code — your professional identity, one scan away</div>
        </div>
        @if($authUser)
        <button class="btn-white" onclick="document.getElementById('createModal').classList.add('open')">+ Create My Card</button>
        @else
        <a href="/auth/login" class="btn-outline">Sign In to Create</a>
        @endif
    </div>

    @if($myCards->count() > 0)
    <div class="section-title">My Cards</div>
    <div class="cards-grid">
        @foreach($myCards as $c)
        <a href="/card/{{ $c->slug }}" class="biz-card">
            <div class="biz-top" style="background:linear-gradient(135deg,{{ $c->theme_color }},{{ $c->theme_color }}cc);">
                <div class="biz-avatar">{{ $c->initials ?: strtoupper(substr($c->display_name,0,2)) }}</div>
                <div class="biz-name">{{ $c->display_name }}</div>
                @if($c->job_title)<div class="biz-job">{{ $c->job_title }}</div>@endif
            </div>
            <div class="biz-bottom">
                @if($c->company_name)<div class="biz-company">{{ $c->company_name }}</div>@endif
                @if($c->tagline)<div class="biz-tagline">{{ Str::limit($c->tagline,70) }}</div>@endif
                <div class="biz-stats"><span><i data-lucide="eye" class="lic"></i> {{ number_format($c->view_count) }}</span><span>↗ {{ number_format($c->share_count) }} shares</span></div>
            </div>
        </a>
        @endforeach
    </div>
    @endif

    <div class="section-title">{{ $myCards->count() > 0 ? 'Discover Cards' : 'Featured Cards' }}</div>
    @if($publicCards->isEmpty())
    <div style="text-align:center;padding:3rem;background:#fff;border-radius:var(--radius);border:1px solid var(--border);">
        <div style="font-size:2rem;margin-bottom:.5rem;"><i data-lucide="contact" class="lic"></i></div>
        <div style="font-weight:700;">No cards yet</div>
        <div style="font-size:.85rem;color:var(--muted);">Be the first to create a digital business card.</div>
    </div>
    @else
    <div class="cards-grid">
        @foreach($publicCards as $c)
        <a href="/card/{{ $c->slug }}" class="biz-card">
            <div class="biz-top" style="background:linear-gradient(135deg,{{ $c->theme_color }},{{ $c->theme_color }}cc);">
                <div class="biz-avatar">{{ $c->initials ?: strtoupper(substr($c->display_name,0,2)) }}</div>
                <div class="biz-name">{{ $c->display_name }}</div>
                @if($c->job_title)<div class="biz-job">{{ $c->job_title }}</div>@endif
            </div>
            <div class="biz-bottom">
                @if($c->company_name)<div class="biz-company">{{ $c->company_name }}</div>@endif
                @if($c->tagline)<div class="biz-tagline">{{ Str::limit($c->tagline,70) }}</div>@endif
                <div class="biz-stats"><span><i data-lucide="eye" class="lic"></i> {{ number_format($c->view_count) }}</span></div>
            </div>
        </a>
        @endforeach
    </div>
    @endif
</div>

@if($authUser)
<div class="modal-overlay" id="createModal">
    <div class="modal">
        <div style="font-weight:800;font-size:1rem;margin-bottom:1rem;"><i data-lucide="contact" class="lic"></i> Create Your Digital Card</div>
        <form method="POST" action="/cards">
            @csrf
            <div class="form-row">
                <div class="form-group"><label class="form-label">Display Name *</label><input type="text" class="form-control" name="display_name" required value="{{ trim(($authUser['first_name']??'').' '.($authUser['last_name']??'')) }}"></div>
                <div class="form-group"><label class="form-label">Job Title</label><input type="text" class="form-control" name="job_title" placeholder="e.g. CEO"></div>
            </div>
            <div class="form-group"><label class="form-label">Company</label>
                @if($myCompanies->count() > 0)
                <select class="form-control" name="company_id" onchange="document.getElementById('cn').value=this.options[this.selectedIndex].dataset.name||''">
                    <option value="" data-name="">— None / Custom —</option>
                    @foreach($myCompanies as $mc)<option value="{{ $mc->id }}" data-name="{{ $mc->name }}">{{ $mc->name }}</option>@endforeach
                </select>
                @endif
            </div>
            <div class="form-group"><label class="form-label">Company Name (display)</label><input type="text" class="form-control" id="cn" name="company_name" placeholder="Shown on the card"></div>
            <div class="form-group"><label class="form-label">Tagline</label><input type="text" class="form-control" name="tagline" placeholder="A short professional tagline"></div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Email</label><input type="email" class="form-control" name="email" value="{{ $authUser['email']??'' }}"></div>
                <div class="form-group"><label class="form-label">Phone</label><input type="text" class="form-control" name="phone" placeholder="+237 …"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">WhatsApp</label><input type="text" class="form-control" name="whatsapp" placeholder="+237 …"></div>
                <div class="form-group"><label class="form-label">Website</label><input type="text" class="form-control" name="website" placeholder="https://…"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">City</label><input type="text" class="form-control" name="city" placeholder="e.g. Douala"></div>
                <div class="form-group"><label class="form-label">LinkedIn</label><input type="text" class="form-control" name="linkedin" placeholder="profile URL"></div>
            </div>
            <div class="form-group"><label class="form-label">Card Color</label>
                <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
                    @foreach(['#007a33','#0284c7','#6d28d9','#be185d','#92400e','#0f1623','#16a34a','#ce1126'] as $i=>$col)
                    <label style="cursor:pointer;display:flex;align-items:center;">
                        <input type="radio" name="theme_color" value="{{ $col }}" {{ $i===0?'checked':'' }} style="margin-right:3px;">
                        <span style="display:inline-block;width:24px;height:24px;border-radius:50%;background:{{ $col }};"></span>
                    </label>
                    @endforeach
                </div>
            </div>
            <div style="display:flex;gap:.6rem;justify-content:flex-end;margin-top:.5rem;">
                <button type="button" style="padding:.6rem 1.2rem;border:1px solid var(--border);background:#fff;border-radius:8px;font-weight:600;cursor:pointer;" onclick="document.getElementById('createModal').classList.remove('open')">Cancel</button>
                <button type="submit" style="padding:.6rem 1.5rem;background:var(--green);color:#fff;border:none;border-radius:8px;font-weight:700;cursor:pointer;">Create Card →</button>
            </div>
        </form>
    </div>
</div>
@endif
@include('partials.footer')
</body>
</html>
