<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>{{ $fed->name }} — Federation — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:1100px;margin:0 auto;padding:1.5rem;}
.fed-hero{border-radius:var(--radius);padding:2.5rem 2rem;color:#fff;margin-bottom:1.5rem;position:relative;overflow:hidden;}
.fed-hero::before{content:'';position:absolute;inset:0;background:rgba(0,0,0,.45);}
.hero-content{position:relative;z-index:1;}
.hero-badge{display:inline-block;padding:3px 10px;border-radius:99px;font-size:.68rem;font-weight:700;margin-bottom:.6rem;}
.hero-name{font-size:1.8rem;font-weight:900;margin-bottom:.4rem;}
.hero-desc{font-size:.9rem;color:#ddd;max-width:600px;}
.hero-stats{display:flex;gap:2rem;margin-top:1.2rem;flex-wrap:wrap;}
.h-stat-val{font-size:1.3rem;font-weight:800;}
.h-stat-lbl{font-size:.72rem;opacity:.8;}
.layout{display:grid;grid-template-columns:1fr 280px;gap:1.5rem;}
.section-card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);margin-bottom:1.2rem;border:1px solid var(--border);}
.section-hd{padding:.8rem 1.1rem;font-weight:700;font-size:.88rem;border-bottom:1px solid var(--border);background:var(--light-bg);display:flex;justify-content:space-between;align-items:center;}
.section-body{padding:1rem;}
.post-item{padding:.9rem 0;border-bottom:1px solid var(--border);}
.post-item:last-child{border-bottom:none;}
.post-type{display:inline-block;padding:1px 7px;border-radius:99px;font-size:.65rem;font-weight:700;background:var(--light-bg);color:var(--muted);}
.post-type-announcement{background:#fff3cd;color:#856404;}
.post-type-discussion{background:#cce5ff;color:#0056b3;}
.post-type-document{background:#d4edda;color:#166534;}
.post-title{font-weight:700;font-size:.88rem;color:var(--text);margin:.3rem 0 .2rem;}
.post-meta{font-size:.72rem;color:var(--muted);}
.post-pinned{font-size:.65rem;background:var(--yellow);color:var(--dark);padding:1px 6px;border-radius:99px;font-weight:700;}
.member-item{display:flex;gap:.7rem;align-items:center;padding:.55rem 0;border-bottom:1px solid var(--border);}
.member-item:last-child{border-bottom:none;}
.member-logo{width:34px;height:34px;border-radius:7px;background:linear-gradient(135deg,var(--dark),var(--mid));display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.72rem;color:var(--yellow);flex-shrink:0;}
.member-name{font-size:.83rem;font-weight:700;color:var(--text);}
.member-role{font-size:.67rem;color:var(--muted);}
.side-info{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);padding:1.2rem;margin-bottom:1rem;border:1px solid var(--border);}
.info-row{display:flex;justify-content:space-between;padding:.4rem 0;border-bottom:1px solid var(--border);font-size:.83rem;}
.info-row:last-child{border-bottom:none;}
.info-lbl{color:var(--muted);}
.info-val{font-weight:600;color:var(--text);}
.join-box{background:#fff;border:2px solid var(--green);border-radius:var(--radius);padding:1.2rem;margin-bottom:1rem;}
.post-form{padding:1rem;}
.form-group{margin-bottom:.8rem;}
.form-label{display:block;font-size:.8rem;font-weight:600;margin-bottom:.3rem;}
.form-control{width:100%;padding:.5rem .75rem;border:1px solid var(--border);border-radius:7px;font-size:.85rem;outline:none;font-family:inherit;box-sizing:border-box;}
.form-control:focus{border-color:var(--green);}
textarea.form-control{resize:vertical;min-height:80px;}
.btn-post{padding:.45rem 1rem;background:var(--green);color:#fff;border:none;border-radius:7px;font-size:.83rem;font-weight:700;cursor:pointer;}
.back{font-size:.82rem;color:var(--muted);display:inline-flex;align-items:center;gap:.3rem;margin-bottom:1rem;}
.back:hover{color:var(--green);}
@media(max-width:700px){.layout{grid-template-columns:1fr;}.hero-stats{gap:1rem;}}
</style>

@php
$authUser = session('auth_user');
$sectorColors = ['cocoa'=>'#7c5c1e','timber'=>'#2d6a1e','palm_oil'=>'#d97706','ict'=>'#0284c7','finance'=>'#7c3aed','health'=>'#dc2626','construction'=>'#b45309','transport'=>'#0891b2','mining'=>'#374151','tourism'=>'#059669','energy'=>'#ca8a04','textile'=>'#7c3aed','agri_food'=>'#166534','other'=>'#4b5563'];
$sectorIcons = ['cocoa'=>'candy','timber'=>'trees','palm_oil'=>'palmtree','ict'=>'laptop','finance'=>'landmark','health'=>'heart-pulse','construction'=>'hard-hat','transport'=>'truck','mining'=>'pickaxe','tourism'=>'plane','energy'=>'zap','textile'=>'shopping-basket','agri_food'=>'wheat','other'=>'building-2'];
$color = $sectorColors[$fed->sector]??'#374151';
$icon  = $sectorIcons[$fed->sector]??'building-2';
$members = DB::table('federation_members')
    ->join('companies','federation_members.company_id','=','companies.id')
    ->where('federation_members.federation_id',$fed->id)
    ->where('federation_members.status','active')
    ->whereNull('companies.deleted_at')
    ->select('companies.id','companies.name','companies.slug','companies.verification_status','federation_members.role')
    ->orderByRaw("CASE federation_members.role WHEN 'admin' THEN 0 WHEN 'moderator' THEN 1 ELSE 2 END")
    ->orderBy('companies.name')
    ->get();
$posts = DB::table('federation_posts')
    ->join('companies','federation_posts.company_id','=','companies.id')
    ->where('federation_posts.federation_id',$fed->id)
    ->whereNull('federation_posts.deleted_at')
    ->select('federation_posts.*','companies.name as co_name','companies.slug as co_slug')
    ->orderByRaw('is_pinned DESC')->orderByDesc('federation_posts.created_at')
    ->limit(20)->get();
$myCompanies = $authUser ? DB::table('company_users')
    ->join('companies','company_users.company_id','=','companies.id')
    ->where('company_users.user_id',$authUser['id'])
    ->where('company_users.status','approved')
    ->whereNull('companies.deleted_at')
    ->select('companies.id','companies.name')->get() : collect();
$isMember = $authUser && $myCompanies->count() > 0 &&
    DB::table('federation_members')
        ->where('federation_id',$fed->id)
        ->whereIn('company_id',$myCompanies->pluck('id')->toArray())
        ->where('status','active')->exists();
DB::table('federations')->where('id',$fed->id)->increment('view_count');
@endphp

<div class="page">
    <a class="back" href="/federations">← All Federations</a>

    <div class="fed-hero" style="background:linear-gradient(135deg,{{ $color }},{{ $color }}88);">
        <div class="hero-content">
            <div class="hero-badge" style="background:rgba(255,255,255,.2);color:#fff;">{{ $icon }} {{ ucfirst(str_replace('_',' ',$fed->sector)) }}</div>
            <div class="hero-name">{{ $fed->name }}</div>
            @if($fed->acronym)<div style="font-size:.85rem;opacity:.8;margin-bottom:.3rem;">{{ $fed->acronym }}</div>@endif
            <div class="hero-desc">{{ Str::limit($fed->description, 200) }}</div>
            <div class="hero-stats">
                <div><div class="h-stat-val">{{ number_format($fed->member_count) }}</div><div class="h-stat-lbl">Member Companies</div></div>
                <div><div class="h-stat-val">{{ $posts->count() }}</div><div class="h-stat-lbl">Posts</div></div>
                <div><div class="h-stat-val">{{ number_format($fed->view_count+1) }}</div><div class="h-stat-lbl">Views</div></div>
            </div>
        </div>
    </div>

    <div class="layout">
        <div>
            @if($isMember)
            <div class="section-card">
                <div class="section-hd">Post to Federation</div>
                <div class="post-form">
                    <form method="POST" action="/federations/{{ $fed->slug }}/post">
                        @csrf
                        <div class="form-group">
                            <div style="display:flex;gap:.6rem;">
                                <select class="form-control" name="company_id" style="max-width:200px;" required>
                                    @foreach($myCompanies as $mc)<option value="{{ $mc->id }}">{{ $mc->name }}</option>@endforeach
                                </select>
                                <select class="form-control" name="type" style="max-width:160px;">
                                    @foreach(['discussion','announcement','document','event','opportunity'] as $t)
                                        <option value="{{ $t }}">{{ ucfirst($t) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-control" name="title" placeholder="Title (optional)">
                        </div>
                        <div class="form-group">
                            <textarea class="form-control" name="body" required placeholder="Share news, ask a question, or post an opportunity with the federation…"></textarea>
                        </div>
                        <button type="submit" class="btn-post">Post to Federation →</button>
                    </form>
                </div>
            </div>
            @endif

            <div class="section-card">
                <div class="section-hd">Federation Posts <span style="font-size:.78rem;color:var(--muted);font-weight:400;">{{ $posts->count() }}</span></div>
                <div class="section-body" style="padding:.5rem 1.1rem;">
                    @forelse($posts as $p)
                    <div class="post-item">
                        <div style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;">
                            <span class="post-type post-type-{{ $p->type }}">{{ ucfirst($p->type) }}</span>
                            @if($p->is_pinned)<span class="post-pinned"><i data-lucide="pin" class="lic"></i> Pinned</span>@endif
                        </div>
                        @if($p->title)<div class="post-title">{{ $p->title }}</div>@endif
                        <div style="font-size:.84rem;color:var(--text);margin:.3rem 0;line-height:1.55;">{{ Str::limit($p->body, 200) }}</div>
                        <div class="post-meta">
                            <a href="/companies/{{ $p->co_slug }}" style="color:var(--green);font-weight:600;">{{ $p->co_name }}</a>
                            · {{ date('d M Y', strtotime($p->created_at)) }}
                            · {{ number_format($p->view_count) }} views
                        </div>
                    </div>
                    @empty
                    <div style="text-align:center;padding:2rem;color:var(--muted);">No posts yet. Be the first to share with this federation.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div>
            @if(!$isMember)
            <div class="join-box">
                <div style="font-weight:800;font-size:.9rem;margin-bottom:.5rem;">{{ $icon }} Join this Federation</div>
                <div style="font-size:.82rem;color:var(--muted);margin-bottom:.8rem;">Membership gives your company access to shared resources, joint procurement, and the federation community.</div>
                @if($authUser && $myCompanies->count() > 0)
                <form method="POST" action="/federations/{{ $fed->slug }}/join">
                    @csrf
                    <select class="form-control" name="company_id" style="margin-bottom:.6rem;" required>
                        @foreach($myCompanies as $mc)<option value="{{ $mc->id }}">{{ $mc->name }}</option>@endforeach
                    </select>
                    <button type="submit" style="width:100%;padding:.5rem;background:var(--green);color:#fff;border:none;border-radius:7px;font-size:.85rem;font-weight:700;cursor:pointer;">Request Membership →</button>
                </form>
                @elseif($authUser)
                <a href="/" style="display:block;text-align:center;background:var(--green);color:#fff;padding:.5rem;border-radius:7px;font-size:.85rem;font-weight:700;">Claim a Company →</a>
                @else
                <a href="/auth/login" style="display:block;text-align:center;background:var(--green);color:#fff;padding:.5rem;border-radius:7px;font-size:.85rem;font-weight:700;">Sign In to Join →</a>
                @endif
            </div>
            @else
            <div class="side-info" style="border-color:var(--green);background:rgba(0,122,51,.04);">
                <div style="color:var(--green);font-weight:700;font-size:.85rem;"><i data-lucide="check" class="lic"></i> Your company is a member of this federation.</div>
            </div>
            @endif

            <div class="side-info">
                <div style="font-weight:700;font-size:.88rem;margin-bottom:.8rem;">Federation Info</div>
                <div class="info-row"><span class="info-lbl">Sector</span><span class="info-val">{{ ucfirst(str_replace('_',' ',$fed->sector)) }}</span></div>
                <div class="info-row"><span class="info-lbl">Members</span><span class="info-val">{{ number_format($fed->member_count) }}</span></div>
                <div class="info-row"><span class="info-lbl">Status</span><span class="info-val">{{ ucfirst($fed->status) }}</span></div>
                @if($fed->website)<div class="info-row"><span class="info-lbl">Website</span><a href="{{ $fed->website }}" target="_blank" style="color:var(--green);font-size:.82rem;">{{ parse_url($fed->website,PHP_URL_HOST) }}</a></div>@endif
                @if($fed->email)<div class="info-row"><span class="info-lbl">Contact</span><a href="mailto:{{ $fed->email }}" style="color:var(--green);font-size:.82rem;">Email</a></div>@endif
            </div>

            <div class="section-card">
                <div class="section-hd">Members ({{ $members->count() }})</div>
                <div class="section-body" style="padding:.5rem 1.1rem;max-height:400px;overflow-y:auto;">
                    @foreach($members as $m)
                    <div class="member-item">
                        <div class="member-logo">{{ strtoupper(substr($m->name,0,2)) }}</div>
                        <div style="flex:1;min-width:0;">
                            <div class="member-name"><a href="/companies/{{ $m->slug }}" style="color:var(--text);">{{ $m->name }}</a></div>
                            <div class="member-role">{{ ucfirst($m->role) }}{{ $m->verification_status==='verified'?' · <i data-lucide="check" class="lic"></i> Verified':'' }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@include('partials.footer')
</body>
</html>
