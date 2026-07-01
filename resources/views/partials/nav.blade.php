<script src="https://unpkg.com/lucide@latest"></script>
<style>
:root{
    --green:#007a33;--green-d:#00592a;--red:#ce1126;--yellow:#fcd116;
    --dark:#0f1623;--mid:#1e2d3d;
    --light-bg:#f4f6f9;--border:#dde2ea;
    --text:#2c3e50;--muted:#6b7a8d;--white:#ffffff;
    --radius:10px;--shadow:0 2px 12px rgba(0,0,0,.08);--shadow-hover:0 6px 24px rgba(0,0,0,.14);
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html{-webkit-text-size-adjust:100%;}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:var(--light-bg);color:var(--text);overflow-x:hidden;-webkit-font-smoothing:antialiased;padding-bottom:62px;}
a{text-decoration:none;color:inherit;}
img,svg,video{max-width:100%;}
input,select,textarea,button{font-family:inherit;}
h1,h2,h3,h4,p,a,span,div{overflow-wrap:break-word;}
@media(max-width:600px){table{display:block;overflow-x:auto;-webkit-overflow-scrolling:touch;}}
/* Lucide icon defaults */
[data-lucide]{width:20px;height:20px;stroke-width:2;flex-shrink:0;vertical-align:middle;}
.lic,svg.lic{width:1em;height:1em;display:inline-block;vertical-align:-.14em;stroke-width:2;}

