<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Notifications — Galerie virtuelle de l'artisanat du Cameroun</title></head>
<body>
@include('partials.nav')
<style>
.page{max-width:700px;margin:2rem auto;padding:0 1.5rem;}
.page-title{font-size:1.3rem;font-weight:800;margin-bottom:1.2rem;}
.card{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden;}
.notif-row{display:flex;gap:.8rem;padding:.9rem 1.2rem;border-bottom:1px solid var(--border);align-items:flex-start;transition:background .15s;}
.notif-row:last-child{border-bottom:none;}
.notif-row.unread{background:#f0fdf4;}
.notif-dot{width:9px;height:9px;border-radius:50%;background:var(--green);flex-shrink:0;margin-top:5px;}
.notif-dot-read{background:#ddd;}
.notif-body{flex:1;}
.notif-title{font-weight:600;font-size:.87rem;}
.notif-text{font-size:.8rem;color:var(--muted);margin-top:3px;line-height:1.5;}
.notif-date{font-size:.73rem;color:var(--muted);margin-top:4px;}
.notif-action{display:inline-block;font-size:.75rem;color:var(--green);font-weight:600;margin-top:4px;}
.empty-state{text-align:center;padding:3rem;color:var(--muted);}
</style>

<div class="page">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.2rem;">
        <div class="page-title" style="margin-bottom:0;">Notifications</div>
        <div style="display:flex;gap:.5rem;align-items:center;">
        <a href="/settings/notifications" style="font-size:.78rem;padding:.35rem .85rem;border:1px solid var(--border);border-radius:7px;background:var(--white);color:var(--muted);text-decoration:none;"><i data-lucide="settings" class="lic"></i> Settings</a>
        @if($notifications->isNotEmpty())
        <form method="POST" action="/notifications/mark-read">
            @csrf
            <button type="submit" style="font-size:.78rem;padding:.35rem .85rem;border:1px solid var(--border);border-radius:7px;background:var(--white);cursor:pointer;color:var(--muted);">Mark all read</button>
        </form>
        @endif
        </div>
    </div>
    @if(session('success'))<div style="background:#d4edda;border-radius:var(--radius);padding:.6rem 1rem;font-size:.83rem;color:#155724;margin-bottom:.8rem;">{{ session('success') }}</div>@endif
    <div class="card">
        @if($notifications->isEmpty())
            <div class="empty-state">
                <div style="font-size:2.5rem;margin-bottom:.5rem;"><i data-lucide="bell" class="lic"></i></div>
                <p>No notifications yet.</p>
            </div>
        @else
            @foreach($notifications as $n)
                <div class="notif-row {{ $n->read_at ? '' : 'unread' }}">
                    <div class="notif-dot {{ $n->read_at ? 'notif-dot-read' : '' }}"></div>
                    <div class="notif-body">
                        <div class="notif-title">{{ $n->title_en ?: $n->title_fr }}</div>
                        @if($n->body_en || $n->body_fr)
                            <div class="notif-text">{{ $n->body_en ?: $n->body_fr }}</div>
                        @endif
                        <div class="notif-date">{{ date('d M Y H:i',strtotime($n->created_at)) }}</div>
                        @if($n->action_url)
                            <a class="notif-action" href="{{ $n->action_url }}">View →</a>
                        @endif
                    </div>
                </div>
            @endforeach
        @endif
    </div>
    <div style="text-align:center;margin-top:1rem;"><a href="/dashboard" style="color:var(--muted);font-size:.83rem;">← Back to Dashboard</a></div>
</div>
@include('partials.footer')
</body>
</html>
