<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>{{ $community->name }} — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:1000px;margin:0 auto;padding:1.5rem;}
.banner{border-radius:var(--radius);padding:2rem;color:#fff;margin-bottom:1.5rem;display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:1rem;}
.banner-left .b-name{font-size:1.5rem;font-weight:900;margin-bottom:.2rem;}
.banner-left .b-tag{font-size:.88rem;opacity:.8;}
.banner-stats{display:flex;gap:1.5rem;}
.b-stat{text-align:right;}
.b-stat-num{font-size:1.2rem;font-weight:900;}
.b-stat-lbl{font-size:.7rem;opacity:.7;}
.grid2{display:grid;grid-template-columns:1fr 260px;gap:1.2rem;}
.card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);border:1px solid var(--border);margin-bottom:1rem;}
.card-head{padding:.7rem 1.1rem;font-weight:700;font-size:.88rem;border-bottom:1px solid var(--border);background:var(--light-bg);}
.card-body{padding:1.1rem;}
.post{border-bottom:1px solid var(--border);padding:1rem 0;}
.post:first-child{padding-top:0;}
.post:last-child{border-bottom:none;}
.post-header{display:flex;gap:.5rem;align-items:center;margin-bottom:.5rem;}
.post-avatar{width:32px;height:32px;border-radius:50%;background:var(--green);display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;color:#fff;flex-shrink:0;}
.post-meta{font-size:.76rem;color:var(--muted);}
.post-title{font-weight:700;font-size:.9rem;margin-bottom:.3rem;}
.post-body{font-size:.83rem;color:var(--text);line-height:1.6;}
.pinned-badge{display:inline-block;padding:1px 7px;border-radius:99px;font-size:.68rem;font-weight:700;background:var(--yellow);color:var(--dark);margin-right:.4rem;}
.type-badge{display:inline-block;padding:1px 7px;border-radius:99px;font-size:.68rem;font-weight:700;background:var(--light-bg);color:var(--muted);border:1px solid var(--border);}
.write-area{background:var(--light-bg);border-radius:var(--radius);padding:1rem;margin-bottom:1rem;}
.member-row{display:flex;align-items:center;gap:.5rem;padding:.3rem 0;font-size:.82rem;border-bottom:1px solid var(--border);}
.member-row:last-child{border-bottom:none;}
.m-avatar{width:28px;height:28px;border-radius:50%;background:var(--green);display:flex;align-items:center;justify-content:center;font-size:.68rem;font-weight:700;color:#fff;flex-shrink:0;}
.role-badge{font-size:.65rem;padding:1px 5px;border-radius:99px;border:1px solid var(--border);}
@media(max-width:700px){.grid2{grid-template-columns:1fr;}}
</style>

@php
$authUser = webUser();
$typeLabels = ['text'=>'Discussion','link'=>'Link','poll'=>'Poll','event'=>'Event','announcement'=>'Announcement','question'=>'Question'];
$typeColors = ['announcement'=>'#fcd116','text'=>'#dde2ea','question'=>'#dbeafe','event'=>'#d1fae5'];
$sectorIcons = ['ict'=>'laptop','cocoa'=>'candy','agriculture'=>'wheat','finance'=>'banknote','health'=>'heart-pulse','general'=>'globe','construction'=>'hard-hat','tourism'=>'plane','energy'=>'zap','mining'=>'pickaxe','textile'=>'spool','transport'=>'truck','agri_food'=>'salad','timber'=>'trees','palm_oil'=>'palmtree','other'=>'diamond'];
@endphp

<div class="page">
    <a href="/communities" style="font-size:.82rem;color:var(--muted);display:inline-flex;gap:.3rem;margin-bottom:.75rem;">← Communities</a>
    <div class="banner" style="background:{{ $community->cover_color }};">
        <div class="banner-left">
            <div style="font-size:2rem;margin-bottom:.3rem;"><i data-lucide="{{ $sectorIcons[$community->sector]??'diamond' }}" class="lic"></i></div>
            <div class="b-name">{{ $community->name }}</div>
            @if($community->tagline)<div class="b-tag">{{ $community->tagline }}</div>@endif
        </div>
        <div class="banner-stats">
            <div class="b-stat"><div class="b-stat-num">{{ number_format($community->member_count) }}</div><div class="b-stat-lbl">Members</div></div>
            <div class="b-stat"><div class="b-stat-num">{{ number_format($community->post_count) }}</div><div class="b-stat-lbl">Posts</div></div>
        </div>
    </div>

    <div class="grid2">
        <div>
            @if($isMember && $authUser)
            <div class="write-area">
                <form method="POST" action="/communities/{{ $community->slug }}/post">
                    @csrf
                    <div style="display:flex;gap:.6rem;align-items:flex-start;">
                        <div class="post-avatar">{{ strtoupper(substr($authUser['first_name']??'Y',0,1)) }}</div>
                        <div style="flex:1;">
                            <input type="text" name="title" placeholder="Post title (optional)" style="width:100%;padding:.4rem .65rem;border:1px solid var(--border);border-radius:7px;font-size:.83rem;margin-bottom:.45rem;font-family:inherit;box-sizing:border-box;outline:none;">
                            <textarea name="body" required placeholder="Share something with the community…" style="width:100%;padding:.45rem .65rem;border:1px solid var(--border);border-radius:7px;font-size:.83rem;resize:vertical;min-height:65px;font-family:inherit;box-sizing:border-box;outline:none;"></textarea>
                            <div style="display:flex;gap:.5rem;margin-top:.4rem;justify-content:space-between;align-items:center;flex-wrap:wrap;">
                                <select name="type" style="padding:.3rem .6rem;border:1px solid var(--border);border-radius:6px;font-size:.78rem;font-family:inherit;">
                                    @foreach($typeLabels as $k=>$v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                                </select>
                                <button type="submit" style="padding:.35rem .9rem;background:var(--green);color:#fff;border:none;border-radius:7px;font-size:.82rem;font-weight:700;cursor:pointer;">Post →</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            @elseif(!$isMember && $authUser)
            <div style="background:#fff;border-radius:var(--radius);padding:1rem;border:1px solid var(--border);margin-bottom:1rem;display:flex;justify-content:space-between;align-items:center;gap:.75rem;flex-wrap:wrap;">
                <div style="font-size:.85rem;color:var(--muted);">Join this community to post and interact with members.</div>
                <form method="POST" action="/communities/{{ $community->slug }}/join">
                    @csrf
                    <button type="submit" style="padding:.45rem 1.1rem;background:var(--green);color:#fff;border:none;border-radius:8px;font-size:.85rem;font-weight:700;cursor:pointer;">Join Community →</button>
                </form>
            </div>
            @endif

            <div class="card">
                <div class="card-head">Posts ({{ $posts->count() }})</div>
                <div class="card-body">
                    @forelse($posts as $post)
                    <div class="post">
                        <div class="post-header">
                            <div class="post-avatar">{{ strtoupper(substr($post->first_name??'A',0,1)) }}</div>
                            <div>
                                <span style="font-weight:600;font-size:.82rem;">{{ ($post->first_name??'').' '.($post->last_name??'') }}</span>
                                <div class="post-meta">{{ date('d M Y', strtotime($post->created_at)) }}</div>
                            </div>
                            <div style="margin-left:auto;display:flex;gap:.3rem;">
                                @if($post->is_pinned)<span class="pinned-badge"><i data-lucide="pin" class="lic"></i> Pinned</span>@endif
                                <span class="type-badge" style="background:{{ $typeColors[$post->type]??'#f3f4f6' }};">{{ $typeLabels[$post->type]??ucfirst($post->type) }}</span>
                            </div>
                        </div>
                        @if($post->title)<div class="post-title">{{ $post->title }}</div>@endif
                        <div class="post-body">{{ $post->body }}</div>
                        @if($post->likes_count > 0 || $post->comments_count > 0)
                        <div style="display:flex;gap:1rem;margin-top:.5rem;font-size:.75rem;color:var(--muted);">
                            @if($post->likes_count > 0)<span><i data-lucide="thumbs-up" class="lic"></i> {{ $post->likes_count }}</span>@endif
                            @if($post->comments_count > 0)<span><i data-lucide="message-circle" class="lic"></i> {{ $post->comments_count }}</span>@endif
                        </div>
                        @endif
                    </div>
                    @empty
                    <div style="text-align:center;padding:1.5rem;color:var(--muted);font-size:.85rem;">No posts yet. Be the first to share something!</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div>
            @if($community->description)
            <div class="card">
                <div class="card-head">About</div>
                <div class="card-body" style="font-size:.83rem;color:var(--text);line-height:1.6;">{{ $community->description }}</div>
            </div>
            @endif

            <div class="card">
                <div class="card-head">Members ({{ number_format($community->member_count) }})</div>
                <div class="card-body">
                    @foreach($members->take(12) as $m)
                    <div class="member-row">
                        <div class="m-avatar">{{ strtoupper(substr($m->first_name??'A',0,1)) }}</div>
                        <div style="flex:1;min-width:0;">
                            <span style="font-size:.82rem;font-weight:600;">{{ ($m->first_name??'').' '.($m->last_name??'') }}</span>
                        </div>
                        @if($m->role !== 'member')<span class="role-badge">{{ ucfirst($m->role) }}</span>@endif
                    </div>
                    @endforeach
                    @if($community->member_count > 12)<div style="font-size:.75rem;color:var(--muted);margin-top:.4rem;">and {{ number_format($community->member_count - 12) }} more…</div>@endif
                </div>
            </div>
        </div>
    </div>
</div>
@include('partials.footer')
</body>
</html>
