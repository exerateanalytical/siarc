<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Companies — Admin</title></head><body>
@php $pageTitle = 'Companies'; @endphp
@include('admin.nav')
<style>
.toolbar{display:flex;align-items:center;gap:.75rem;margin-bottom:1.2rem;flex-wrap:wrap;}
.search-input{padding:.5rem .85rem;border:1.5px solid var(--border);border-radius:8px;font-size:.85rem;outline:none;min-width:240px;}
.search-input:focus{border-color:var(--green);}
.card{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden;}
table{width:100%;border-collapse:collapse;}
th{padding:.55rem .85rem;font-size:.72rem;font-weight:700;text-transform:uppercase;color:var(--muted);border-bottom:2px solid var(--border);background:var(--light-bg);text-align:left;}
td{padding:.65rem .85rem;font-size:.82rem;border-bottom:1px solid var(--border);vertical-align:middle;}
tr:last-child td{border-bottom:none;}
.badge{display:inline-block;font-size:.67rem;font-weight:700;padding:1px 6px;border-radius:99px;}
.b-unverified{background:#f8f9fa;color:#6c757d;}
.b-basic{background:#cce5ff;color:#004085;}
.b-verified{background:#d4edda;color:#155724;}
.b-certified{background:#d4edda;color:#007a33;border:1px solid #007a33;}
.btn-sm{padding:.3rem .65rem;border-radius:6px;font-size:.74rem;font-weight:700;border:none;cursor:pointer;}
.pagination{padding:1rem;display:flex;gap:.4rem;justify-content:center;}
.page-link{padding:.4rem .7rem;border:1px solid var(--border);border-radius:6px;font-size:.8rem;color:var(--text);}
.page-link.current{background:var(--green);color:#fff;border-color:var(--green);}
</style>

<div class="toolbar">
    <form method="GET" action="/admin/companies" style="display:flex;gap:.5rem;">
        <input class="search-input" type="text" name="q" value="{{ $q }}" placeholder="Search by name or email…">
        <button type="submit" style="padding:.5rem 1rem;background:var(--green);color:#fff;border:none;border-radius:8px;font-size:.85rem;font-weight:700;cursor:pointer;">Search</button>
        @if($q)<a href="/admin/companies" style="padding:.5rem .8rem;border:1px solid var(--border);border-radius:8px;font-size:.82rem;color:var(--muted);">Clear</a>@endif
    </form>
    <span style="margin-left:auto;font-size:.82rem;color:var(--muted);">{{ $companies->total() }} companies</span>
</div>

<div class="card">
    <table>
        <thead><tr><th>Company</th><th>Email</th><th>Sector</th><th>Verification</th><th>Since</th><th>Actions</th></tr></thead>
        <tbody>
        @forelse($companies as $c)
        <tr>
            <td>
                <a href="/companies/{{ $c->slug }}" target="_blank" style="color:var(--green);font-weight:700;">{{ $c->name }}</a>
                @if($c->is_featured)<span style="font-size:.65rem;color:var(--yellow);margin-left:.3rem;">★ Featured</span>@endif
            </td>
            <td style="color:var(--muted);">{{ $c->email ?? '—' }}</td>
            <td style="color:var(--muted);font-size:.78rem;">{{ $c->sector_id ?? '—' }}</td>
            <td><span class="badge b-{{ $c->verification_status ?? 'unverified' }}">{{ ucfirst($c->verification_status ?? 'unverified') }}</span></td>
            <td style="color:var(--muted);">{{ $c->founded_year ?? (date('Y',strtotime($c->created_at))) }}</td>
            <td>
                <form method="POST" action="/admin/companies/{{ $c->id }}/verify" style="display:inline;display:flex;gap:.3rem;flex-wrap:wrap;">
                    @csrf
                    <select name="level" style="padding:.25rem .4rem;border:1px solid var(--border);border-radius:6px;font-size:.74rem;outline:none;">
                        <option value="basic" {{ ($c->verification_status??'')=='basic'?'selected':'' }}>Basic</option>
                        <option value="verified" {{ ($c->verification_status??'')=='verified'?'selected':'' }}>Verified</option>
                        <option value="certified" {{ ($c->verification_status??'')=='certified'?'selected':'' }}>Certified</option>
                    </select>
                    <button type="submit" class="btn-sm" style="background:var(--green);color:#fff;">Set</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--muted);">No companies found.</td></tr>
        @endforelse
        </tbody>
    </table>
    @if($companies->hasPages())
    <div class="pagination">
        @if($companies->onFirstPage())<span class="page-link" style="opacity:.4;">‹ Prev</span>@else<a class="page-link" href="{{ $companies->previousPageUrl() }}">‹ Prev</a>@endif
        <span class="page-link current">{{ $companies->currentPage() }}</span>
        @if($companies->hasMorePages())<a class="page-link" href="{{ $companies->nextPageUrl() }}">Next ›</a>@else<span class="page-link" style="opacity:.4;">Next ›</span>@endif
    </div>
    @endif
</div>
@include('admin.end')
