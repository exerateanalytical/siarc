<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Complete Payment — Galerie virtuelle de l'artisanat du Cameroun</title></head>
<body>
@include('partials.nav')
<style>
.page{max-width:580px;margin:2rem auto;padding:0 1.5rem;}
.card{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden;margin-bottom:1rem;}
.card-title{padding:.85rem 1.2rem;font-weight:700;border-bottom:1px solid var(--border);background:var(--light-bg);}
.card-body{padding:1.3rem;}
.summary-row{display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid var(--border);font-size:.85rem;}
.summary-row:last-child{border-bottom:none;}
.s-lbl{color:var(--muted);}
.s-val{font-weight:700;}
.amount-big{text-align:center;padding:1.2rem;background:var(--light-bg);border-radius:9px;margin-bottom:1.2rem;}
.amount-big .amt{font-size:2rem;font-weight:800;}
.amount-big .lbl{font-size:.8rem;color:var(--muted);margin-top:3px;}
.method-card{border:2px solid var(--green);border-radius:9px;padding:1rem;text-align:center;margin-bottom:1.2rem;}
.method-icon{font-size:2rem;margin-bottom:.3rem;}
.method-name{font-weight:700;}
.phone-input{width:100%;padding:.65rem .85rem;border:1.5px solid var(--border);border-radius:8px;font-size:1rem;outline:none;margin-top:.5rem;transition:border-color .15s;}
.phone-input:focus{border-color:var(--green);}
.btn-pay{width:100%;padding:.9rem;background:var(--green);color:#fff;border:none;border-radius:9px;font-size:1rem;font-weight:700;cursor:pointer;transition:background .15s;margin-top:.5rem;}
.btn-pay:hover{background:#00962e;}
.btn-cancel{display:block;text-align:center;color:var(--muted);font-size:.83rem;margin-top:.75rem;}
.status-badge{display:inline-block;padding:3px 10px;border-radius:99px;font-size:.75rem;font-weight:700;}
.st-pending_payment{background:#fff3cd;color:#856404;}
.st-confirmed{background:#d4edda;color:#007a33;}
.timer{text-align:center;font-size:.82rem;color:var(--muted);margin-bottom:.8rem;}
</style>

@php
    $method = $pledge->payment_method;
    $methodLabel = match($method){ 'mtn_momo'=>'MTN MoMo', 'orange_money'=>'Orange Money', 'bank_transfer'=>'Bank Transfer', default=>$method };
    $methodIcon = match($method){ 'mtn_momo'=>'smartphone', 'orange_money'=>'circle', 'bank_transfer'=>'landmark', default=>'credit-card' };
    $expires = $pledge->expires_at ? date('d M Y H:i', strtotime($pledge->expires_at)) : null;
@endphp

<div class="page">
    <h1 style="font-size:1.3rem;font-weight:800;margin-bottom:1.2rem;">Complete Your Payment</h1>

    <div class="card">
        <div class="card-title">Payment Summary</div>
        <div class="card-body">
            <div class="amount-big">
                <div class="amt">{{ number_format($pledge->amount) }} XAF</div>
                <div class="lbl">Investment amount</div>
            </div>
            <div class="summary-row"><span class="s-lbl">Offering</span><span class="s-val">{{ $pledge->title_en }}</span></div>
            <div class="summary-row"><span class="s-lbl">Company</span><span class="s-val"><a href="/companies/{{ $pledge->company_slug }}" style="color:var(--green)">{{ $pledge->company_name }}</a></span></div>
            <div class="summary-row"><span class="s-lbl">Units</span><span class="s-val">{{ number_format($pledge->shares_requested) }}</span></div>
            <div class="summary-row"><span class="s-lbl">Status</span><span class="s-val"><span class="status-badge st-{{ str_replace('_','-',$pledge->status) }}">{{ ucfirst(str_replace('_',' ',$pledge->status)) }}</span></span></div>
            @if($expires)<div class="summary-row"><span class="s-lbl">Expires</span><span class="s-val">{{ $expires }}</span></div>@endif
        </div>
    </div>

    @if($pledge->status === 'pending_payment')
        <div class="card">
            <div class="card-title">{{ $methodLabel }} Payment</div>
            <div class="card-body">
                <div class="method-card">
                    <div class="method-icon">{{ $methodIcon }}</div>
                    <div class="method-name">{{ $methodLabel }}</div>
                </div>

                @if(in_array($method, ['mtn_momo', 'orange_money']))
                    <p style="font-size:.85rem;color:var(--muted);margin-bottom:.75rem;line-height:1.6;">
                        Enter your {{ $methodLabel }} phone number below. You will receive a payment prompt on your phone.
                    </p>
                    <input class="phone-input" type="tel" placeholder="+237 6XX XXX XXX" id="phone">
                    <div style="font-size:.75rem;color:var(--muted);margin-top:.35rem;">Cameroon numbers only (+237)</div>
                    <button class="btn-pay" onclick="initiatePayment()">Send Payment Request</button>
                @else
                    <p style="font-size:.85rem;color:var(--muted);margin-bottom:.75rem;line-height:1.6;">
                        Transfer the exact amount to the account below, then confirm using your transaction reference.
                    </p>
                    <div style="background:var(--light-bg);border-radius:8px;padding:1rem;font-size:.85rem;margin-bottom:1rem;">
                        <div><strong>Bank:</strong> Afriland First Bank</div>
                        <div><strong>Account:</strong> 001 000 1234567 89</div>
                        <div><strong>Reference:</strong> CAM-{{ strtoupper(substr($pledge->id,0,8)) }}</div>
                        <div><strong>Amount:</strong> {{ number_format($pledge->amount) }} XAF</div>
                    </div>
                    <button class="btn-pay" onclick="alert('Transfer confirmation feature coming soon. Please contact support@camcompany.cm with your proof of transfer.')">I Have Transferred</button>
                @endif
                <a class="btn-cancel" href="/dashboard">Back to Dashboard</a>
            </div>
        </div>
    @else
        <div style="text-align:center;padding:2rem;background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);">
            <div style="font-size:2.5rem;margin-bottom:.5rem;"><i data-lucide="check-circle-2" class="lic"></i></div>
            <div style="font-weight:700;margin-bottom:.3rem;">Payment {{ ucfirst($pledge->status) }}</div>
            <div style="color:var(--muted);font-size:.85rem;margin-bottom:1rem;">Your investment has been recorded.</div>
            <a href="/dashboard" style="display:inline-block;padding:.6rem 1.5rem;background:var(--green);color:#fff;border-radius:8px;font-weight:600;">Back to Dashboard</a>
        </div>
    @endif
</div>

<script>
function initiatePayment() {
    const phone = document.getElementById('phone').value.trim();
    if (!phone) { alert('Please enter your phone number.'); return; }
    alert('Payment prompt sent to ' + phone + '.\n\nIn production this would trigger a real ' + '{{ $methodLabel }}' + ' push request.\n\nPlease approve the request on your phone.');
}
</script>
@include('partials.footer')
</body>
</html>
