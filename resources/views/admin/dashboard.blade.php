<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Admin Dashboard — Galerie virtuelle de l'artisanat du Cameroun</title></head><body>
@php $pageTitle = 'Dashboard'; @endphp
@include('admin.nav')
<style>
.stats-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:1rem;margin-bottom:2rem;}
.stat-card{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);padding:1.1rem;text-align:center;}
.stat-val{font-size:1.8rem;font-weight:900;color:var(--text);}
.stat-lbl{font-size:.72rem;color:var(--muted);margin-top:3px;}
.stat-card.warn .stat-val{color:var(--red);}
.grid2{display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;}
.card{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden;margin-bottom:1rem;}
.card-hd{padding:.75rem 1rem;font-weight:700;font-size:.85rem;border-bottom:1px solid var(--border);background:var(--light-bg);display:flex;justify-content:space-between;align-items:center;}
.card-bd{padding:0;}
table{width:100%;border-collapse:collapse;}
th{padding:.5rem .85rem;font-size:.72rem;font-weight:700;text-transform:uppercase;color:var(--muted);border-bottom:2px solid var(--border);background:var(--light-bg);text-align:left;}
td{padding:.65rem .85rem;font-size:.82rem;border-bottom:1px solid var(--border);}
tr:last-child td{border-bottom:none;}
.badge{display:inline-block;font-size:.68rem;font-weight:700;padding:2px 7px;border-radius:99px;}
.b-pending{background:#fff3cd;color:#856404;}
.b-confirmed{background:#d4edda;color:#155724;}
.b-cancelled{background:#f8f9fa;color:#6c757d;}
.b-completed{background:#cce5ff;color:#004085;}
.quick-actions{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:.75rem;margin-bottom:2rem;}
.qa-btn{display:block;background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);padding:1rem;font-size:.85rem;font-weight:700;color:var(--text);text-align:center;border:1px solid var(--border);transition:box-shadow .15s;}
.qa-btn:hover{box-shadow:0 4px 16px rgba(0,0,0,.12);}
.qa-icon{font-size:1.4rem;margin-bottom:.3rem;}
@media(max-width:700px){.grid2{grid-template-columns:1fr;}}
</style>

@if($stats['kyc_pending'] > 0 || $stats['claims_pending'] > 0 || $stats['tickets'] > 0)
<div style="background:#fff3cd;border:1px solid #ffc107;border-radius:var(--radius);padding:.85rem 1.1rem;margin-bottom:1.5rem;font-size:.83rem;color:#856404;display:flex;gap:1.5rem;flex-wrap:wrap;">
    <strong>Action required:</strong>
    @if($stats['kyc_pending'] > 0)<a href="/admin/kyc" style="color:#856404;font-weight:700;">{{ $stats['kyc_pending'] }} KYC pending</a>@endif
    @if($stats['claims_pending'] > 0)<a href="/admin/claims" style="color:#856404;font-weight:700;">{{ $stats['claims_pending'] }} claims pending</a>@endif
    @if($stats['tickets'] > 0)<a href="/admin/tickets" style="color:#856404;font-weight:700;">{{ $stats['tickets'] }} open tickets</a>@endif
</div>
@endif

<div class="stats-grid">
    <div class="stat-card"><div class="stat-val">{{ number_format($stats['users']) }}</div><div class="stat-lbl">Total Users</div></div>
    <div class="stat-card"><div class="stat-val">{{ number_format($stats['companies']) }}</div><div class="stat-lbl">Companies</div></div>
    <div class="stat-card"><div class="stat-val">{{ number_format($stats['offerings']) }}</div><div class="stat-lbl">Offerings</div></div>
    <div class="stat-card"><div class="stat-val">{{ number_format($stats['pledges']) }}</div><div class="stat-lbl">Pledges</div></div>
    <div class="stat-card"><div class="stat-val">{{ number_format($stats['jobs']) }}</div><div class="stat-lbl">Open Jobs</div></div>
    <div class="stat-card"><div class="stat-val">{{ number_format($stats['applications']) }}</div><div class="stat-lbl">Applications</div></div>
    <div class="stat-card {{ $stats['kyc_pending']>0?'warn':'' }}"><div class="stat-val">{{ $stats['kyc_pending'] }}</div><div class="stat-lbl">KYC Pending</div></div>
    <div class="stat-card {{ $stats['claims_pending']>0?'warn':'' }}"><div class="stat-val">{{ $stats['claims_pending'] }}</div><div class="stat-lbl">Claims Pending</div></div>
    <div class="stat-card {{ $stats['tickets']>0?'warn':'' }}"><div class="stat-val">{{ $stats['tickets'] }}</div><div class="stat-lbl">Open Tickets</div></div>
</div>

<div class="grid2">
    <div class="card">
        <div class="card-hd">Recent Users <a href="/admin/users" style="font-size:.78rem;color:var(--green);font-weight:400;">View all</a></div>
        <div class="card-bd">
            <table>
                <thead><tr><th>Name</th><th>Email</th><th>Joined</th></tr></thead>
                <tbody>
                @forelse($recentUsers as $u)
                <tr>
                    <td>{{ $u->first_name }} {{ $u->last_name }}{{ $u->is_admin ? ' <i data-lucide="key" class="lic"></i>' : '' }}</td>
                    <td style="color:var(--muted);">{{ $u->email }}</td>
                    <td style="color:var(--muted);">{{ $u->created_at ? date('d M',strtotime($u->created_at)) : '' }}</td>
                </tr>
                @empty
                <tr><td colspan="3" style="text-align:center;color:var(--muted);padding:1.5rem;">No users yet</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card">
        <div class="card-hd">Recent Pledges <a href="#" style="font-size:.78rem;color:var(--green);font-weight:400;"></a></div>
        <div class="card-bd">
            <table>
                <thead><tr><th>Investor</th><th>Offering</th><th>Amount</th><th>Status</th></tr></thead>
                <tbody>
                @forelse($recentPledges as $p)
                <tr>
                    <td>{{ $p->first_name }} {{ $p->last_name }}</td>
                    <td style="font-size:.78rem;color:var(--muted);">{{ Str::limit($p->title_en,22) }}</td>
                    <td style="font-weight:700;">{{ number_format($p->amount/1000000,1) }}M</td>
                    <td><span class="badge b-{{ $p->status }}">{{ $p->status }}</span></td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center;color:var(--muted);padding:1.5rem;">No pledges yet</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@include('admin.end')
