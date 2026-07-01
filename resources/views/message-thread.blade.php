<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>{{ trim(($other->first_name??'').' '.($other->last_name??'')) }} — Messages — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:680px;margin:0 auto;padding:1.5rem 1.5rem 1rem;}
.thread-head{display:flex;align-items:center;gap:.8rem;margin-bottom:1rem;}
.back{font-size:1.3rem;color:var(--muted);text-decoration:none;}
.avatar{width:42px;height:42px;border-radius:50%;background:linear-gradient(135deg,#0f1623,#334155);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:.85rem;flex-shrink:0;}
.peer-name{font-weight:800;font-size:1.05rem;}
.peer-link{font-size:.76rem;color:var(--green);}
.msgs{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);border:1px solid var(--border);padding:1.2rem;min-height:340px;max-height:60vh;overflow-y:auto;display:flex;flex-direction:column;gap:.55rem;}
.bubble{max-width:74%;padding:.55rem .85rem;border-radius:14px;font-size:.88rem;line-height:1.5;white-space:pre-wrap;word-wrap:break-word;}
.mine{align-self:flex-end;background:var(--green);color:#fff;border-bottom-right-radius:4px;}
.theirs{align-self:flex-start;background:var(--light-bg);color:var(--text);border-bottom-left-radius:4px;}
.meta{font-size:.66rem;opacity:.7;margin-top:.2rem;display:block;}
.day-sep{align-self:center;font-size:.7rem;color:var(--muted);background:var(--light-bg);padding:2px 10px;border-radius:99px;margin:.3rem 0;}
.composer{display:flex;gap:.5rem;margin-top:.8rem;}
.composer textarea{flex:1;padding:.6rem .8rem;border:1px solid var(--border);border-radius:10px;font-size:.88rem;font-family:inherit;resize:none;min-height:46px;max-height:140px;outline:none;}
.composer textarea:focus{border-color:var(--green);}
.send-btn{padding:0 1.3rem;background:var(--green);color:#fff;border:none;border-radius:10px;font-weight:700;font-size:.88rem;cursor:pointer;}
.empty-msgs{text-align:center;color:var(--muted);font-size:.85rem;margin:auto;}
</style>

@php
$fullName = trim(($other->first_name??'').' '.($other->last_name??''));
$lastDay = null;
@endphp

<div class="page">
    <div class="thread-head">
        <a href="/messages" class="back"><i data-lucide="arrow-left" style="width:22px;height:22px;"></i></a>
        <div class="avatar">{{ strtoupper(substr($other->first_name??'?',0,1).substr($other->last_name??'',0,1)) }}</div>
        <div>
            <div class="peer-name">{{ $fullName }}</div>
            <a href="/talent/{{ $other->id }}" class="peer-link">View profile →</a>
        </div>
    </div>

    <div class="msgs" id="msgs">
        @forelse($messages as $m)
            @php $day = date('Y-m-d', strtotime($m->created_at)); @endphp
            @if($day !== $lastDay)
                <div class="day-sep">{{ date('d M Y', strtotime($m->created_at)) }}</div>
                @php $lastDay = $day; @endphp
            @endif
            @if($m->sender_id === $user->id)
            <div class="bubble mine">{{ $m->body }}<span class="meta">{{ date('H:i', strtotime($m->created_at)) }}{{ $m->read_at ? ' · Read' : '' }}</span></div>
            @else
            <div class="bubble theirs">{{ $m->body }}<span class="meta">{{ date('H:i', strtotime($m->created_at)) }}</span></div>
            @endif
        @empty
            <div class="empty-msgs">No messages yet. Say hello <i data-lucide="hand" class="lic"></i></div>
        @endforelse
    </div>

    <form method="POST" action="/messages/{{ $other->id }}" class="composer">
        @csrf
        <textarea name="body" required maxlength="4000" placeholder="Write a message…" oninput="this.style.height='auto';this.style.height=this.scrollHeight+'px';"></textarea>
        <button type="submit" class="send-btn">Send</button>
    </form>
</div>
<script>
var m=document.getElementById('msgs'); if(m){m.scrollTop=m.scrollHeight;}
</script>
@include('partials.footer')
</body>
</html>
