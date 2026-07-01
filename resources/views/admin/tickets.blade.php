<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Support Tickets — Admin</title></head><body>
@php $pageTitle = 'Support Tickets'; @endphp
@include('admin.nav')
<style>
.tab-bar{display:flex;gap:.4rem;margin-bottom:1.2rem;border-bottom:2px solid var(--border);}
.tab{padding:.55rem 1.1rem;font-size:.83rem;font-weight:700;color:var(--muted);border-bottom:2px solid transparent;margin-bottom:-2px;}
.tab.active{color:var(--green);border-bottom-color:var(--green);}
.card{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden;}
table{width:100%;border-collapse:collapse;}
th{padding:.55rem .85rem;font-size:.72rem;font-weight:700;text-transform:uppercase;color:var(--muted);border-bottom:2px solid var(--border);background:var(--light-bg);text-align:left;}
td{padding:.7rem .85rem;font-size:.82rem;border-bottom:1px solid var(--border);vertical-align:middle;}
tr:last-child td{border-bottom:none;}
.badge{display:inline-block;font-size:.67rem;font-weight:700;padding:1px 6px;border-radius:99px;}
.b-open{background:#fff3cd;color:#856404;}
.b-waiting_user{background:#cce5ff;color:#004085;}
.b-closed{background:#f8f9fa;color:#6c757d;}
.b-low{background:#f8f9fa;color:#6c757d;}
.b-normal{background:#e8f4fd;color:#0c63a0;}
.b-medium{background:#fff3cd;color:#856404;}
.b-high{background:#fef2f2;color:#991b1b;}
.b-urgent{background:#ff4d4d;color:#fff;}
.btn-sm{padding:.35rem .75rem;border-radius:6px;font-size:.76rem;font-weight:700;border:none;cursor:pointer;background:var(--green);color:#fff;}
.empty-state{text-align:center;padding:3rem;color:var(--muted);}
</style>

<div class="tab-bar">
    <a class="tab {{ $status==='open'?'active':'' }}" href="/admin/tickets?status=open">Open @php $tc=DB::table('tickets')->where('status','open')->count();@endphp @if($tc)({{ $tc }})@endif</a>
    <a class="tab {{ $status==='waiting_user'?'active':'' }}" href="/admin/tickets?status=waiting_user">Waiting on User</a>
    <a class="tab {{ $status==='closed'?'active':'' }}" href="/admin/tickets?status=closed">Closed</a>
</div>

<div class="card">
    @if($tickets->isEmpty())
    <div class="empty-state">
        <div style="font-size:2.5rem;margin-bottom:.8rem;"><i data-lucide="ticket" class="lic"></i></div>
        <div style="font-weight:700;margin-bottom:.4rem;">No {{ str_replace('_',' ',$status) }} tickets</div>
        <div style="font-size:.82rem;">{{ $status==='open'?'All support tickets are handled. Great!':'Nothing here.' }}</div>
    </div>
    @else
    <table>
        <thead><tr><th>Ticket #</th><th>User</th><th>Subject</th><th>Priority</th><th>Status</th><th>Created</th><th>Action</th></tr></thead>
        <tbody>
        @foreach($tickets as $t)
        <tr>
            <td style="font-weight:700;font-family:monospace;">{{ $t->ticket_number }}</td>
            <td>{{ $t->first_name }} {{ $t->last_name }}<div style="font-size:.74rem;color:var(--muted);">{{ $t->email }}</div></td>
            <td>{{ Str::limit($t->subject,40) }}</td>
            <td><span class="badge b-{{ $t->priority }}">{{ $t->priority }}</span></td>
            <td><span class="badge b-{{ $t->status }}">{{ str_replace('_',' ',$t->status) }}</span></td>
            <td style="color:var(--muted);">{{ date('d M Y',strtotime($t->created_at)) }}</td>
            <td><a href="/admin/tickets/{{ $t->id }}" class="btn-sm" style="display:inline-block;">View &amp; Reply</a></td>
        </tr>
        @endforeach
        </tbody>
    </table>
    @endif
</div>
@include('admin.end')
