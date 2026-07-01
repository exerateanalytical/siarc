<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Invest — {{ $offering->title_en }}</title></head>
<body>
@include('partials.nav')
<style>
.breadcrumb{padding:.75rem 2rem;font-size:.8rem;color:var(--muted);border-bottom:1px solid var(--border);background:var(--white);}
.breadcrumb a{color:var(--green);}
.page{max-width:820px;margin:2rem auto;padding:0 1.5rem;}
.card{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden;margin-bottom:1rem;}
.card-title{padding:.85rem 1.2rem;font-weight:700;border-bottom:1px solid var(--border);background:var(--light-bg);}
.card-body{padding:1.3rem;}
.offering-summary{background:var(--light-bg);border-radius:9px;padding:1rem;margin-bottom:1.3rem;display:grid;grid-template-columns:1fr 1fr 1fr;gap:.75rem;text-align:center;}
.os-val{font-size:1rem;font-weight:800;}
.os-lbl{font-size:.72rem;color:var(--muted);margin-top:2px;}
.form-group{margin-bottom:1.1rem;}
.form-label{display:block;font-size:.84rem;font-weight:600;margin-bottom:.4rem;}
.form-input{width:100%;padding:.65rem .85rem;border:1.5px solid var(--border);border-radius:8px;font-size:.9rem;outline:none;transition:border-color .15s;}
.form-input:focus{border-color:var(--green);}
.form-hint{font-size:.76rem;color:var(--muted);margin-top:.3rem;}
.payment-options{display:grid;grid-template-columns:1fr 1fr 1fr;gap:.75rem;margin-top:.5rem;}
.pay-opt{border:2px solid var(--border);border-radius:9px;padding:.9rem;text-align:center;cursor:pointer;transition:border-color .15s;}
.pay-opt:has(input:checked){border-color:var(--green);background:#f0fdf4;}
.pay-opt input[type=radio]{display:none;}
.pay-icon{font-size:1.6rem;margin-bottom:.3rem;}
.pay-name{font-size:.8rem;font-weight:700;}
.pay-sub{font-size:.7rem;color:var(--muted);}
.btn-submit{width:100%;padding:.85rem;background:var(--green);color:#fff;border:none;border-radius:9px;font-size:.95rem;font-weight:700;cursor:pointer;margin-top:.5rem;transition:background .15s;}
.btn-submit:hover{background:#00962e;}
.shares-preview{background:#f0fdf4;border:1px solid #86efac;border-radius:8px;padding:.8rem 1rem;margin-top:.6rem;font-size:.83rem;color:#166534;display:none;}
@media(max-width:500px){.offering-summary{grid-template-columns:1fr 1fr;}.payment-options{grid-template-columns:1fr;}}
</style>

<div class="breadcrumb">
    <a href="/offerings">Offerings</a> / <a href="/offerings/{{ $offering->id }}">{{ $offering->title_en }}</a> / Invest
</div>

<div class="page">
    <h1 style="font-size:1.3rem;font-weight:800;margin-bottom:1.2rem;">Invest in {{ $offering->title_en }}</h1>

    <div class="card">
        <div class="card-title">Offering Summary</div>
        <div class="card-body">
            <div class="offering-summary">
                <div><div class="os-val">{{ number_format($offering->target_amount/1000000,0) }}M XAF</div><div class="os-lbl">Target</div></div>
                <div><div class="os-val">{{ number_format($offering->share_price) }} XAF</div><div class="os-lbl">Unit Price</div></div>
                <div><div class="os-val">{{ number_format($offering->min_investment) }} XAF</div><div class="os-lbl">Minimum</div></div>
            </div>
            <p style="font-size:.85rem;color:var(--muted);line-height:1.6;">{{ $offering->summary_en }}</p>
        </div>
    </div>

    <div class="card">
        <div class="card-title">Investment Details</div>
        <div class="card-body">
            @if(!$investorProfile)
            <div style="background:#fff3cd;border:1px solid #ffc107;border-radius:9px;padding:1.1rem 1.2rem;margin-bottom:1.2rem;">
                <div style="font-weight:700;font-size:.9rem;margin-bottom:.3rem;">Investor profile required</div>
                <p style="font-size:.82rem;color:#856404;line-height:1.5;margin-bottom:.8rem;">You must complete your investor profile (KYC) before you can make investment pledges. This is required by the CMF.</p>
                <a href="/investor-profile" style="display:inline-block;padding:.5rem 1.1rem;background:var(--green);color:#fff;border-radius:7px;font-size:.82rem;font-weight:700;">Complete Investor Profile &rarr;</a>
            </div>
            @else
            @if($errors->any())
                <div style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;border-radius:8px;padding:.75rem 1rem;margin-bottom:1rem;font-size:.83rem;">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="/invest/{{ $offering->id }}">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="amount">Investment Amount (XAF)</label>
                    <input class="form-input" type="number" id="amount" name="amount"
                        min="{{ $offering->min_investment }}" max="{{ $offering->max_investment }}"
                        step="{{ $offering->share_price }}"
                        value="{{ old('amount', $offering->min_investment) }}"
                        oninput="calcShares(this.value)" required>
                    <div class="form-hint">Min: {{ number_format($offering->min_investment) }} XAF · Max: {{ number_format($offering->max_investment) }} XAF · Unit price: {{ number_format($offering->share_price) }} XAF</div>
                    <div class="shares-preview" id="sharesPreview"></div>
                </div>

                <div class="form-group">
                    <label class="form-label">Payment Method</label>
                    <div class="payment-options">
                        <label class="pay-opt">
                            <input type="radio" name="payment_method" value="mtn_momo" {{ old('payment_method','mtn_momo')==='mtn_momo'?'checked':'' }}>
                            <div class="pay-icon"><i data-lucide="smartphone" class="lic"></i></div>
                            <div class="pay-name">MTN MoMo</div>
                            <div class="pay-sub">Mobile Money</div>
                        </label>
                        <label class="pay-opt">
                            <input type="radio" name="payment_method" value="orange_money" {{ old('payment_method')==='orange_money'?'checked':'' }}>
                            <div class="pay-icon"><i data-lucide="circle" class="lic"></i></div>
                            <div class="pay-name">Orange Money</div>
                            <div class="pay-sub">Mobile Money</div>
                        </label>
                        <label class="pay-opt">
                            <input type="radio" name="payment_method" value="bank_transfer" {{ old('payment_method')==='bank_transfer'?'checked':'' }}>
                            <div class="pay-icon"><i data-lucide="landmark" class="lic"></i></div>
                            <div class="pay-name">Bank Transfer</div>
                            <div class="pay-sub">Direct BEAC</div>
                        </label>
                    </div>
                </div>

                <div style="background:#fffbeb;border:1px solid #fcd116;border-radius:8px;padding:.8rem 1rem;margin-bottom:1rem;font-size:.8rem;color:#92400e;">
                    <i data-lucide="alert-triangle" class="lic"></i> By proceeding you confirm this is a binding pledge. Your investment will be held in escrow until the offering closes. You have 24 hours to complete payment.
                </div>

                <button class="btn-submit" type="submit">Continue to Payment &rarr;</button>
            </form>
            @endif
        </div>
    </div>
</div>

<script>
function calcShares(amount) {
    const price = {{ $offering->share_price }};
    const shares = Math.floor(amount / price);
    const preview = document.getElementById('sharesPreview');
    if (amount >= {{ $offering->min_investment }} && shares > 0) {
        preview.style.display = 'block';
        preview.textContent = `You will receive ${shares.toLocaleString()} unit${shares!==1?'s':''} for ${Number(shares * price).toLocaleString()} XAF`;
    } else {
        preview.style.display = 'none';
    }
}
calcShares({{ $offering->min_investment }});
</script>
@include('partials.footer')
</body>
</html>
