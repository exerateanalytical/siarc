<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>{{ $seek->title }} — Investment — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:1000px;margin:0 auto;padding:1.5rem;}
.back{font-size:.82rem;color:var(--muted);display:inline-flex;gap:.3rem;margin-bottom:1rem;}
.back:hover{color:var(--green);}
.grid2{display:grid;grid-template-columns:1fr 300px;gap:1.5rem;}
.card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);margin-bottom:1rem;border:1px solid var(--border);}
.card-title{padding:.75rem 1.1rem;font-weight:700;font-size:.88rem;border-bottom:1px solid var(--border);background:var(--light-bg);}
.card-body{padding:1.2rem;}
.seek-title{font-size:1.4rem;font-weight:900;color:var(--text);margin-bottom:.5rem;}
.badge{display:inline-block;padding:3px 12px;border-radius:99px;font-size:.75rem;font-weight:700;background:var(--light-bg);color:var(--muted);border:1px solid var(--border);}
.seek-amount{font-size:1.5rem;font-weight:900;color:var(--green);margin:.7rem 0 .2rem;}
.seek-equity{font-size:.85rem;color:var(--muted);}
.info-grid{display:grid;grid-template-columns:1fr 1fr;gap:.6rem;margin:.9rem 0;}
.info-item{padding:.6rem .8rem;background:var(--light-bg);border-radius:8px;}
.info-lbl{font-size:.68rem;color:var(--muted);font-weight:600;margin-bottom:2px;}
.info-val{font-size:.88rem;font-weight:700;color:var(--text);}
.section-title{font-weight:700;font-size:.9rem;color:var(--text);margin:.9rem 0 .4rem;}
.desc-text{font-size:.87rem;color:var(--text);line-height:1.7;}
.express-box{background:#fff;border:2px solid var(--green);border-radius:var(--radius);padding:1.3rem;margin-bottom:1rem;}
.form-group{margin-bottom:.85rem;}
.form-label{display:block;font-size:.8rem;font-weight:600;margin-bottom:.3rem;}
.form-control{width:100%;padding:.5rem .75rem;border:1px solid var(--border);border-radius:7px;font-size:.85rem;outline:none;font-family:inherit;color:var(--text);box-sizing:border-box;}
.form-control:focus{border-color:var(--green);}
textarea.form-control{resize:vertical;min-height:90px;}
.btn-express{width:100%;padding:.6rem;background:var(--green);color:#fff;border:none;border-radius:8px;font-size:.9rem;font-weight:700;cursor:pointer;}
.side-row{display:flex;justify-content:space-between;padding:.4rem 0;border-bottom:1px solid var(--border);font-size:.83rem;}
.side-row:last-child{border-bottom:none;}
@media(max-width:700px){.grid2{grid-template-columns:1fr;}.info-grid{grid-template-columns:1fr;}}
</style>

@php
$typeLabels = ['equity'=>'Equity','debt'=>'Debt','grant'=>'Grant','convertible_note'=>'Convertible Note','revenue_sharing'=>'Revenue Sharing','joint_venture'=>'Joint Venture','angel'=>'Angel Round','seed'=>'Seed','series_a'=>'Series A','series_b'=>'Series B','ipo_prep'=>'IPO Prep','government_fund'=>'Gov. Fund','development_finance'=>'Dev. Finance'];
@endphp

<div class="page">
    <a class="back" href="/invest-hub">← Investment Marketplace</a>
    <div class="grid2">
        <div>
            <div class="card">
                <div class="card-body">
                    <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:.6rem;">
                        <span class="badge">{{ $typeLabels[$seek->type]??ucfirst(str_replace('_',' ',$seek->type)) }}</span>
                        <span class="badge">{{ ucfirst($seek->sector) }}</span>
                        <span class="badge" style="background:#d4edda;color:#166534;border-color:#b8dbc4;">{{ ucfirst($seek->status) }}</span>
                    </div>
                    <div class="seek-title">{{ $seek->title }}</div>
                    <div style="font-size:.82rem;color:var(--muted);margin-bottom:.7rem;">
                        <a href="/companies/{{ $company->slug }}" style="color:var(--green);font-weight:600;">{{ $company->name }}</a>
                    </div>
                    <div class="seek-amount">{{ number_format($seek->amount_sought/1000000,1) }}M {{ $seek->currency }}</div>
                    @if($seek->equity_offered)<div class="seek-equity">{{ $seek->equity_offered }}% equity offered</div>@endif
                    <div class="info-grid">
                        @if($seek->valuation)<div class="info-item"><div class="info-lbl">Valuation</div><div class="info-val">{{ number_format($seek->valuation/1000000,1) }}M XAF</div></div>@endif
                        @if($seek->revenue_last_year)<div class="info-item"><div class="info-lbl">Last Year Revenue</div><div class="info-val">{{ number_format($seek->revenue_last_year/1000000,1) }}M XAF</div></div>@endif
                        <div class="info-item"><div class="info-lbl">Expressions of Interest</div><div class="info-val">{{ $interests }}</div></div>
                        <div class="info-item"><div class="info-lbl">Views</div><div class="info-val">{{ number_format($seek->view_count+1) }}</div></div>
                    </div>
                    <div class="section-title">About this Opportunity</div>
                    <div class="desc-text">{{ $seek->description }}</div>
                    @if($seek->use_of_funds)
                    <div class="section-title">Use of Funds</div>
                    <div class="desc-text">{{ $seek->use_of_funds }}</div>
                    @endif
                    @if($seek->traction)
                    <div class="section-title">Traction & Key Metrics</div>
                    <div class="desc-text">{{ $seek->traction }}</div>
                    @endif
                    @if($seek->team_info)
                    <div class="section-title">Team</div>
                    <div class="desc-text">{{ $seek->team_info }}</div>
                    @endif
                    @if($seek->deadline)
                    <div style="margin-top:.9rem;font-size:.82rem;color:var(--muted);"><i data-lucide="calendar" class="lic"></i> Deadline: <strong>{{ date('d M Y',strtotime($seek->deadline)) }}</strong></div>
                    @endif
                </div>
            </div>

            @if($isOwner && $interestList->count() > 0)
            <div class="card">
                <div class="card-title">Expressions of Interest ({{ $interestList->count() }})</div>
                <div class="card-body">
                    @foreach($interestList as $it)
                    <div style="padding:.6rem 0;border-bottom:1px solid var(--border);">
                        <div style="display:flex;justify-content:space-between;align-items:center;gap:.5rem;">
                            <span style="font-weight:700;font-size:.85rem;">{{ trim(($it->first_name??'').' '.($it->last_name??'')) }}{{ $it->investor_company ? ' · '.$it->investor_company : '' }}</span>
                            @if($it->proposed_amount)<span style="font-weight:800;color:var(--green);font-size:.85rem;">{{ number_format($it->proposed_amount/1000000,1) }}M XAF</span>@endif
                        </div>
                        @if($it->message)<div style="font-size:.8rem;color:var(--muted);margin-top:.3rem;">{{ $it->message }}</div>@endif
                        @if($it->investor_user_id)<a href="/messages/{{ $it->investor_user_id }}" style="display:inline-block;margin-top:.3rem;font-size:.74rem;color:var(--green);font-weight:700;"><i data-lucide="message-circle" class="lic"></i> Message investor</a>@endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        <div>
            @if(!$isOwner && $seek->status === 'open')
                @if($alreadyExpressed)
                <div class="express-box" style="border-color:var(--muted);">
                    <div style="text-align:center;padding:.5rem;">
                        <div style="font-size:1.5rem;margin-bottom:.5rem;"><i data-lucide="check" class="lic"></i></div>
                        <div style="font-weight:700;color:var(--text);">Interest Expressed!</div>
                        <div style="font-size:.82rem;color:var(--muted);margin-top:.4rem;">The company has been notified and will review your interest.</div>
                    </div>
                </div>
                @elseif($authUser)
                <div class="express-box" id="express">
                    <div style="font-weight:800;font-size:.95rem;margin-bottom:.9rem;">Express Interest</div>
                    <form method="POST" action="/invest-hub/{{ $seek->id }}/express">
                        @csrf
                        @if($myCompanies->count() > 0)
                        <div class="form-group">
                            <label class="form-label">Investing as</label>
                            <select class="form-control" name="investor_company_id">
                                <option value="">Personal / Individual Investor</option>
                                @foreach($myCompanies as $mc)<option value="{{ $mc->id }}">{{ $mc->name }}</option>@endforeach
                            </select>
                        </div>
                        @endif
                        <div class="form-group">
                            <label class="form-label">Message</label>
                            <textarea class="form-control" name="message" required placeholder="Introduce yourself and explain your investment interest…"></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Proposed Amount (XAF, optional)</label>
                            <input type="number" class="form-control" name="proposed_amount" placeholder="e.g. 100000000">
                        </div>
                        <button type="submit" class="btn-express">Express Interest →</button>
                    </form>
                </div>
                @else
                <div class="express-box" style="border-color:var(--muted);">
                    <div style="font-size:.85rem;color:var(--text);margin-bottom:.75rem;">Sign in to express investment interest.</div>
                    <a href="/auth/login" style="display:block;text-align:center;background:var(--green);color:#fff;padding:.55rem;border-radius:7px;font-size:.85rem;font-weight:700;">Sign In →</a>
                </div>
                @endif
            @endif

            <div style="background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);padding:1.2rem;border:1px solid var(--border);">
                <div style="display:flex;gap:.6rem;align-items:center;margin-bottom:.75rem;">
                    <div style="width:40px;height:40px;border-radius:8px;background:linear-gradient(135deg,var(--dark),var(--mid));display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.9rem;color:var(--yellow);">{{ strtoupper(substr($company->name,0,2)) }}</div>
                    <div>
                        <div style="font-weight:800;font-size:.88rem;">{{ $company->name }}</div>
                        @if($company->verification_status==='verified')<div style="font-size:.68rem;background:#d4edda;color:#166534;padding:1px 7px;border-radius:99px;display:inline-block;font-weight:700;margin-top:1px;"><i data-lucide="check" class="lic"></i> Verified</div>@endif
                    </div>
                </div>
                @if($company->description_en)<div style="font-size:.8rem;color:var(--muted);margin-bottom:.75rem;line-height:1.5;">{{ Str::limit($company->description_en,100) }}</div>@endif
                <div style="margin-bottom:.5rem;"><div class="side-row"><span style="color:var(--muted)">Type</span><span style="font-weight:600;">{{ $typeLabels[$seek->type]??ucfirst($seek->type) }}</span></div><div class="side-row"><span style="color:var(--muted)">Sector</span><span style="font-weight:600;">{{ ucfirst($seek->sector) }}</span></div></div>
                <a href="/companies/{{ $company->slug }}" style="display:block;text-align:center;border:1px solid var(--border);color:var(--text);padding:.45rem;border-radius:7px;font-size:.82rem;font-weight:600;">View Company →</a>
            </div>
        </div>
    </div>
</div>
@include('partials.footer')
</body>
</html>
