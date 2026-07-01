<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>KYC Applications — Admin</title></head><body>
@php $pageTitle = 'KYC Applications'; @endphp
@include('admin.nav')
<style>
.tab-bar{display:flex;gap:.4rem;margin-bottom:1.2rem;border-bottom:2px solid var(--border);padding-bottom:0;}
.tab{padding:.55rem 1.1rem;font-size:.83rem;font-weight:700;color:var(--muted);border-bottom:2px solid transparent;margin-bottom:-2px;cursor:pointer;}
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
.reject-form{display:inline;position:relative;}
.empty-state{text-align:center;padding:3rem;color:var(--muted);}
.empty-icon{font-size:2.5rem;margin-bottom:.8rem;}
</style>

<div class="tab-bar">
    <a class="tab {{ $status==='pending'?'active':'' }}" href="/admin/kyc?status=pending">Pending @php $pc=DB::table('kyc_applications')->where('status','pending')->count(); @endphp @if($pc)({{ $pc }})@endif</a>
    <a class="tab {{ $status==='approved'?'active':'' }}" href="/admin/kyc?status=approved">Approved</a>
    <a class="tab {{ $status==='rejected'?'active':'' }}" href="/admin/kyc?status=rejected">Rejected</a>
</div>

<div class="card">
    @if($applications->isEmpty())
    <div class="empty-state">
        <div class="empty-icon"><i data-lucide="contact" class="lic"></i></div>
        <div style="font-weight:700;margin-bottom:.4rem;">No {{ $status }} KYC applications</div>
        <div style="font-size:.82rem;">{{ $status==='pending' ? 'All applications are processed. Great!' : 'Nothing to show here.' }}</div>
    </div>
    @else
    <table>
        <thead><tr><th>Applicant</th><th>Email</th><th>Type</th><th>ID Type</th><th>Submitted</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        @foreach($applications as $app)
        <tr>
            <td><strong>{{ $app->first_name }} {{ $app->last_name }}</strong></td>
            <td style="color:var(--muted);">{{ $app->email }}</td>
            <td>{{ ucfirst($app->tier ?? 'individual') }}</td>
            <td>{{ strtoupper($app->id_type ?? '—') }}</td>
            <td style="color:var(--muted);">{{ $app->submitted_at ? date('d M Y',strtotime($app->submitted_at)) : date('d M Y',strtotime($app->created_at)) }}</td>
            <td><span class="badge b-{{ $app->status }}">{{ $app->status }}</span></td>
            <td>
                @if($app->status === 'pending')
                <form method="POST" action="/admin/kyc/{{ $app->id }}/approve" style="display:inline;">
                    @csrf<button type="submit" class="btn-sm btn-approve">Approve</button>
                </form>
                <form method="POST" action="/admin/kyc/{{ $app->id }}/reject" style="display:inline;margin-left:.3rem;" onsubmit="var r=prompt('Rejection reason (optional):');if(r!==null){this.querySelector('[name=reason]').value=r;return true;}return false;">
                    @csrf<input type="hidden" name="reason" value="Does not meet requirements.">
                    <button type="submit" class="btn-sm btn-reject">Reject</button>
                </form>
                @else
                <span style="font-size:.76rem;color:var(--muted);">{{ $app->reviewed_at ? date('d M Y',strtotime($app->reviewed_at)) : 'Processed' }}</span>
                @if($app->rejection_reason_en)<div style="font-size:.74rem;color:var(--red);margin-top:2px;">{{ Str::limit($app->rejection_reason_en,40) }}</div>@endif
                @endif
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
    @if($applications->hasPages())
    <div style="padding:1rem;display:flex;gap:.4rem;justify-content:center;">
        @if(!$applications->onFirstPage())<a style="padding:.4rem .7rem;border:1px solid var(--border);border-radius:6px;font-size:.8rem;" href="{{ $applications->previousPageUrl() }}">‹ Prev</a>@endif
        <span style="padding:.4rem .7rem;background:var(--green);color:#fff;border-radius:6px;font-size:.8rem;">{{ $applications->currentPage() }}</span>
        @if($applications->hasMorePages())<a style="padding:.4rem .7rem;border:1px solid var(--border);border-radius:6px;font-size:.8rem;" href="{{ $applications->nextPageUrl() }}">Next ›</a>@endif
    </div>
    @endif
    @endif
</div>
@include('admin.end')
