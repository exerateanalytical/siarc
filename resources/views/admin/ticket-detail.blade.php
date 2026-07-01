<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Ticket #{{ $ticket->ticket_number }} — Admin</title></head><body>
@php $pageTitle = 'Ticket #' . $ticket->ticket_number; @endphp
@include('admin.nav')
<style>
.back-link{font-size:.82rem;color:var(--green);display:inline-block;margin-bottom:1rem;}
.ticket-card{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);padding:1.3rem;margin-bottom:1rem;}
.ticket-meta{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:.75rem;margin-bottom:1rem;padding-bottom:1rem;border-bottom:1px solid var(--border);}
.meta-item label{display:block;font-size:.72rem;text-transform:uppercase;color:var(--muted);font-weight:700;margin-bottom:.2rem;}
.meta-item span{font-size:.85rem;font-weight:600;}
.badge{display:inline-block;font-size:.67rem;font-weight:700;padding:2px 7px;border-radius:99px;}
.b-open{background:#fff3cd;color:#856404;}
.b-closed{background:#f8f9fa;color:#6c757d;}
.b-waiting_user{background:#cce5ff;color:#004085;}
.messages{display:flex;flex-direction:column;gap:.8rem;margin-bottom:1.2rem;}
.msg{padding:.9rem 1rem;border-radius:10px;font-size:.84rem;line-height:1.6;max-width:80%;}
.msg-user{background:var(--light-bg);border:1px solid var(--border);align-self:flex-start;}
.msg-staff{background:#e8f5e9;border:1px solid #a5d6a7;align-self:flex-end;}
.msg-meta{font-size:.72rem;color:var(--muted);margin-top:.3rem;}
.reply-card{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);padding:1.2rem;}
.reply-card h3{font-size:.9rem;font-weight:700;margin-bottom:.75rem;}
textarea{width:100%;padding:.7rem;border:1.5px solid var(--border);border-radius:8px;font-size:.85rem;resize:vertical;min-height:120px;outline:none;font-family:inherit;}
textarea:focus{border-color:var(--green);}
.btn-row{display:flex;gap:.75rem;margin-top:.75rem;}
.btn-primary{padding:.6rem 1.4rem;background:var(--green);color:#fff;border:none;border-radius:8px;font-weight:700;font-size:.87rem;cursor:pointer;}
.btn-close{padding:.6rem 1.1rem;background:var(--light-bg);color:var(--muted);border:1px solid var(--border);border-radius:8px;font-weight:700;font-size:.87rem;cursor:pointer;}
</style>

<a class="back-link" href="/admin/tickets">&larr; All tickets</a>

<div class="ticket-card">
    <div style="font-size:1.1rem;font-weight:800;margin-bottom:.75rem;">{{ $ticket->subject }}</div>
    <div class="ticket-meta">
        <div class="meta-item"><label>User</label><span>{{ $ticket->first_name }} {{ $ticket->last_name }}</span></div>
        <div class="meta-item"><label>Email</label><span style="font-size:.8rem;">{{ $ticket->email }}</span></div>
        <div class="meta-item"><label>Status</label><span><span class="badge b-{{ $ticket->status }}">{{ str_replace('_',' ',$ticket->status) }}</span></span></div>
        <div class="meta-item"><label>Priority</label><span>{{ ucfirst($ticket->priority) }}</span></div>
        <div class="meta-item"><label>Created</label><span>{{ date('d M Y H:i',strtotime($ticket->created_at)) }}</span></div>
    </div>

    <div class="messages">
        @forelse($messages as $msg)
        <div class="msg {{ $msg->is_from_staff ? 'msg-staff' : 'msg-user' }}">
            {{ $msg->body }}
            <div class="msg-meta">{{ $msg->is_from_staff ? 'Staff' : $ticket->first_name }} &bull; {{ date('d M Y H:i',strtotime($msg->created_at)) }}</div>
        </div>
        @empty
        <div style="color:var(--muted);font-size:.84rem;text-align:center;padding:1rem;">No messages yet — reply to start the conversation.</div>
        @endforelse
    </div>

    @if($ticket->status !== 'closed')
    <div class="reply-card">
        <h3>Reply</h3>
        <form method="POST" action="/admin/tickets/{{ $ticket->id }}/reply">
            @csrf
            <textarea name="body" placeholder="Type your reply…" required></textarea>
            <div class="btn-row">
                <button type="submit" class="btn-primary">Send Reply</button>
                <form method="POST" action="/admin/tickets/{{ $ticket->id }}/close" style="display:inline;margin:0;padding:0;">
                    @csrf<button type="submit" class="btn-close" onclick="return confirm('Close this ticket?')">Close Ticket</button>
                </form>
            </div>
        </form>
    </div>
    @else
    <div style="background:var(--light-bg);border-radius:8px;padding:.85rem;text-align:center;font-size:.83rem;color:var(--muted);">This ticket is closed. Resolved {{ $ticket->resolved_at ? date('d M Y',strtotime($ticket->resolved_at)) : '' }}.</div>
    @endif
</div>
@include('admin.end')
