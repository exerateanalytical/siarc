<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Users — Admin</title></head><body>
@php $pageTitle = 'Users'; @endphp
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
.b-active{background:#d4edda;color:#155724;}
.b-suspended{background:#fef2f2;color:#991b1b;}
.b-admin{background:#e8f5e9;color:var(--green);}
.btn-sm{padding:.3rem .7rem;border-radius:6px;font-size:.75rem;font-weight:700;border:none;cursor:pointer;}
.btn-warn{background:#fff3cd;color:#856404;}
.btn-danger{background:#fef2f2;color:#991b1b;}
.btn-green{background:#d4edda;color:#155724;}
.pagination{padding:1rem;display:flex;gap:.4rem;justify-content:center;}
.page-link{padding:.4rem .7rem;border:1px solid var(--border);border-radius:6px;font-size:.8rem;color:var(--text);}
.page-link.current{background:var(--green);color:#fff;border-color:var(--green);}
</style>

<div class="toolbar">
    <form method="GET" action="/admin/users" style="display:flex;gap:.5rem;">
        <input class="search-input" type="text" name="q" value="{{ $q }}" placeholder="Search by name or email…">
        <button type="submit" style="padding:.5rem 1rem;background:var(--green);color:#fff;border:none;border-radius:8px;font-size:.85rem;font-weight:700;cursor:pointer;">Search</button>
        @if($q)<a href="/admin/users" style="padding:.5rem .8rem;border:1px solid var(--border);border-radius:8px;font-size:.82rem;color:var(--muted);">Clear</a>@endif
    </form>
    <span style="margin-left:auto;font-size:.82rem;color:var(--muted);">{{ $users->total() }} users total</span>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Name</th><th>Email</th><th>Type</th><th>Status</th><th>Joined</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse($users as $u)
        <tr>
            <td>
                {{ $u->first_name }} {{ $u->last_name }}
                @if($u->is_admin)<span class="badge b-admin">Admin</span>@endif
            </td>
            <td style="color:var(--muted);">{{ $u->email }}</td>
            <td style="color:var(--muted);">{{ str_replace('_',' ',ucfirst($u->user_type ?? 'investor')) }}</td>
            <td><span class="badge b-{{ $u->status }}">{{ $u->status }}</span></td>
            <td style="color:var(--muted);">{{ $u->created_at ? date('d M Y',strtotime($u->created_at)) : '' }}</td>
            <td>
                <form method="POST" action="/admin/users/{{ $u->id }}/toggle-status" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn-sm {{ $u->status==='active'?'btn-warn':'btn-green' }}" onclick="return confirm('Toggle status for {{ $u->email }}?')">
                        {{ $u->status==='active' ? 'Suspend' : 'Activate' }}
                    </button>
                </form>
                <form method="POST" action="/admin/users/{{ $u->id }}/toggle-admin" style="display:inline;margin-left:.3rem;">
                    @csrf
                    <button type="submit" class="btn-sm {{ $u->is_admin?'btn-danger':'btn-sm' }}" style="{{ !$u->is_admin?'background:var(--light-bg);color:var(--muted);':'' }}" onclick="return confirm('Toggle admin for {{ $u->email }}?')">
                        {{ $u->is_admin ? '− Admin' : '+ Admin' }}
                    </button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--muted);">No users found.</td></tr>
        @endforelse
        </tbody>
    </table>
    @if($users->hasPages())
    <div class="pagination">
        @if($users->onFirstPage())<span class="page-link" style="opacity:.4;">‹ Prev</span>@else<a class="page-link" href="{{ $users->previousPageUrl() }}">‹ Prev</a>@endif
        <span class="page-link current">{{ $users->currentPage() }}</span>
        <span class="page-link" style="opacity:.5;">of {{ $users->lastPage() }}</span>
        @if($users->hasMorePages())<a class="page-link" href="{{ $users->nextPageUrl() }}">Next ›</a>@else<span class="page-link" style="opacity:.4;">Next ›</span>@endif
    </div>
    @endif
</div>
@include('admin.end')