/* ── Top bar (mobile-first) ── */
nav.topbar{position:sticky;top:0;z-index:120;background:var(--dark);height:56px;display:flex;align-items:center;gap:.4rem;padding:0 .7rem;box-shadow:0 2px 8px rgba(0,0,0,.3);}
.hamburger{display:inline-flex;align-items:center;justify-content:center;width:42px;height:42px;border:none;background:none;color:#cbd5e1;border-radius:9px;cursor:pointer;}
.hamburger:active{background:rgba(255,255,255,.1);}
.nav-logo{display:flex;align-items:center;gap:.5rem;color:#fff;font-weight:800;font-size:1rem;flex-shrink:0;}
.nav-flag{display:flex;height:20px;border-radius:3px;overflow:hidden;}
.nav-flag span{display:block;width:8px;height:20px;}
.f-g{background:var(--green);}.f-r{background:var(--red);}.f-y{background:var(--yellow);}
.desktop-links{display:none;}
.nav-search{display:none;}
.topbar-right{margin-left:auto;display:flex;gap:.15rem;align-items:center;}
.icon-btn{position:relative;width:42px;height:42px;display:inline-flex;align-items:center;justify-content:center;color:#cbd5e1;border-radius:9px;background:none;border:none;cursor:pointer;}
.icon-btn:active{background:rgba(255,255,255,.1);}
.notif-count{position:absolute;top:5px;right:5px;background:var(--red);color:#fff;font-size:.58rem;font-weight:800;min-width:15px;height:15px;border-radius:99px;display:flex;align-items:center;justify-content:center;padding:0 3px;border:1.5px solid var(--dark);}
.nav-avatar{width:32px;height:32px;border-radius:50%;background:var(--green);display:flex;align-items:center;justify-content:center;font-size:.74rem;font-weight:700;color:#fff;}
.nav-cta{display:none;}
.lang-toggle{display:none;}

/* ── Drawer ── */
.drawer-overlay{position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:200;opacity:0;visibility:hidden;transition:opacity .2s;}
.drawer-overlay.open{opacity:1;visibility:visible;}
.drawer{position:fixed;top:0;left:0;bottom:0;width:86%;max-width:330px;background:#fff;z-index:201;transform:translateX(-100%);transition:transform .25s ease;overflow-y:auto;display:flex;flex-direction:column;}
.drawer.open{transform:none;}
.drawer-head{background:var(--dark);color:#fff;padding:1rem 1.1rem;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;}
.drawer-head .nav-logo{font-size:1.05rem;}
.drawer-close{background:none;border:none;color:#cbd5e1;cursor:pointer;width:38px;height:38px;display:flex;align-items:center;justify-content:center;border-radius:8px;}
.drawer-user{display:flex;align-items:center;gap:.7rem;padding:.9rem 1.1rem;border-bottom:1px solid var(--border);}
.drawer-user .nav-avatar{width:40px;height:40px;font-size:.9rem;}
.drawer-sect{font-size:.68rem;text-transform:uppercase;letter-spacing:.6px;color:var(--muted);padding:.9rem 1.1rem .25rem;font-weight:800;}
.drawer-link{display:flex;align-items:center;gap:.8rem;padding:.7rem 1.1rem;font-size:.92rem;color:var(--text);min-height:46px;font-weight:500;}
.drawer-link [data-lucide]{width:19px;height:19px;color:var(--muted);}
.drawer-link:active{background:var(--light-bg);}
.drawer-link.active{background:#eaf5ee;color:var(--green);font-weight:700;box-shadow:inset 3px 0 0 var(--green);}
.drawer-link.active [data-lucide]{color:var(--green);}
.drawer-foot{margin-top:auto;padding:1rem 1.1rem;border-top:1px solid var(--border);}
.drawer-signout{width:100%;padding:.7rem;background:var(--red);color:#fff;border:none;border-radius:9px;font-size:.88rem;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:.4rem;}
.drawer-auth{display:flex;gap:.6rem;padding:1rem 1.1rem;}
.drawer-auth a{flex:1;text-align:center;padding:.7rem;border-radius:9px;font-weight:700;font-size:.9rem;}
.da-login{background:var(--light-bg);color:var(--text);}
.da-register{background:var(--green);color:#fff;}

/* ── Bottom tab bar (mobile) ── */
.bottom-nav{position:fixed;bottom:0;left:0;right:0;height:62px;background:#fff;border-top:1px solid var(--border);display:flex;z-index:110;box-shadow:0 -2px 12px rgba(0,0,0,.06);padding-bottom:env(safe-area-inset-bottom,0);}
.bottom-nav a,.bottom-nav button{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:3px;color:var(--muted);font-size:.6rem;font-weight:600;background:none;border:none;cursor:pointer;position:relative;}
.bottom-nav [data-lucide]{width:22px;height:22px;}
.bottom-nav .active{color:var(--green);}
.bottom-nav .bn-badge{position:absolute;top:7px;right:50%;margin-right:-16px;background:var(--red);color:#fff;font-size:.55rem;font-weight:800;min-width:14px;height:14px;border-radius:99px;display:flex;align-items:center;justify-content:center;padding:0 3px;}

/* ── Mobile search overlay ── */
.search-overlay{position:fixed;inset:0;background:#fff;z-index:300;display:none;flex-direction:column;}
.search-overlay.open{display:flex;}
.so-bar{display:flex;align-items:center;gap:.5rem;padding:.7rem .8rem;border-bottom:1px solid var(--border);}
.so-bar input{flex:1;border:none;outline:none;font-size:1rem;padding:.5rem;background:none;color:var(--text);}
.so-bar button{background:none;border:none;color:var(--muted);cursor:pointer;width:40px;height:40px;display:flex;align-items:center;justify-content:center;}
.so-results{flex:1;overflow-y:auto;}

/* search dropdown (shared) */
.nav-search-dropdown{display:none;}
.ns-item{display:flex;align-items:center;gap:.6rem;padding:.7rem .9rem;color:var(--text);font-size:.9rem;border-bottom:1px solid var(--light-bg);}
.ns-item:active,.ns-item.active{background:var(--light-bg);}
.ns-item [data-lucide]{width:18px;height:18px;color:var(--muted);}
.ns-label{flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-weight:600;}
.ns-type{font-size:.66rem;color:var(--muted);background:var(--light-bg);border:1px solid var(--border);padding:1px 7px;border-radius:99px;}
.ns-foot{padding:.7rem .9rem;font-size:.82rem;color:var(--green);font-weight:700;cursor:pointer;}
.ns-empty{padding:.9rem;font-size:.85rem;color:var(--muted);}

.nav-badge{background:var(--red);color:#fff;font-size:.6rem;font-weight:700;padding:2px 6px;border-radius:99px;}

/* ── Toasts ── */
.toast-wrap{position:fixed;top:66px;left:50%;transform:translateX(-50%);z-index:500;display:flex;flex-direction:column;gap:.5rem;pointer-events:none;width:calc(100% - 1.5rem);max-width:420px;}
.toast{padding:.75rem 1rem;border-radius:10px;font-size:.85rem;font-weight:500;pointer-events:all;display:flex;align-items:center;gap:.6rem;box-shadow:0 6px 20px rgba(0,0,0,.18);animation:slideDown .25s ease;}
.toast-success{background:#f0fdf4;color:#166534;border:1px solid #86efac;}
.toast-error{background:#fef2f2;color:#991b1b;border:1px solid #fca5a5;}
.toast-close{margin-left:auto;background:none;border:none;cursor:pointer;opacity:.5;display:flex;align-items:center;}
@keyframes slideDown{from{transform:translateY(-100%);opacity:0;}to{transform:none;opacity:1;}}

/* ── Footer ── */
footer{margin-top:3rem;padding:1.5rem;background:var(--dark);color:#8899aa;text-align:center;font-size:.82rem;}
footer a{color:var(--yellow);}

/* ── Desktop ── */
@media(min-width:1024px){
    body{padding-bottom:0;}
    .hamburger{display:none;}
    .bottom-nav{display:none;}
    nav.topbar{height:60px;padding:0 1.3rem;gap:.7rem;}
    .nav-logo{font-size:1.05rem;}
    .desktop-links{display:flex;gap:.1rem;margin-left:.4rem;flex:1;overflow-x:auto;scrollbar-width:none;}
    .desktop-links::-webkit-scrollbar{display:none;}
    .nav-link{color:#8899aa;padding:.3rem .6rem;border-radius:6px;font-size:.83rem;font-weight:500;white-space:nowrap;}
    .nav-link:hover{color:#fff;background:rgba(255,255,255,.08);}
    .nav-link.active{color:#fff;background:rgba(255,255,255,.12);}
    .nav-search{display:block;position:relative;width:210px;flex-shrink:0;}
    .nav-search input{width:100%;padding:.42rem .7rem .42rem 2rem;border:1px solid rgba(255,255,255,.15);border-radius:8px;background:rgba(255,255,255,.1);color:#fff;font-size:.82rem;outline:none;}
    .nav-search input::placeholder{color:rgba(255,255,255,.4);}
    .nav-search .so-icon{position:absolute;left:.55rem;top:50%;transform:translateY(-50%);color:rgba(255,255,255,.45);pointer-events:none;}
    .nav-search .nav-search-dropdown{display:none;position:absolute;top:calc(100% + 6px);left:0;right:0;background:#fff;border-radius:10px;box-shadow:0 10px 34px rgba(0,0,0,.2);z-index:300;max-height:72vh;overflow-y:auto;padding:.3rem 0;}
    .nav-search .nav-search-dropdown.open{display:block;}
    .lang-toggle{display:flex;border:1px solid rgba(255,255,255,.2);border-radius:7px;overflow:hidden;}
    .lang-btn{padding:.25rem .5rem;font-size:.7rem;font-weight:700;color:#8899aa;background:none;border:none;cursor:pointer;}
    .lang-btn.active{background:rgba(255,255,255,.15);color:#fff;}
    .search-mobile-btn{display:none;}
    .toast-wrap{left:auto;right:1.5rem;transform:none;}
}
@media(min-width:1024px) and (max-width:1300px){ .nav-search{width:150px;} }
</style>

@php
    $authUser = session('auth_user');
    $currentLang = session('lang','en');
    $announcement = \DB::table('announcements')->where('is_published',1)->where('starts_at','<=',now())->where('ends_at','>=',now())->first();
    $navUnread = 0; $navMsgs = 0;
    if ($authUser) {
        $navUnread = \DB::table('notifications')->where('user_id',$authUser['id'])->whereNull('read_at')->count();
        $navMsgs = \DB::table('messages')->join('conversations','messages.conversation_id','=','conversations.id')
            ->where(function($q) use ($authUser){ $q->where('conversations.user_one_id',$authUser['id'])->orWhere('conversations.user_two_id',$authUser['id']); })
            ->where('messages.sender_id','!=',$authUser['id'])->whereNull('messages.read_at')->count();
    }
    // [href, label, lucide, active-pattern]
    $primaryNav = [
        ['/', 'Companies', 'building-2', null],
        ['/offerings', 'Offerings', 'trending-up', null],
        ['/jobs', 'Jobs', 'briefcase', 'jobs'],
        ['/talent', 'Talent', 'users', 'talent'],
        ['/salaries', 'Salaries', 'banknote', 'salaries'],
    ];
    $businessNav = [
        ['/tenders','Tenders','file-text','tenders'],
        ['/invest-hub','Investment','hand-coins','invest-hub'],
        ['/federations','Federations','landmark','federations'],
        ['/esg','ESG','leaf','esg'],
        ['/export-hub','Export Hub','globe','export-hub'],
        ['/supplier-reviews','Supplier Reviews','star','supplier-reviews'],
        ['/associations','Associations','users-round','associations'],
        ['/collabcam','CollabCam','sparkles','collabcam'],
    ];
    $marketNav = [
        ['/events','Events','calendar','events'],
        ['/communities','Communities','messages-square','communities'],
        ['/knowledge','Knowledge','book-open','knowledge'],
        ['/innovation','Innovation','lightbulb','innovation'],
        ['/logistics','Logistics','truck','logistics'],
        ['/assets','Assets','package','assets'],
        ['/cards','Digital Cards','contact','cards'],
        ['/compliance','Compliance','shield-check','compliance'],
        ['/prm','Partners','handshake','prm'],
        ['/health-score','Health Score','activity','health-score'],
    ];
    $infoNav = [
        ['/blog','Blog','newspaper','blog'],
        ['/about','About','info','about'],
        ['/help','Help','life-buoy','help'],
    ];
    $isActive = function($pattern){ return $pattern && request()->is($pattern, $pattern.'/*'); };
@endphp

@if($announcement)
<div style="background:var(--yellow);color:var(--dark);text-align:center;padding:.5rem 2.5rem .5rem 1rem;font-size:.78rem;font-weight:700;position:relative;">
    {{ $currentLang==='fr' ? ($announcement->body_fr??$announcement->body_en) : $announcement->body_en }}
    <button onclick="this.parentElement.remove()" style="position:absolute;right:.7rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;display:flex;"><i data-lucide="x" style="width:16px;height:16px;"></i></button>
</div>
@endif

<nav class="topbar">
    <button class="hamburger" onclick="CC.openDrawer()" aria-label="Menu"><i data-lucide="menu" style="width:24px;height:24px;"></i></button>
    <a class="nav-logo" href="/">
        <span class="nav-flag"><span class="f-g"></span><span class="f-r"></span><span class="f-y"></span></span>
        Galerie virtuelle de l'artisanat du Cameroun
    </a>

    <div class="desktop-links">
        @foreach(array_merge($primaryNav,$businessNav,$marketNav,$infoNav) as $it)
            <a class="nav-link {{ $isActive($it[3]) ? 'active' : '' }}" href="{{ $it[0] }}">{{ $it[1] }}</a>
        @endforeach
        @if($authUser)<a class="nav-link {{ request()->is('dashboard')?'active':'' }}" href="/dashboard">Dashboard</a>@endif
    </div>

    <form class="nav-search" method="GET" action="/search" role="search" autocomplete="off">
        <i data-lucide="search" class="so-icon" style="width:16px;height:16px;"></i>
        <input type="text" name="q" id="navSearchInput" placeholder="Search everything…" value="{{ request('q') }}">
        <div id="navSearchDropdown" class="nav-search-dropdown"></div>
    </form>

    <div class="topbar-right">
        <div class="lang-toggle">
            <a href="/lang/en" class="lang-btn {{ $currentLang==='en'?'active':'' }}">EN</a>
            <a href="/lang/fr" class="lang-btn {{ $currentLang==='fr'?'active':'' }}">FR</a>
        </div>
        <button class="icon-btn search-mobile-btn" onclick="CC.openSearch()" aria-label="Search"><i data-lucide="search" style="width:22px;height:22px;"></i></button>
        @if($authUser)
            <a href="/messages" class="icon-btn" aria-label="Messages"><i data-lucide="message-circle" style="width:22px;height:22px;"></i><span class="notif-count" id="navMsgCount" style="{{ $navMsgs>0?'':'display:none;' }}">{{ $navMsgs>9?'9+':$navMsgs }}</span></a>
            <a href="/notifications" class="icon-btn" aria-label="Notifications"><i data-lucide="bell" style="width:22px;height:22px;"></i><span class="notif-count" id="navNotifCount" style="{{ $navUnread>0?'':'display:none;' }}">{{ $navUnread>9?'9+':$navUnread }}</span></a>
            <button class="icon-btn" onclick="CC.openDrawer()" aria-label="Account"><span class="nav-avatar">{{ strtoupper(substr($authUser['first_name']??'U',0,1).substr($authUser['last_name']??'',0,1)) }}</span></button>
        @else
            <a class="nav-cta nav-badge" href="/login" style="background:none;color:#cbd5e1;padding:.4rem .6rem;font-size:.82rem;">Log in</a>
        @endif
    </div>
</nav>

{{-- Drawer --}}
<div class="drawer-overlay" id="drawerOverlay" onclick="CC.closeDrawer()"></div>
<aside class="drawer" id="drawer">
    <div class="drawer-head">
        <a class="nav-logo" href="/"><span class="nav-flag"><span class="f-g"></span><span class="f-r"></span><span class="f-y"></span></span>Galerie virtuelle de l'artisanat du Cameroun</a>
        <button class="drawer-close" onclick="CC.closeDrawer()" aria-label="Close"><i data-lucide="x" style="width:22px;height:22px;"></i></button>
    </div>
    @if($authUser)
    <a href="/dashboard" class="drawer-user">
        <span class="nav-avatar">{{ strtoupper(substr($authUser['first_name']??'U',0,1).substr($authUser['last_name']??'',0,1)) }}</span>
        <div><div style="font-weight:700;font-size:.92rem;color:var(--text);">{{ $authUser['first_name'] }} {{ $authUser['last_name']??'' }}</div><div style="font-size:.76rem;color:var(--green);font-weight:600;">View dashboard →</div></div>
    </a>
    @else
    <div class="drawer-auth"><a class="da-login" href="/login">Log in</a><a class="da-register" href="/register">Register</a></div>
    @endif

    <div class="drawer-sect">Explore</div>
    @foreach($primaryNav as $it)<a class="drawer-link {{ $it[3]===null ? (request()->is('/')&&$it[0]==='/'?'active':'') : ($isActive($it[3])?'active':'') }}" href="{{ $it[0] }}"><i data-lucide="{{ $it[2] }}"></i>{{ $it[1] }}</a>@endforeach
    <div class="drawer-sect">Business &amp; Collaboration</div>
    @foreach($businessNav as $it)<a class="drawer-link {{ $isActive($it[3])?'active':'' }}" href="{{ $it[0] }}"><i data-lucide="{{ $it[2] }}"></i>{{ $it[1] }}</a>@endforeach
    <div class="drawer-sect">Marketplace &amp; Community</div>
    @foreach($marketNav as $it)<a class="drawer-link {{ $isActive($it[3])?'active':'' }}" href="{{ $it[0] }}"><i data-lucide="{{ $it[2] }}"></i>{{ $it[1] }}</a>@endforeach

    @if($authUser)
    <div class="drawer-sect">My Account</div>
    <a class="drawer-link" href="/profile"><i data-lucide="user"></i>My Profile</a>
    <a class="drawer-link" href="/my-profile"><i data-lucide="briefcase"></i>Career Profile</a>
    <a class="drawer-link" href="/cv"><i data-lucide="file-text"></i>My CVs</a>
    <a class="drawer-link" href="/cover-letters"><i data-lucide="pen-line"></i>Cover Letters</a>
    <a class="drawer-link" href="/saved-jobs"><i data-lucide="bookmark"></i>Saved Jobs &amp; Alerts</a>
    <a class="drawer-link" href="/watchlist"><i data-lucide="star"></i>Watchlist</a>
    <a class="drawer-link" href="/portfolio"><i data-lucide="pie-chart"></i>Portfolio</a>
    <a class="drawer-link" href="/wallet"><i data-lucide="wallet"></i>Wallet</a>
    <a class="drawer-link" href="/investor-profile"><i data-lucide="badge-check"></i>KYC / Investor</a>
    <a class="drawer-link" href="/developer"><i data-lucide="code"></i>Developer API</a>
    <a class="drawer-link" href="/settings/notifications"><i data-lucide="bell"></i>Notification Settings</a>
    @if(!empty($authUser['is_admin']))<a class="drawer-link" href="/admin" style="color:var(--green);font-weight:700;"><i data-lucide="shield"></i>Admin Panel</a>@endif
    @endif

    <div class="drawer-sect">Info</div>
    @foreach($infoNav as $it)<a class="drawer-link {{ $isActive($it[3])?'active':'' }}" href="{{ $it[0] }}"><i data-lucide="{{ $it[2] }}"></i>{{ $it[1] }}</a>@endforeach

    @if($authUser)
    <div class="drawer-foot">
        <form method="POST" action="/logout">@csrf<button class="drawer-signout"><i data-lucide="log-out" style="width:18px;height:18px;"></i>Sign Out</button></form>
    </div>
    @endif
</aside>

{{-- Mobile search overlay --}}
<div class="search-overlay" id="searchOverlay">
    <div class="so-bar">
        <i data-lucide="search" style="width:20px;height:20px;color:var(--muted);"></i>
        <input type="text" id="soInput" placeholder="Search everything…" autocomplete="off">
        <button onclick="CC.closeSearch()" aria-label="Close"><i data-lucide="x" style="width:22px;height:22px;"></i></button>
    </div>
    <div class="so-results" id="soResults"></div>
</div>

{{-- Bottom tab bar (mobile) --}}
<nav class="bottom-nav">
    <a href="/" class="{{ request()->is('/')?'active':'' }}"><i data-lucide="home"></i>Home</a>
    <a href="/jobs" class="{{ request()->is('jobs','jobs/*')?'active':'' }}"><i data-lucide="briefcase"></i>Jobs</a>
    <button onclick="CC.openSearch()"><i data-lucide="search"></i>Search</button>
    @if($authUser)
    <a href="/messages" class="{{ request()->is('messages','messages/*')?'active':'' }}" style="position:relative;"><i data-lucide="message-circle"></i>Inbox @if($navMsgs>0)<span class="bn-badge">{{ $navMsgs>9?'9+':$navMsgs }}</span>@endif</a>
    @else
    <a href="/talent" class="{{ request()->is('talent','talent/*')?'active':'' }}"><i data-lucide="users"></i>Talent</a>
    @endif
    <button onclick="CC.openDrawer()"><i data-lucide="menu"></i>Menu</button>
</nav>

{{-- Flash toasts --}}
<div class="toast-wrap" id="toastWrap">
    @if(session('success'))<div class="toast toast-success"><i data-lucide="check-circle-2" style="width:18px;height:18px;"></i>{{ session('success') }}<button class="toast-close" onclick="this.parentElement.remove()"><i data-lucide="x" style="width:16px;height:16px;"></i></button></div>@endif
    @if(session('error'))<div class="toast toast-error"><i data-lucide="alert-circle" style="width:18px;height:18px;"></i>{{ session('error') }}<button class="toast-close" onclick="this.parentElement.remove()"><i data-lucide="x" style="width:16px;height:16px;"></i></button></div>@endif
    @if($errors->any() && !request()->is('login') && !request()->is('register'))<div class="toast toast-error"><i data-lucide="alert-circle" style="width:18px;height:18px;"></i>{{ $errors->first() }}<button class="toast-close" onclick="this.parentElement.remove()"><i data-lucide="x" style="width:16px;height:16px;"></i></button></div>@endif
</div>

<script>
window.CC = {
    openDrawer(){document.getElementById('drawer').classList.add('open');document.getElementById('drawerOverlay').classList.add('open');document.body.style.overflow='hidden';},
    closeDrawer(){document.getElementById('drawer').classList.remove('open');document.getElementById('drawerOverlay').classList.remove('open');document.body.style.overflow='';},
    openSearch(){var o=document.getElementById('searchOverlay');o.classList.add('open');document.body.style.overflow='hidden';setTimeout(function(){document.getElementById('soInput').focus();},50);},
    closeSearch(){document.getElementById('searchOverlay').classList.remove('open');document.body.style.overflow='';},
};
function ccIcons(){ if(window.lucide) lucide.createIcons(); }
ccIcons();
window.addEventListener('load', ccIcons);
setTimeout(()=>{document.querySelectorAll('.toast').forEach(t=>t.remove());},5000);

// autocomplete shared between desktop input and mobile overlay
(function(){
    var lucideMap=null;
    function suggest(q, container, formSubmit){
        if(q.length<2){container.innerHTML='';container.classList.remove('open');return;}
        fetch('/search/suggest?q='+encodeURIComponent(q),{headers:{'X-Requested-With':'XMLHttpRequest'}})
            .then(r=>r.json()).then(function(d){
                var res=d.results||[];
                if(!res.length){container.innerHTML='<div class="ns-empty">No quick matches. Press Enter for full search.</div>';container.classList.add('open');return;}
                container.innerHTML=res.map(function(r){return '<a class="ns-item" href="'+r.url+'"><i data-lucide="'+(r.icon||'circle')+'"></i><span class="ns-label">'+r.label.replace(/</g,'&lt;')+'</span><span class="ns-type">'+r.type+'</span></a>';}).join('')
                    +'<div class="ns-foot" onclick="'+formSubmit+'">See all results for &quot;'+q.replace(/</g,'&lt;').replace(/"/g,'')+'&quot; →</div>';
                container.classList.add('open'); ccIcons();
            }).catch(function(){container.classList.remove('open');});
    }
    var di=document.getElementById('navSearchInput'), dd=document.getElementById('navSearchDropdown');
    if(di){ var t; di.addEventListener('input',function(){clearTimeout(t);var q=di.value.trim();t=setTimeout(function(){suggest(q,dd,"document.getElementById('navSearchInput').form.submit()");},180);});
        document.addEventListener('click',function(e){if(!di.form.contains(e.target))dd.classList.remove('open');}); }
    var si=document.getElementById('soInput'), sr=document.getElementById('soResults');
    if(si){ var t2; si.addEventListener('input',function(){clearTimeout(t2);var q=si.value.trim();t2=setTimeout(function(){suggest(q,sr,"window.location='/search?q='+encodeURIComponent(document.getElementById('soInput').value)");},180);});
        si.addEventListener('keydown',function(e){if(e.key==='Enter'){window.location='/search?q='+encodeURIComponent(si.value);}}); }
})();

// notification + message badge polling
(function(){
    function setBadge(id,n){var b=document.getElementById(id);if(!b)return;if(n>0){b.textContent=n>9?'9+':n;b.style.display='';}else{b.style.display='none';}}
    setInterval(function(){
        fetch('/notifications/count',{headers:{'X-Requested-With':'XMLHttpRequest'}}).then(r=>r.json()).then(d=>setBadge('navNotifCount',d.unread||0)).catch(()=>{});
    },30000);
})();
</script>
