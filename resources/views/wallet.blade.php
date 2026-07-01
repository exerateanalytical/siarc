<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>My Wallet — Galerie virtuelle de l'artisanat du Cameroun</title></head>
<body>
@include('partials.nav')
<style>
.page{max-width:760px;margin:0 auto;padding:1.5rem;}
h1{font-size:1.3rem;font-weight:800;margin-bottom:1rem;}
.wallet-card{background:linear-gradient(135deg,var(--green),#009040);border-radius:var(--radius);padding:1.8rem;color:#fff;margin-bottom:1.2rem;}
.wallet-label{font-size:.78rem;opacity:.8;margin-bottom:.3rem;}
.wallet-balance{font-size:2.2rem;font-weight:900;letter-spacing:-1px;}
.wallet-currency{font-size:.85rem;opacity:.7;margin-top:.1rem;}
.wallet-pending{font-size:.8rem;opacity:.75;margin-top:.5rem;}
.actions{display:flex;gap:.6rem;margin-top:1.1rem;}
.action-btn{padding:.5rem 1.1rem;border-radius:7px;font-size:.82rem;font-weight:700;border:none;cursor:pointer;}
.btn-topup{background:#fff;color:var(--green);}
.btn-withdraw{background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3);}
.section-title{font-size:.85rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:.7rem;}
.table-wrap{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden;}
table{width:100%;border-collapse:collapse;}
th{padding:.5rem .8rem;text-align:left;font-size:.72rem;font-weight:700;text-transform:uppercase;color:var(--muted);border-bottom:2px solid var(--border);background:var(--light-bg);}
td{padding:.7rem .8rem;font-size:.83rem;border-bottom:1px solid var(--border);}
tr:last-child td{border-bottom:none;}
.amount-credit{color:var(--green);font-weight:700;}
.amount-debit{color:var(--red);font-weight:700;}
.empty{text-align:center;padding:2.5rem;color:var(--muted);font-size:.85rem;}
.info-box{background:#e8f5e9;border-radius:var(--radius);padding:.8rem 1rem;font-size:.82rem;color:#2e7d32;margin-bottom:1rem;}
</style>

<div class="page">
    <h1>My Wallet</h1>
    <div class="info-box">Your wallet balance can be used to fund investment pledges without entering payment details each time.</div>

    <div class="wallet-card">
        <div class="wallet-label">Available Balance</div>
        <div class="wallet-balance">{{ number_format($wallet->balance ?? 0) }}</div>
        <div class="wallet-currency">XAF · Franc CFA d'Afrique Centrale</div>
        @if(($wallet->pending_balance ?? 0) > 0)
        <div class="wallet-pending">+ {{ number_format($wallet->pending_balance) }} XAF pending</div>
        @endif
        <div class="actions">
            <button class="action-btn btn-topup" onclick="document.getElementById('topup-modal').style.display='flex'">+ Top Up</button>
            <button class="action-btn btn-withdraw">Withdraw</button>
        </div>
    </div>

    <div class="section-title">Transaction History</div>
    @if($transactions->isEmpty())
    <div class="table-wrap">
        <div class="empty">No transactions yet. Top up your wallet to get started.</div>
    </div>
    @else
    <div class="table-wrap">
        <table>
            <thead><tr><th>Date</th><th>Description</th><th>Amount (XAF)</th><th>Balance</th></tr></thead>
            <tbody>
                @foreach($transactions as $tx)
                <tr>
                    <td>{{ $tx->created_at ? date('d M Y H:i', strtotime($tx->created_at)) : '' }}</td>
                    <td>{{ $tx->description ?? ucfirst($tx->type ?? 'Transaction') }}</td>
                    <td class="{{ ($tx->type??'debit')==='credit' ? 'amount-credit' : 'amount-debit' }}">
                        {{ ($tx->type??'debit')==='credit' ? '+' : '-' }}{{ number_format($tx->amount ?? 0) }}
                    </td>
                    <td>{{ number_format($tx->balance_after ?? 0) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

{{-- Top-up modal --}}
<div id="topup-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:999;align-items:center;justify-content:center;" onclick="if(event.target===this)this.style.display='none'">
    <div style="background:#fff;border-radius:var(--radius);padding:1.5rem;width:340px;max-width:95vw;">
        <h3 style="margin-bottom:.5rem;font-size:1rem;font-weight:700;">Top Up Wallet</h3>
        <p style="font-size:.82rem;color:var(--muted);margin-bottom:1rem;">Add funds to your Galerie virtuelle de l'artisanat du Cameroun wallet.</p>
        @if(session('topup_error'))<div style="background:#f8d7da;border-radius:6px;padding:.5rem .8rem;font-size:.82rem;color:#721c24;margin-bottom:.7rem;">{{ session('topup_error') }}</div>@endif
        <form method="POST" action="/wallet/topup">
            @csrf
            <input type="number" name="amount" placeholder="Amount (XAF)" min="5000" step="1000" required style="width:100%;padding:.55rem .8rem;border:1px solid var(--border);border-radius:7px;font-size:.88rem;margin-bottom:.7rem;box-sizing:border-box;">
            <select name="method" style="width:100%;padding:.55rem .8rem;border:1px solid var(--border);border-radius:7px;font-size:.88rem;margin-bottom:1rem;">
                <option value="mtn_momo">MTN Mobile Money</option>
                <option value="orange_money">Orange Money</option>
                <option value="bank_transfer">Bank Transfer</option>
            </select>
            <div style="display:flex;gap:.5rem;">
                <button type="button" onclick="document.getElementById('topup-modal').style.display='none'" style="flex:1;padding:.55rem;border:1px solid var(--border);border-radius:7px;background:#fff;font-size:.85rem;cursor:pointer;">Cancel</button>
                <button type="submit" style="flex:1;padding:.55rem;border:none;border-radius:7px;background:var(--green);color:#fff;font-weight:700;font-size:.85rem;cursor:pointer;">Proceed</button>
            </div>
        </form>
    </div>
</div>
@include('partials.footer')
</body>
</html>
