<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Investor Profile & KYC — Galerie virtuelle de l'artisanat du Cameroun</title></head>
<body>
@include('partials.nav')
<style>
.page{max-width:760px;margin:0 auto;padding:1.5rem 1.5rem 3rem;}
h1{font-size:1.3rem;font-weight:800;margin-bottom:.3rem;}
.subtitle{font-size:.83rem;color:var(--muted);margin-bottom:1.5rem;}
.kyc-bar{display:flex;gap:.6rem;align-items:center;padding:.8rem 1rem;border-radius:var(--radius);margin-bottom:1.2rem;font-size:.84rem;}
.kyc-none{background:#fff3cd;color:#7a5900;}
.kyc-pending{background:#cce5ff;color:#0056b3;}
.kyc-approved{background:#d4edda;color:#007a33;}
.kyc-rejected{background:#f8d7da;color:#721c24;}
.card{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);padding:1.3rem;margin-bottom:1rem;}
.card h2{font-size:.98rem;font-weight:700;margin-bottom:1rem;}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:.8rem;}
@media(max-width:580px){.form-grid{grid-template-columns:1fr;}}
.form-group{display:flex;flex-direction:column;gap:.3rem;}
.form-group.full{grid-column:1/-1;}
label{font-size:.78rem;font-weight:600;color:var(--text);}
input,select,textarea{padding:.55rem .8rem;border:1px solid var(--border);border-radius:7px;font-size:.88rem;font-family:inherit;}
input:focus,select:focus,textarea:focus{outline:none;border-color:var(--green);}
.save-btn{padding:.65rem 1.6rem;background:var(--green);color:#fff;border:none;border-radius:8px;font-weight:700;font-size:.9rem;cursor:pointer;margin-top:.5rem;}
.save-btn:hover{background:#00962e;}
.success{background:#d4edda;border-radius:var(--radius);padding:.7rem 1rem;font-size:.84rem;color:#155724;margin-bottom:1rem;}
.limit-info{background:#e8f5e9;border-radius:8px;padding:.7rem .9rem;font-size:.8rem;color:#2e7d32;display:flex;align-items:center;gap:.6rem;}
</style>

<div class="page">
    <h1>Investor Profile & KYC</h1>
    <p class="subtitle">Complete your investor profile to unlock higher investment limits and access all offerings.</p>

    @if(session('success'))<div class="success">{{ session('success') }}</div>@endif

    <div class="kyc-bar {{ $kyc ? 'kyc-'.$kyc->status : 'kyc-none' }}">
        @if(!$kyc)
        <i data-lucide="alert-triangle" class="lic"></i> KYC not submitted — your investment limit is 500,000 XAF per offering.
        @elseif($kyc->status === 'pending')
        <i data-lucide="clock" class="lic"></i> KYC under review — submitted on {{ date('d M Y', strtotime($kyc->submitted_at ?? $kyc->created_at)) }}
        @elseif($kyc->status === 'approved')
        <i data-lucide="check-circle-2" class="lic"></i> KYC approved — no investment limit restrictions.
        @else
        <i data-lucide="x" class="lic"></i> KYC rejected — {{ $kyc->rejection_reason_en }}. Please resubmit with correct documents.
        @endif
    </div>

    <div class="limit-info">
        <span>ℹ</span>
        <span>Without KYC: max 500,000 XAF per offering &nbsp;|&nbsp; With approved KYC: unlimited</span>
    </div>

    <form method="POST" action="/investor-profile" style="margin-top:1rem;">
        @csrf
        <div class="card">
            <h2>Personal Information</h2>
            <div class="form-grid">
                <div class="form-group">
                    <label>Investor Type</label>
                    <select name="investor_type" required>
                        <option value="individual" {{ ($profile->investor_type??'individual')==='individual'?'selected':'' }}>Individual</option>
                        <option value="institutional" {{ ($profile->investor_type??'')==='institutional'?'selected':'' }}>Institutional</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>ID Type</label>
                    <select name="id_type" required>
                        <option value="cni" {{ ($profile->id_type??'cni')==='cni'?'selected':'' }}>National ID (CNI)</option>
                        <option value="passport" {{ ($profile->id_type??'')==='passport'?'selected':'' }}>Passport</option>
                        <option value="driving_licence" {{ ($profile->id_type??'')==='driving_licence'?'selected':'' }}>Driving Licence</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>National ID / Passport Number</label>
                    <input type="text" name="national_id" value="{{ $profile->national_id ?? '' }}" required placeholder="e.g. 123456789">
                    @error('national_id')<span style="font-size:.75rem;color:var(--red);">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label>Date of Birth</label>
                    <input type="date" name="dob" value="{{ $profile->dob ?? '' }}" required>
                </div>
                <div class="form-group">
                    <label>Nationality</label>
                    <input type="text" name="nationality" value="{{ $profile->nationality ?? 'Cameroonian' }}" required>
                </div>
                <div class="form-group">
                    <label>Occupation / Job Title</label>
                    <input type="text" name="occupation" value="{{ $profile->occupation ?? '' }}" required placeholder="e.g. Software Engineer">
                </div>
                <div class="form-group">
                    <label>Risk Tolerance</label>
                    <select name="risk_tolerance" required>
                        <option value="conservative" {{ ($profile->risk_tolerance??'')==='conservative'?'selected':'' }}>Conservative (prefer bonds)</option>
                        <option value="moderate" {{ ($profile->risk_tolerance??'moderate')==='moderate'?'selected':'' }}>Moderate (balanced)</option>
                        <option value="aggressive" {{ ($profile->risk_tolerance??'')==='aggressive'?'selected':'' }}>Aggressive (growth focused)</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="card">
            <h2>Banking Information</h2>
            <div class="form-grid">
                <div class="form-group">
                    <label>Bank Name (optional)</label>
                    <input type="text" name="bank_name" value="{{ $profile->bank_name ?? '' }}" placeholder="e.g. Afriland First Bank">
                </div>
                <div class="form-group">
                    <label>Account Number (optional)</label>
                    <input type="text" name="bank_account" value="{{ $profile->bank_account ?? '' }}" placeholder="e.g. 00123456789">
                </div>
            </div>
            <p style="font-size:.75rem;color:var(--muted);margin-top:.6rem;">Banking details are used to process refunds for oversubscribed offerings. They are stored securely and never shared with third parties except as required by CMF regulations.</p>
        </div>

        <button type="submit" class="save-btn">Save Investor Profile</button>
    </form>
</div>
@include('partials.footer')
</body>
</html>
