<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Company Claims — Admin</title></head><body>
@php $pageTitle = 'Company Claims'; @endphp
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
.b-pending{background:#fff3cd;color:#856404;}
.b-approved{background:#d4edda;color:#155724;}
.b-rejected{background:#fef2f2;color:#991b1b;}
.btn-sm{padding:.35rem .75rem;border-radius:6px;font-size:.76rem;font-weight:700;border:none;cursor:pointer;}
.btn-approve{background:#d4edda;color:#155724;}
.btn-reject{background:#fef2f2;color:#991b1b;}
.empty-state{text-align:center;padding:3rem;color:var(--muted);}
</style>

<div class="tab-bar">
    <a class="tab {{ $status==='pending'?'active':'' }}" href="/admin/claims?status=pending">Pending @php $pc=DB::table('verification_applications')->where('status','pending')->count(); @endphp @if($pc)({{ $pc }})@endif</a>
    <a class="tab {{ $status==='approved'?'active':'' }}" href="/admin/claims?status=approved">Approved</a>
    <a class="tab {{ $status==='rejected'?'active':'' }}" href="/admin/claims?status=rejected">Rejected</a>
</div>

<div class="card">
    @if($claims->isEmpty())
    <div class="empty-state">
        <div style="font-size:2.5rem;margin-bottom:.8rem;"><i data-lucide="building-2" class="lic"></i></div>
        <div style="font-weight:700;margin-bottom:.4rem;">No {{ $status }} company claims</div>
        <div style="font-size:.82rem;">{{ $status==='pending'?'All caught up!':'Nothing here.' }}</div>
    </div>
    @else
    <table>
        <thead><tr><th>Company</th><th>Claimant</th><th>Email</th><th>Submitted</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        @foreach($claims as $c)
        <tr>
            <td><a href="/companies/{{ $c->company_slug }}" target="_blank" style="color:var(--green);font-weight:700;">{{ $c->company_name }}</a></td>
            <td>{{ $c->first_name }} {{ $c->last_name }}</td>
            <td style="color:var(--muted);">{{ $c->email }}</td>
            <td style="color:var(--muted);">{{ $c->submitted_at ? date('d M Y',strtotime($c->submitted_at)) : date('d M Y',strtotime($c->created_at)) }}</td>
            <td><span class="badge b-{{ $c->status }}">{{ $c->status }}</span></td>
            <td>
                @if($c->status === 'pending')
                <form method="POST" action="/admin/claims/{{ $c->id }}/approve" style="display:inline;">
                    @csrf<button type="submit" class="btn-sm btn-approve" onclick="return confirm('Approve claim for {{ $c->company_name }}? This will add the user as owner and upgrade verification to verified.')">Approve</button>
                </form>
                <form method="POST" action="/admin/claims/{{ $c->id }}/reject" style="display:inline;margin-left:.3rem;" onsubmit="var r=prompt('Rejection reason:');if(r!==null){this.querySelector('[name=reason]').value=r;return true;}return false;">
                    @csrf<input type="hidden" name="reason" value="Claim could not be verified.">
                    <button type="submit" class="btn-sm btn-reject">Reject</button>
                </form>
                @else
                <span style="font-size:.76rem;color:var(--muted);">{{ $c->reviewed_at ? date('d M Y',strtotime($c->reviewed_at)) : 'Processed' }}</span>
                @endif
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
    @endif
</div>
@include('admin.end')
