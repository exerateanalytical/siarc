@php
$adminUser = session('auth_user');
$currentPath = request()->path();
function adminActive($path) {
    return request()->is($path) || request()->is($path.'/*') ? 'active' : '';
}
@endphp
<style>
:root{--green:#007a33;--red:#ce1126;--yellow:#fcd116;--dark:#0f1623;--mid:#1e2d3d;--light-bg:#f4f6f9;--border:#dde2ea;--text:#2c3e50;--muted:#6b7a8d;--white:#ffffff;--radius:10px;--shadow:0 2px 12px rgba(0,0,0,.08);}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:var(--light-bg);color:var(--text);}
a{text-decoration:none;color:inherit;}
.admin-layout{display:flex;min-height:100vh;}
.admin-sidebar{width:220px;background:var(--dark);color:#8899aa;flex-shrink:0;display:flex;flex-direction:column;}
.sidebar-logo{padding:1.2rem 1.2rem .8rem;border-bottom:1px solid rgba(255,255,255,.06);display:flex;align-items:center;gap:.5rem;color:#fff;font-weight:800;font-size:.95rem;}
.sidebar-logo .flag{display:flex;height:18px;border-radius:2px;overflow:hidden;}
.sidebar-logo .flag span{display:block;width:7px;height:18px;}
.f-g{background:var(--green);}.f-r{background:var(--red);}.f-y{background:var(--yellow);}
.sidebar-label{font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:#4a5a6a;padding:.9rem 1.2rem .3rem;}
.sidebar-link{display:flex;align-items:center;gap:.6rem;padding:.5rem 1.2rem;font-size:.82rem;color:#8899aa;border-left:3px solid transparent;transition:color .15s,background .15s,border-color .15s;}
.sidebar-link:hover{color:#fff;background:rgba(255,255,255,.06);}
.sidebar-link.active{color:#fff;background:rgba(255,255,255,.08);border-left-color:var(--green);}
.sidebar-link .icon{width:16px;text-align:center;flex-shrink:0;}
.sidebar-badge{margin-left:auto;background:var(--red);color:#fff;font-size:.6rem;font-weight:800;padding:1px 5px;border-radius:99px;}
.sidebar-bottom{margin-top:auto;padding:.8rem;border-top:1px solid rgba(255,255,255,.06);}
.admin-main{flex:1;display:flex;flex-direction:column;overflow:hidden;}
.admin-topbar{background:var(--white);border-bottom:1px solid var(--border);padding:.7rem 1.5rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;}
.admin-topbar-title{font-size:.9rem;font-weight:700;color:var(--text);}
.admin-topbar-user{font-size:.8rem;color:var(--muted);display:flex;align-items:center;gap:.5rem;}
.admin-content{flex:1;padding:1.5rem;overflow-y:auto;}
.toast{position:fixed;top:1rem;right:1rem;z-index:999;padding:.75rem 1.1rem;border-radius:9px;font-size:.83rem;font-weight:500;display:flex;align-items:center;gap:.6rem;box-shadow:0 4px 16px rgba(0,0,0,.15);animation:slideIn .2s ease;}
.toast-success{background:#f0fdf4;color:#166534;border:1px solid #86efac;}
.toast-error{background:#fef2f2;color:#991b1b;border:1px solid #fca5a5;}
.toast-close{background:none;border:none;cursor:pointer;opacity:.5;font-size:1rem;}
@keyframes slideIn{from{transform:translateX(100%);opacity:0}to{transform:none;opacity:1}}
</style>

<div class="admin-layout">
<div class="admin-sidebar">
    <div class="sidebar-logo">
        <div class="flag"><span class="f-g"></span><span class="f-r"></span><span class="f-y"></span></div>
        Admin Panel
    </div>

    <div class="sidebar-label">Overview</div>
    <a class="sidebar-link {{ adminActive('admin') && !request()->is('admin/*') ? 'active' : '' }}" href="/admin"><span class="icon"><i data-lucide="bar-chart-3" class="lic"></i></span> Dashboard</a>

    <div class="sidebar-label">Queue</div>
    <a class="sidebar-link {{ adminActive('admin/kyc') }}" href="/admin/kyc">
        <span class="icon"><i data-lucide="contact" class="lic"></i></span> KYC Applications
        @php $kc = DB::table('kyc_applications')->where('status','pending')->count(); @endphp
        @if($kc)<span class="sidebar-badge">{{ $kc }}</span>@endif
    </a>
    <a class="sidebar-link {{ adminActive('admin/claims') }}" href="/admin/claims">
        <span class="icon"><i data-lucide="building-2" class="lic"></i></span> Company Claims
        @php $cc = DB::table('verification_applications')->where('status','pending')->count(); @endphp
        @if($cc)<span class="sidebar-badge">{{ $cc }}</span>@endif
    </a>
    <a class="sidebar-link {{ adminActive('admin/tickets') }}" href="/admin/tickets">
        <span class="icon"><i data-lucide="ticket" class="lic"></i></span> Support Tickets
        @php $tc = DB::table('tickets')->where('status','open')->count(); @endphp
        @if($tc)<span class="sidebar-badge">{{ $tc }}</span>@endif
    </a>

    <div class="sidebar-label">Manage</div>
    <a class="sidebar-link {{ adminActive('admin/users') }}" href="/admin/users"><span class="icon"><i data-lucide="users" class="lic"></i></span> Users</a>
    <a class="sidebar-link {{ adminActive('admin/companies') }}" href="/admin/companies"><span class="icon"><i data-lucide="factory" class="lic"></i></span> Companies</a>
    <a class="sidebar-link {{ adminActive('admin/announcements') }}" href="/admin/announcements"><span class="icon"><i data-lucide="megaphone" class="lic"></i></span> Announcements</a>

    <div class="sidebar-label">Platform</div>
    <a class="sidebar-link" href="/" target="_blank"><span class="icon"><i data-lucide="globe" class="lic"></i></span> View Site</a>
    <a class="sidebar-link" href="/dashboard"><span class="icon">◀</span> Back to App</a>

    <div class="sidebar-bottom">
        <form method="POST" action="/logout">@csrf<button style="width:100%;background:none;border:none;color:#6b7a8d;font-size:.8rem;cursor:pointer;text-align:left;padding:.3rem 0;">Sign Out ({{ $adminUser['first_name'] ?? '' }})</button></form>
    </div>
</div>

<div class="admin-main">
    <div class="admin-topbar">
        <div class="admin-topbar-title">{{ $pageTitle ?? 'Admin' }}</div>
        <div class="admin-topbar-user">
            <span style="width:24px;height:24px;border-radius:50%;background:var(--green);display:flex;align-items:center;justify-content:center;color:#fff;font-size:.65rem;font-weight:800;">{{ strtoupper(substr($adminUser['first_name']??'A',0,1)) }}</span>
            {{ $adminUser['first_name'] ?? 'Admin' }} — <span style="color:var(--red);">Admin</span>
        </div>
    </div>

    @if(session('success'))
    <div class="toast toast-success" id="__toast"><i data-lucide="check-circle-2" class="lic"></i> {{ session('success') }}<button class="toast-close" onclick="this.parentElement.remove()"><i data-lucide="x" class="lic"></i></button></div>
    @endif
    @if(session('error'))
    <div class="toast toast-error" id="__toast"><i data-lucide="x" class="lic"></i> {{ session('error') }}<button class="toast-close" onclick="this.parentElement.remove()"><i data-lucide="x" class="lic"></i></button></div>
    @endif

    <div class="admin-content">
