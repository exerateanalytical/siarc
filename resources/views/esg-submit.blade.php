<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Submit ESG Report — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:780px;margin:0 auto;padding:1.5rem;}
.back{font-size:.82rem;color:var(--muted);display:inline-flex;gap:.3rem;margin-bottom:1rem;}
.back:hover{color:var(--green);}
.form-card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);padding:2rem;border:1px solid var(--border);}
.form-title{font-size:1.2rem;font-weight:900;color:var(--text);margin-bottom:.3rem;}
.form-subtitle{font-size:.85rem;color:var(--muted);margin-bottom:1.8rem;}
.section-hd{font-weight:800;font-size:.9rem;color:var(--text);padding:.5rem 0;border-bottom:2px solid var(--border);margin:1.5rem 0 .9rem;}
.section-e{border-color:#22c55e;}
.section-s{border-color:#3b82f6;}
.section-g{border-color:#a855f7;}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:.75rem;}
.form-group{margin-bottom:.85rem;}
.form-label{display:block;font-size:.8rem;font-weight:600;margin-bottom:.3rem;color:var(--text);}
.form-hint{font-size:.72rem;color:var(--muted);margin-bottom:.3rem;}
.form-control{width:100%;padding:.5rem .75rem;border:1px solid var(--border);border-radius:7px;font-size:.85rem;outline:none;font-family:inherit;color:var(--text);box-sizing:border-box;}
.form-control:focus{border-color:var(--green);}
textarea.form-control{resize:vertical;min-height:70px;}
.check-group{display:flex;flex-direction:column;gap:.5rem;}
.check-item{display:flex;align-items:center;gap:.5rem;font-size:.85rem;font-weight:600;}
.check-item input{width:auto;cursor:pointer;}
.btn-submit{padding:.7rem 2rem;background:var(--green);color:#fff;border:none;border-radius:8px;font-size:.9rem;font-weight:700;cursor:pointer;margin-top:.5rem;}
.btn-submit:hover{background:#00962e;}
@media(max-width:560px){.form-row{grid-template-columns:1fr;}}
</style>

@php
$authUser = session('auth_user');
if(!$authUser) { header('Location:/auth/login?redirect=/esg/submit'); exit; }
$myCompanies = DB::table('company_users')
    ->join('companies','company_users.company_id','=','companies.id')
    ->where('company_users.user_id',$authUser['id'])
    ->where('company_users.status','approved')
    ->whereNull('companies.deleted_at')
    ->select('companies.id','companies.name')->get();
@endphp

<div class="page">
    <a class="back" href="/esg">← ESG Leaderboard</a>
    <div class="form-card">
        <div class="form-title"><i data-lucide="bar-chart-3" class="lic"></i> Submit ESG Report</div>
        <div class="form-subtitle">Complete your company's Environmental, Social & Governance data for {{ date('Y') - 1 }}. All fields are optional — share what you have.</div>

        <form method="POST" action="/esg/submit">
            @csrf
            <div class="form-group">
                <label class="form-label">Reporting Company</label>
                <select class="form-control" name="company_id" required>
                    @foreach($myCompanies as $mc)<option value="{{ $mc->id }}">{{ $mc->name }}</option>@endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Reporting Year</label>
                <select class="form-control" name="year" required>
                    @for($y=date('Y')-1;$y>=date('Y')-5;$y--)<option value="{{ $y }}">{{ $y }}</option>@endfor
                </select>
            </div>

            <div class="section-hd section-e"><i data-lucide="globe" class="lic"></i> Environmental</div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">CO₂ Emissions (tonnes)</label>
                    <div class="form-hint">Total greenhouse gas emissions for the year</div>
                    <input type="number" step="0.01" class="form-control" name="co2_tonnes" placeholder="e.g. 125.5">
                </div>
                <div class="form-group">
                    <label class="form-label">Energy Consumption (kWh)</label>
                    <input type="number" class="form-control" name="energy_kwh" placeholder="e.g. 450000">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Renewable Energy (%)</label>
                    <input type="number" step="0.1" min="0" max="100" class="form-control" name="renewable_energy_pct" placeholder="e.g. 30">
                </div>
                <div class="form-group">
                    <label class="form-label">Water Usage (m³)</label>
                    <input type="number" class="form-control" name="water_m3" placeholder="e.g. 5000">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Total Waste (tonnes)</label>
                    <input type="number" step="0.01" class="form-control" name="waste_tonnes" placeholder="e.g. 25">
                </div>
                <div class="form-group">
                    <label class="form-label">Waste Recycled (%)</label>
                    <input type="number" step="0.1" min="0" max="100" class="form-control" name="recycled_pct" placeholder="e.g. 45">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Environmental Initiatives</label>
                <textarea class="form-control" name="environmental_initiatives" placeholder="Describe green initiatives: solar panels, tree planting, biodegradable packaging, etc."></textarea>
            </div>

            <div class="section-hd section-s"><i data-lucide="users" class="lic"></i> Social</div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Total Employees</label>
                    <input type="number" class="form-control" name="total_employees" placeholder="e.g. 120">
                </div>
                <div class="form-group">
                    <label class="form-label">Female Employees</label>
                    <input type="number" class="form-control" name="female_employees" placeholder="e.g. 45">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Local Employees (%)</label>
                    <input type="number" min="0" max="100" class="form-control" name="local_employees_pct" placeholder="e.g. 85">
                </div>
                <div class="form-group">
                    <label class="form-label">Training Hours / Employee</label>
                    <input type="number" class="form-control" name="training_hours_per_employee" placeholder="e.g. 24">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Safety Incidents</label>
                    <input type="number" min="0" class="form-control" name="safety_incidents" placeholder="e.g. 0">
                </div>
                <div class="form-group" style="padding-top:1.5rem;">
                    <div class="check-item"><input type="checkbox" name="has_health_insurance" value="1" id="hhi"><label for="hhi">Provides health insurance</label></div>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Community Initiatives</label>
                <textarea class="form-control" name="community_initiatives" placeholder="Scholarships, health drives, local sourcing policies, community development, etc."></textarea>
            </div>

            <div class="section-hd section-g"><i data-lucide="landmark" class="lic"></i> Governance</div>
            <div class="check-group">
                <div class="check-item"><input type="checkbox" name="has_ethics_policy" value="1" id="hep"><label for="hep">We have a formal ethics and code of conduct policy</label></div>
                <div class="check-item"><input type="checkbox" name="has_whistleblower_policy" value="1" id="hwp"><label for="hwp">We have a whistleblower protection mechanism</label></div>
                <div class="check-item"><input type="checkbox" name="has_board_diversity" value="1" id="hbd"><label for="hbd">Our board/management has gender or background diversity</label></div>
                <div class="check-item"><input type="checkbox" name="anti_corruption_training" value="1" id="act"><label for="act">We conduct regular anti-corruption training</label></div>
            </div>
            <div class="form-group" style="margin-top:.9rem;">
                <label class="form-label">Governance Notes</label>
                <textarea class="form-control" name="governance_notes" placeholder="Any additional governance information you'd like to share…"></textarea>
            </div>

            <button type="submit" class="btn-submit">Submit ESG Report →</button>
        </form>
    </div>
</div>
@include('partials.footer')
</body>
</html>
