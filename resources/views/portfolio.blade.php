<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>My Portfolio — Galerie virtuelle de l'artisanat du Cameroun</title></head>
<body>
@include('partials.nav')
<style>
.page{max-width:960px;margin:0 auto;padding:1.5rem;}
h1{font-size:1.3rem;font-weight:800;margin-bottom:1rem;}
.stat-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:.8rem;margin-bottom:1.5rem;}
.stat-card{background:var(--white);border-radius:var(--radius);padding:1.2rem;box-shadow:var(--shadow);text-align:center;}
.stat-num{font-size:1.6rem;font-weight:900;color:var(--green);}
.stat-label{font-size:.78rem;color:var(--muted);margin-top:.2rem;}
.table-wrap{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden;}
table{width:100%;border-collapse:collapse;}
th{padding:.6rem .9rem;text-align:left;font-size:.75rem;font-weight:700;text-transform:uppercase;color:var(--muted);border-bottom:2px solid var(--border);background:var(--light-bg);}
td{padding:.75rem .9rem;font-size:.85rem;border-bottom:1px solid var(--border);}
tr:last-child td{border-bottom:none;}
.status-badge{font-size:.7rem;padding:2px 8px;border-radius:99px;font-weight:700;text-transform:uppercase;}
.s-confirmed,.s-completed,.s-allocated{background:#d4edda;color:#007a33;}
.empty{text-align:center;padding:4rem 2rem;color:var(--muted);}
</style>

<div class="page">
    <h1>My Investment Portfolio</h1>
    @if(session('auth_user'))
    <div class="stat-row">
        <div class="stat-card"><div class="stat-num">{{ number_format($totalInvested) }}</div><div class="stat-label">Total Invested (XAF)</div></div>
        <div class="stat-card"><div class="stat-num">{{ number_format($totalShares) }}</div><div class="stat-label">Total Units/Shares</div></div>
        <div class="stat-card"><div class="stat-num">{{ $pledges->count() }}</div><div class="stat-label">Confirmed Investments</div></div>
    </div>
    @endif

    @if($pledges->isEmpty())
    <div class="empty">
        <div style="font-size:3rem;margin-bottom:.7rem"><i data-lucide="trending-up" class="lic"></i></div>
        <h3>No confirmed investments yet</h3>
        <p style="margin:.4rem 0 1rem;">Browse open share offerings and make your first investment.</p>
        <a href="/offerings" style="display:inline-block;padding:.55rem 1.3rem;background:var(--green);color:#fff;border-radius:7px;font-weight:700;font-size:.85rem;text-decoration:none;">Browse Offerings</a>
    </div>
    @else
    <div class="table-wrap">
        <table>
            <thead><tr><th>Company / Offering</th><th>Type</th><th>Units</th><th>Amount (XAF)</th><th>Date</th><th>Status</th></tr></thead>
            <tbody>
                @foreach($pledges as $p)
                <tr>
                    <td>
                        <div style="font-weight:600;"><a href="/companies/{{ $p->company_slug }}" style="color:var(--text);">{{ $p->company_name }}</a></div>
                        <div style="font-size:.75rem;color:var(--muted);">{{ $p->offering_title }}</div>
                    </td>
                    <td>{{ ucfirst(str_replace('_',' ',$p->instrument_type??'')) }}</td>
                    <td>{{ number_format($p->shares_count??0) }}</td>
                    <td>{{ number_format($p->amount) }}</td>
                    <td>{{ $p->created_at ? date('d M Y',strtotime($p->created_at)) : '' }}</td>
                    <td><span class="status-badge s-{{ $p->status }}">{{ ucfirst($p->status) }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@include('partials.footer')
</body>
</html>
