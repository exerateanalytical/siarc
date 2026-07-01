<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Messages — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:720px;margin:0 auto;padding:1.5rem;}
.h-title{font-size:1.5rem;font-weight:900;margin-bottom:.2rem;}
.subtitle{font-size:.85rem;color:var(--muted);margin-bottom:1.3rem;}
.thread-list{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);border:1px solid var(--border);overflow:hidden;}
.thread{display:flex;align-items:center;gap:.85rem;padding:.9rem 1.2rem;border-bottom:1px solid var(--border);text-decoration:none;color:var(--text);transition:background .12s;}
.thread:last-child{border-bottom:none;}
.thread:hover{background:var(--light-bg);}
.avatar{width:46px;height:46px;border-radius:50%;background:linear-gradient(135deg,#0f1623,#334155);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:.9rem;flex-shrink:0;}
.t-name{font-weight:700;font-size:.92rem;}
.t-preview{font-size:.8rem;color:var(--muted);margin-top:.15rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:380px;}
.t-preview.unread{color:var(--text);font-weight:600;}
.t-right{margin-left:auto;text-align:right;flex-shrink:0;}
.t-time{font-size:.72rem;color:var(--muted);}
.badge{display:inline-block;min-width:18px;height:18px;border-radius:99px;background:var(--green);color:#fff;font-size:.66rem;font-weight:800;text-align:center;line-height:18px;padding:0 5px;margin-top:.3rem;}
.empty{text-align:center;padding:3rem;background:#fff;border-radius:var(--radius);border:1px solid var(--border);}
</style>

<div class="page">
    <div class="h-title"><i data-lucide="message-circle" style="width:22px;height:22px;display:inline;vertical-align:-3px;"></i> Messages</div>
    <p class="subtitle">Your conversations with employers and candidates.</p>

    @if(empty($threads))
    <div class="empty">
        <i data-lucide="message-circle" style="width:38px;height:38px;color:var(--muted);margin-bottom:.4rem;"></i>
        <div style="font-weight:700;margin-bottom:.3rem;">No conversations yet</div>
        <div style="font-size:.85rem;color:var(--muted);">Message a candidate from the <a href="/talent" style="color:var(--green);">Talent Directory</a>, or reply when someone contacts you.</div>
    </div>
    @else
    <div class="thread-list">
        @foreach($threads as $t)
        <a href="/messages/{{ $t['other']->id }}" class="thread">
            <div class="avatar">{{ strtoupper(substr($t['other']->first_name??'?',0,1).substr($t['other']->last_name??'',0,1)) }}</div>
            <div style="min-width:0;flex:1;">
                <div class="t-name">{{ trim(($t['other']->first_name??'').' '.($t['other']->last_name??'')) }}</div>
                <div class="t-preview {{ $t['unread']>0 ? 'unread' : '' }}">
                    @if($t['last']){{ $t['last']->sender_id === $user->id ? 'You: ' : '' }}{{ Str::limit($t['last']->body, 60) }}@else No messages yet @endif
                </div>
            </div>
            <div class="t-right">
                @if($t['at'])<div class="t-time">{{ \Carbon\Carbon::parse($t['at'])->diffForHumans(null, true) }}</div>@endif
                @if($t['unread']>0)<div class="badge">{{ $t['unread'] }}</div>@endif
            </div>
        </a>
        @endforeach
    </div>
    @endif
</div>
@include('partials.footer')
</body>
</html>
