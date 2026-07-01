<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Export Readiness Assessment — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:740px;margin:0 auto;padding:1.5rem;}
.back{font-size:.82rem;color:var(--muted);display:inline-flex;gap:.3rem;margin-bottom:1rem;}
.form-card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);padding:2rem;border:1px solid var(--border);}
.progress-bar{height:6px;background:var(--light-bg);border-radius:3px;margin-bottom:1.5rem;}
.progress-fill{height:6px;background:var(--green);border-radius:3px;width:0%;}
.form-title{font-size:1.2rem;font-weight:900;margin-bottom:.3rem;}
.form-sub{font-size:.85rem;color:var(--muted);margin-bottom:1.5rem;}
.q-group{margin-bottom:1.2rem;padding:1rem;background:var(--light-bg);border-radius:8px;border-left:3px solid var(--green);}
.q-label{font-weight:700;font-size:.9rem;color:var(--text);margin-bottom:.6rem;}
.q-options{display:flex;gap:.5rem;flex-wrap:wrap;}
.q-option{display:flex;align-items:center;gap:.4rem;padding:.35rem .75rem;background:#fff;border:1px solid var(--border);border-radius:7px;cursor:pointer;font-size:.83rem;font-weight:600;transition:all .15s;}
.q-option:hover{border-color:var(--green);}
.q-option input{display:none;}
.q-option.selected{background:var(--green);color:#fff;border-color:var(--green);}
.form-group{margin-bottom:.9rem;}
.form-label{display:block;font-size:.8rem;font-weight:600;margin-bottom:.3rem;}
.form-control{width:100%;padding:.5rem .75rem;border:1px solid var(--border);border-radius:7px;font-size:.85rem;outline:none;font-family:inherit;box-sizing:border-box;}
.form-control:focus{border-color:var(--green);}
.btn-submit{padding:.7rem 2rem;background:var(--green);color:#fff;border:none;border-radius:8px;font-size:.9rem;font-weight:700;cursor:pointer;margin-top:.5rem;}
</style>

<div class="page">
    <a class="back" href="/export-hub">← Export Hub</a>
    <div class="form-card">
        <div class="form-title"><i data-lucide="rocket" class="lic"></i> Export Readiness Assessment</div>
        <div class="form-sub">Answer 11 questions about your export preparedness. Takes about 5 minutes. You'll receive a readiness score and personalised action plan.</div>

        <form method="POST" action="/export-hub/assessment">
            @csrf
            <div class="form-group">
                <label class="form-label">Your Company</label>
                <select class="form-control" name="company_id" required>
                    @foreach($myCompanies as $mc)<option value="{{ $mc->id }}">{{ $mc->name }}</option>@endforeach
                </select>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1rem;">
                <div class="form-group">
                    <label class="form-label">Product/Service to Export</label>
                    <input type="text" class="form-control" name="product_name" required placeholder="e.g. Cocoa beans, Timber, Software">
                </div>
                <div class="form-group">
                    <label class="form-label">Target Market Country</label>
                    <input type="text" class="form-control" name="target_market" placeholder="e.g. France, UK, China, UAE">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">HS Code (if known)</label>
                <input type="text" class="form-control" name="hs_code" placeholder="e.g. 1801.00 for cocoa beans">
            </div>

            @php
            $questions = [
                ['registered','Is your company officially registered and has a NIU/RCCM?'],
                ['has_product','Do you have a market-ready product or service that meets quality standards?'],
                ['has_certifications','Do you have the required certifications for your product (phytosanitary, ISO, halal, organic, etc.)?'],
                ['has_export_docs','Do you understand the export documentation process (CoO, invoice, packing list, bill of lading)?'],
                ['has_packaging','Is your product packaging suitable for international shipping and meets destination country labelling requirements?'],
                ['has_insurance','Do you have or understand how to obtain export cargo insurance?'],
                ['has_bank_account','Do you have a company bank account capable of receiving international wire transfers (USD/EUR)?'],
                ['knows_hs_code','Do you know the correct HS tariff code for your product in the destination market?'],
                ['knows_target_market','Have you researched demand, competition, and pricing in your target market?'],
                ['has_export_partner','Do you have an export agent, freight forwarder, or logistics partner?'],
                ['has_logistics','Have you arranged inland transport from your facility to the Port of Douala?'],
            ];
            @endphp

            @foreach($questions as $i => [$field,$label])
            <div class="q-group">
                <div class="q-label">{{ $i+1 }}. {{ $label }}</div>
                <div class="q-options">
                    <label class="q-option" id="opt_{{ $field }}_yes">
                        <input type="radio" name="{{ $field }}" value="1" onchange="toggleOption('{{ $field }}','yes')"> <i data-lucide="check" class="lic"></i> Yes
                    </label>
                    <label class="q-option" id="opt_{{ $field }}_no">
                        <input type="radio" name="{{ $field }}" value="" onchange="toggleOption('{{ $field }}','no')"> <i data-lucide="x" class="lic"></i> Not yet
                    </label>
                    <label class="q-option" id="opt_{{ $field }}_partial">
                        <input type="radio" name="{{ $field }}" value="partial" onchange="toggleOption('{{ $field }}','partial')"> ~ Partially
                    </label>
                </div>
            </div>
            @endforeach

            <button type="submit" class="btn-submit">Get My Readiness Score →</button>
        </form>
    </div>
</div>
<script>
function toggleOption(field, choice) {
    ['yes','no','partial'].forEach(c => {
        document.getElementById('opt_'+field+'_'+c)?.classList.remove('selected');
    });
    document.getElementById('opt_'+field+'_'+choice)?.classList.add('selected');
}
</script>
@include('partials.footer')
</body>
</html>
