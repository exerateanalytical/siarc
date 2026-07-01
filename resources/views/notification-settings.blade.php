<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Notification Settings — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:600px;margin:0 auto;padding:1.5rem 1.5rem 3rem;}
.crumb{font-size:.78rem;color:var(--muted);margin-bottom:.8rem;}
h1{font-size:1.3rem;font-weight:900;margin-bottom:.3rem;}
.subtitle{font-size:.85rem;color:var(--muted);margin-bottom:1.3rem;}
.success{background:#d4edda;border-radius:var(--radius);padding:.7rem 1rem;font-size:.84rem;color:#155724;margin-bottom:1rem;}
.card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);border:1px solid var(--border);overflow:hidden;}
.pref-row{display:flex;align-items:center;gap:1rem;padding:1.1rem 1.3rem;border-bottom:1px solid var(--border);}
.pref-row:last-of-type{border-bottom:none;}
.pref-icon{font-size:1.5rem;flex-shrink:0;}
.pref-info{flex:1;}
.pref-name{font-weight:700;font-size:.92rem;}
.pref-desc{font-size:.78rem;color:var(--muted);margin-top:.15rem;line-height:1.4;}
/* toggle */
.switch{position:relative;display:inline-block;width:46px;height:26px;flex-shrink:0;}
.switch input{opacity:0;width:0;height:0;}
.slider{position:absolute;cursor:pointer;inset:0;background:#cbd5e1;border-radius:99px;transition:.2s;}
.slider:before{content:"";position:absolute;height:20px;width:20px;left:3px;bottom:3px;background:#fff;border-radius:50%;transition:.2s;}
input:checked+.slider{background:var(--green);}
input:checked+.slider:before{transform:translateX(20px);}
.always{padding:1rem 1.3rem;background:var(--light-bg);font-size:.78rem;color:var(--muted);}
.save-btn{margin-top:1.2rem;padding:.7rem 1.6rem;background:var(--green);color:#fff;border:none;border-radius:8px;font-weight:700;font-size:.9rem;cursor:pointer;}
</style>

@php
$m = $prefs->messages ?? 1;
$j = $prefs->job_updates ?? 1;
$mk = $prefs->marketplace ?? 1;
@endphp

<div class="page">
    <div class="crumb"><a href="/notifications" style="color:var(--muted);">Notifications</a> › Settings</div>
    <h1><i data-lucide="bell" class="lic"></i> Notification Preferences</h1>
    <p class="subtitle">Choose which in-app notifications you receive. You can change these anytime.</p>

    @if(session('success'))<div class="success">{{ session('success') }}</div>@endif

    <form method="POST" action="/settings/notifications">
        @csrf
        <div class="card">
            <div class="pref-row">
                <div class="pref-icon"><i data-lucide="message-circle" class="lic"></i></div>
                <div class="pref-info">
                    <div class="pref-name">Messages</div>
                    <div class="pref-desc">When someone sends you a direct message.</div>
                </div>
                <label class="switch"><input type="checkbox" name="messages" {{ $m ? 'checked' : '' }}><span class="slider"></span></label>
            </div>
            <div class="pref-row">
                <div class="pref-icon"><i data-lucide="briefcase" class="lic"></i></div>
                <div class="pref-info">
                    <div class="pref-name">Job Updates</div>
                    <div class="pref-desc">Application status changes, new applicants on your jobs, and job alerts.</div>
                </div>
                <label class="switch"><input type="checkbox" name="job_updates" {{ $j ? 'checked' : '' }}><span class="slider"></span></label>
            </div>
            <div class="pref-row">
                <div class="pref-icon"><i data-lucide="handshake" class="lic"></i></div>
                <div class="pref-info">
                    <div class="pref-name">Marketplace Activity</div>
                    <div class="pref-desc">Tender bids, asset inquiries, investment interest, reviews, and federation/community/event responses.</div>
                </div>
                <label class="switch"><input type="checkbox" name="marketplace" {{ $mk ? 'checked' : '' }}><span class="slider"></span></label>
            </div>
            <div class="always"><i data-lucide="lock" class="lic"></i> Account &amp; security notifications (verification, claims) are always delivered.</div>
        </div>
        <button type="submit" class="save-btn">Save Preferences</button>
    </form>
</div>
@include('partials.footer')
</body>
</html>
