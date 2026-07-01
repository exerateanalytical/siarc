<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Claim {{ $company->name }} — Galerie virtuelle de l'artisanat du Cameroun</title></head>
<body>
@include('partials.nav')
<style>
.page{max-width:680px;margin:0 auto;padding:1.5rem 1.5rem 3rem;}
h1{font-size:1.3rem;font-weight:800;margin-bottom:.3rem;}
.subtitle{font-size:.83rem;color:var(--muted);margin-bottom:1.5rem;}
.co-header{display:flex;gap:.8rem;align-items:center;background:var(--white);border-radius:var(--radius);padding:1rem;box-shadow:var(--shadow);margin-bottom:1.2rem;}
.co-logo{width:52px;height:52px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;font-weight:800;color:#fff;flex-shrink:0;}
.co-name{font-size:1rem;font-weight:700;}
.co-meta{font-size:.78rem;color:var(--muted);margin-top:.2rem;}
.existing-claim{background:#fff3cd;border-radius:var(--radius);padding:1rem;font-size:.85rem;color:#7a5900;margin-bottom:1.2rem;}
.card{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);padding:1.3rem;margin-bottom:1rem;}
.card h2{font-size:.98rem;font-weight:700;margin-bottom:.8rem;}
.form-group{display:flex;flex-direction:column;gap:.3rem;margin-bottom:.75rem;}
label{font-size:.78rem;font-weight:600;}
input,select,textarea{padding:.55rem .8rem;border:1px solid var(--border);border-radius:7px;font-size:.88rem;font-family:inherit;}
input:focus,select:focus,textarea:focus{outline:none;border-color:var(--green);}
.steps{counter-reset:step;list-style:none;padding:0;margin:0;}
.steps li{counter-increment:step;display:flex;gap:.7rem;align-items:flex-start;padding:.5rem 0;font-size:.83rem;color:var(--muted);}
.steps li::before{content:counter(step);width:20px;height:20px;background:var(--green);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:800;flex-shrink:0;}
.submit-btn{padding:.65rem 1.6rem;background:var(--green);color:#fff;border:none;border-radius:8px;font-weight:700;font-size:.9rem;cursor:pointer;}
.submit-btn:hover{background:#00962e;}
.success{background:#d4edda;border-radius:var(--radius);padding:.9rem 1rem;font-size:.85rem;color:#155724;margin-bottom:1rem;}
</style>

<div class="page">
    <div class="breadcrumb" style="font-size:.78rem;color:var(--muted);margin-bottom:1rem;"><a href="/" style="color:var(--muted);">Home</a> › <a href="/companies/{{ $company->slug }}" style="color:var(--muted);">{{ $company->name }}</a> › Claim</div>

    @if(session('success'))
    <div class="success">{{ session('success') }}</div>
    @else

    <h1>Claim {{ $company->name }}</h1>
    <p class="subtitle">Verify that you represent this company to manage its profile, respond to reviews, and post jobs.</p>

    <div class="co-header">
        @php $clrs=['#007a33','#ce1126','#0056b3','#7b2d8b','#c0392b','#16a085']; $clr=$clrs[crc32($company->slug??'')%count($clrs)]; $ini=strtoupper(substr($company->trade_name?:$company->name,0,2)); @endphp
        <div class="co-logo" style="background:{{ $clr }}">{{ $ini }}</div>
        <div>
            <div class="co-name">{{ $company->name }}</div>
            <div class="co-meta">{{ $company->city_name ?? $company->region_name }} · {{ ucfirst(str_replace('_',' ',$company->verification_status??'')) }}</div>
        </div>
    </div>

    @if($existing)
    <div class="existing-claim">
        <i data-lucide="alert-triangle" class="lic"></i> You have already submitted a claim for this company on {{ date('d M Y', strtotime($existing->created_at)) }}. Status: <strong>{{ ucfirst($existing->status ?? 'pending') }}</strong>
        @if($existing->admin_notes_en)<br>Note from our team: {{ $existing->admin_notes_en }}@endif
    </div>
    @else

    <div class="card">
        <h2>How it works</h2>
        <ol class="steps">
            <li>Submit this form with your business documentation</li>
            <li>Our team verifies your claim within 2–5 business days</li>
            <li>You receive a confirmation email when approved</li>
            <li>You can then edit the company profile, post jobs, and manage reviews</li>
        </ol>
    </div>

    <div class="card">
        <h2>Your Details</h2>
        <form method="POST" action="/companies/{{ $company->slug }}/claim">
            @csrf
            <div class="form-group">
                <label>Your Role / Job Title <span style="color:var(--red)">*</span></label>
                <input type="text" name="claimant_role" required placeholder="e.g. CEO, Director of Operations, Head of HR">
            </div>
            <div class="form-group">
                <label>Work Email (must match company domain) <span style="color:var(--red)">*</span></label>
                <input type="email" name="claimant_email" required placeholder="yourname@{{ Str::afterLast($company->website??'company.cm','/') }}">
            </div>
            <div class="form-group">
                <label>Supporting Documentation URL (optional)</label>
                <input type="url" name="document_url" placeholder="Link to business registration or ID doc">
            </div>
            <div class="form-group">
                <label>Additional Information</label>
                <textarea name="notes" rows="3" placeholder="Any other details that would help us verify your claim…"></textarea>
            </div>
            <p style="font-size:.75rem;color:var(--muted);margin-bottom:.8rem;">By submitting this claim you confirm that you are authorised to represent {{ $company->name }} and that the information provided is accurate. False claims may result in account suspension.</p>
            <button type="submit" class="submit-btn">Submit Claim →</button>
        </form>
    </div>

    @endif
    @endif
</div>
@include('partials.footer')
</body>
</html>
