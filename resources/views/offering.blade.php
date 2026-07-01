<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>{{ $offering->title_en }} — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@php $activeTab = 'offerings'; @endphp
@include('partials.nav')
<style>
.breadcrumb{padding:.75rem 2rem;font-size:.8rem;color:var(--muted);border-bottom:1px solid var(--border);background:var(--white);}
.breadcrumb a{color:var(--green);}
.hero{background:var(--dark);padding:2rem 2rem 1.5rem;color:#fff;}
.hero-inner{max-width:1100px;margin:0 auto;display:grid;grid-template-columns:1fr auto;gap:1.5rem;align-items:start;}
.offering-logo{width:64px;height:64px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.4rem;font-weight:800;color:#fff;flex-shrink:0;text-transform:uppercase;}
.offering-head{display:flex;gap:1rem;align-items:flex-start;margin-bottom:1rem;}
.offering-meta{flex:1;}
.offering-title-lg{font-size:1.5rem;font-weight:800;line-height:1.25;margin-bottom:.35rem;}
.offering-company-link{color:#7ecfff;font-size:.9rem;}
.hero-badges{display:flex;gap:.5rem;flex-wrap:wrap;margin-top:.6rem;}
.badge{font-size:.73rem;font-weight:700;padding:3px 10px;border-radius:99px;text-transform:uppercase;}
.b-open{background:#d4edda;color:#007a33;}.b-cmf_approved{background:#cce5ff;color:#0056b3;}
.b-pending_cmf{background:#fff3cd;color:#856404;}.b-closed,.b-completed{background:#ced4da;color:#495057;}
.b-type{background:rgba(255,255,255,.15);color:#fff;}
.b-vs{background:rgba(255,255,255,.1);color:#cce5ff;}
.hero-side{text-align:right;}
.target-big{font-size:2rem;font-weight:800;color:#fff;line-height:1;}
.target-lbl{font-size:.75rem;color:#8899aa;margin-top:2px;}
.main{max-width:1100px;margin:0 auto;padding:1.5rem;display:grid;grid-template-columns:1fr 300px;gap:1.5rem;align-items:start;}
.card{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden;margin-bottom:1rem;}
.card-title{padding:.85rem 1.1rem;font-weight:700;font-size:.9rem;border-bottom:1px solid var(--border);background:var(--light-bg);}
.card-body{padding:1.1rem;}
.progress-section{padding:1.1rem;}
.progress-label{display:flex;justify-content:space-between;font-size:.82rem;color:var(--muted);margin-bottom:6px;font-weight:600;}
.progress-bar{height:10px;background:#eee;border-radius:99px;overflow:hidden;margin-bottom:.4rem;}
.progress-fill{height:100%;border-radius:99px;}
.progress-caption{display:flex;justify-content:space-between;font-size:.75rem;color:var(--muted);}
.metrics-grid{display:grid;grid-template-columns:1fr 1fr;gap:.75rem;}
.metric{padding:.8rem;background:var(--light-bg);border-radius:8px;text-align:center;}
.metric-val{font-size:1.05rem;font-weight:700;color:var(--text);}
.metric-lbl{font-size:.7rem;color:var(--muted);margin-top:2px;}
.info-row{display:flex;justify-content:space-between;padding:.55rem 0;border-bottom:1px solid var(--border);font-size:.85rem;}
.info-row:last-child{border-bottom:none;}
.info-lbl{color:var(--muted);}
.info-val{font-weight:600;text-align:right;}
.desc-text{font-size:.87rem;line-height:1.7;color:var(--text);}
.cta-card{background:var(--dark);border-radius:var(--radius);padding:1.3rem;text-align:center;margin-bottom:1rem;}
.cta-amount{font-size:1.5rem;font-weight:800;color:#fff;margin-bottom:.2rem;}
.cta-sub{font-size:.77rem;color:#8899aa;margin-bottom:1rem;}
.btn-invest{display:block;width:100%;padding:.85rem;background:var(--green);color:#fff;border:none;border-radius:9px;font-weight:700;font-size:.95rem;cursor:pointer;text-align:center;transition:background .15s;}
.btn-invest:hover{background:#00962e;}
.btn-secondary{display:block;width:100%;padding:.65rem;background:rgba(255,255,255,.1);color:#fff;border:none;border-radius:9px;font-weight:600;font-size:.84rem;cursor:pointer;margin-top:.6rem;text-align:center;transition:background .15s;}
.btn-secondary:hover{background:rgba(255,255,255,.18);}
.sidebar-info{padding:.9rem 1.1rem;}
.s-row{display:flex;justify-content:space-between;padding:.4rem 0;border-bottom:1px solid var(--border);font-size:.82rem;}
.s-row:last-child{border-bottom:none;}
.s-lbl{color:var(--muted);}
.s-val{font-weight:600;}
.cmf-banner{border-radius:8px;padding:.75rem 1rem;font-size:.8rem;display:flex;gap:.6rem;align-items:flex-start;margin-bottom:1rem;}
.cmf-approved{background:#d4edda;color:#155724;}
.cmf-pending{background:#fff3cd;color:#856404;}
.cmf-open{background:#d1ecf1;color:#0c5460;}
.back-link{display:inline-flex;align-items:center;gap:.4rem;color:var(--green);font-size:.85rem;margin-bottom:1rem;}
@media(max-width:750px){.main{grid-template-columns:1fr;}.hero-inner{grid-template-columns:1fr;}.hero-side{text-align:left;}.metrics-grid{grid-template-columns:1fr 1fr;}}
</style>

@php
    $ini = strtoupper(substr($offering->company_trade ?: $offering->company_name, 0, 2));
    $clrs = ['#007a33','#ce1126','#0056b3','#7b2d8b','#c0392b','#16a085','#d35400','#2c3e50'];
    $clr = $clrs[crc32($offering->company_id) % count($clrs)];
    $pct = $offering->target_amount > 0 ? round($offering->amount_raised / $offering->target_amount * 100) : 0;
    $barColor = match($offering->status){'open'=>'#007a33','cmf_approved'=>'#0056b3','closed'=>'#95a5a6','completed'=>'#27ae60',default=>'#e67e22'};
    $stLabel = match($offering->status){
        'open'=>'Open','cmf_approved'=>'CMF Approved','pending_cmf'=>'Pending CMF',
        'closed'=>'Closed','completed'=>'Completed','paused'=>'Paused','draft'=>'Draft','cancelled'=>'Cancelled',default=>ucfirst($offering->status)};
    $instLabel = match($offering->instrument_type){
        'ordinary_shares'=>'Equity / Shares','bonds'=>'Bonds',
        'preference_shares'=>'Preference Shares','convertible_notes'=>'Convertible Notes',default=>$offering->instrument_type};
    $isOpen = $offering->status === 'open';
    $pledgeCount = $pledgeCount ?? 0;
@endphp

<div class="breadcrumb">
    <a href="/">Home</a> / <a href="/offerings">Offerings</a> / {{ $offering->title_en }}
</div>

<div class="hero">
    <div class="hero-inner">
        <div>
            <div class="offering-head">
                <div class="offering-logo" style="background:{{ $clr }}">{{ $ini }}</div>
                <div class="offering-meta">
                    <div class="offering-title-lg">{{ $offering->title_en }}</div>
                    <a class="offering-company-link" href="/companies/{{ $offering->company_slug }}">{{ $offering->company_name }} →</a>
                    <div class="hero-badges">
                        <span class="badge b-{{ str_replace('_','-',$offering->status) }}">{{ $stLabel }}</span>
                        <span class="badge b-type">{{ $instLabel }}</span>
                        @if($offering->currency)<span class="badge b-vs">{{ $offering->currency }}</span>@endif
                    </div>
                </div>
            </div>
            <p style="color:#aab;font-size:.88rem;line-height:1.6;max-width:680px;">{{ $offering->summary_en }}</p>
        </div>
        <div class="hero-side">
            <div class="target-big">{{ number_format($offering->target_amount/1000000,0) }}M</div>
            <div class="target-lbl">XAF Target</div>
            @if($offering->close_date)
                <div style="margin-top:.8rem;font-size:.78rem;color:#8899aa;">Closes {{ date('d M Y',strtotime($offering->close_date)) }}</div>
            @endif
        </div>
    </div>
</div>

<div class="main">
    <div>
        @if($offering->status === 'cmf_approved')
            <div class="cmf-banner cmf-approved"><i data-lucide="check-circle-2" class="lic"></i> <div><strong>CMF Approved</strong> — This offering has received approval from the Cameroon Markets Regulator (CMF) and is ready to open.</div></div>
        @elseif($offering->status === 'pending_cmf')
            <div class="cmf-banner cmf-pending">⏳ <div><strong>Awaiting CMF Review</strong> — This offering has been submitted to the CMF for review. Investing will become available once approved.</div></div>
        @elseif($offering->status === 'open')
            <div class="cmf-banner cmf-open">ℹ <div><strong>Offering Open</strong> — This offering is currently accepting investments through the Galerie virtuelle de l'artisanat du Cameroun platform.</div></div>
        @endif

        @if($offering->target_amount > 0)
        <div class="card">
            <div class="card-title">Fundraising Progress</div>
            <div class="progress-section">
                <div class="progress-label">
                    <span>{{ number_format($offering->amount_raised/1000000,1) }}M XAF raised</span>
                    <span style="color:{{ $barColor }};font-weight:800;">{{ $pct }}%</span>
                </div>
                <div class="progress-bar"><div class="progress-fill" style="width:{{ min($pct,100) }}%;background:{{ $barColor }}"></div></div>
                <div class="progress-caption">
                    <span>Minimum: {{ number_format($offering->minimum_amount/1000000,0) }}M XAF</span>
                    <span>Target: {{ number_format($offering->target_amount/1000000,0) }}M XAF</span>
                    <span>Max: {{ number_format($offering->maximum_amount/1000000,0) }}M XAF</span>
                </div>
            </div>
            <div style="padding:0 1.1rem 1.1rem;">
                <div class="metrics-grid">
                    <div class="metric">
                        <div class="metric-val">{{ number_format($offering->shares_sold) }}</div>
                        <div class="metric-lbl">Units Sold</div>
                    </div>
                    <div class="metric">
                        <div class="metric-val">{{ number_format($offering->total_shares - $offering->shares_sold) }}</div>
                        <div class="metric-lbl">Units Available</div>
                    </div>
                    <div class="metric">
                        <div class="metric-val">{{ number_format($offering->share_price) }} XAF</div>
                        <div class="metric-lbl">Unit Price</div>
                    </div>
                    <div class="metric">
                        <div class="metric-val">{{ number_format($offering->min_investment) }} XAF</div>
                        <div class="metric-lbl">Minimum Investment</div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="card">
            <div class="card-title">Offering Details</div>
            <div class="card-body">
                <div class="info-row"><span class="info-lbl">Instrument Type</span><span class="info-val">{{ $instLabel }}</span></div>
                <div class="info-row"><span class="info-lbl">Currency</span><span class="info-val">{{ $offering->currency }}</span></div>
                @if($offering->equity_offered)<div class="info-row"><span class="info-lbl">Equity Offered</span><span class="info-val">{{ $offering->equity_offered }}%</span></div>@endif
                <div class="info-row"><span class="info-lbl">Unit Price</span><span class="info-val">{{ number_format($offering->share_price) }} XAF</span></div>
                <div class="info-row"><span class="info-lbl">Total Units</span><span class="info-val">{{ number_format($offering->total_shares) }}</span></div>
                <div class="info-row"><span class="info-lbl">Minimum Investment</span><span class="info-val">{{ number_format($offering->min_investment) }} XAF</span></div>
                <div class="info-row"><span class="info-lbl">Maximum Investment</span><span class="info-val">{{ number_format($offering->max_investment) }} XAF</span></div>
                @if($offering->open_date)<div class="info-row"><span class="info-lbl">Opening Date</span><span class="info-val">{{ date('d M Y',strtotime($offering->open_date)) }}</span></div>@endif
                @if($offering->close_date)<div class="info-row"><span class="info-lbl">Closing Date</span><span class="info-val">{{ date('d M Y',strtotime($offering->close_date)) }}</span></div>@endif
                <div class="info-row"><span class="info-lbl">Platform Fee</span><span class="info-val">{{ $offering->platform_fee_pct }}%</span></div>
            </div>
        </div>

        @if($offering->summary_fr)
        <div class="card">
            <div class="card-title">Description (Français)</div>
            <div class="card-body"><p class="desc-text">{{ $offering->summary_fr }}</p></div>
        </div>
        @endif

        @if(isset($faqs) && $faqs->count() > 0)
        <div class="card">
            <div class="card-title">Frequently Asked Questions</div>
            <div class="card-body" id="faq-list">
                @foreach($faqs as $i => $faq)
                <div style="border-bottom:1px solid var(--border);padding:.7rem 0;">
                    <div style="display:flex;justify-content:space-between;cursor:pointer;font-weight:700;font-size:.88rem;" onclick="toggleFaq({{ $i }})">
                        <span>{{ $faq->question_en }}</span>
                        <span id="faq-icon-{{ $i }}" style="color:var(--green);flex-shrink:0;margin-left:.5rem;">+</span>
                    </div>
                    <div id="faq-ans-{{ $i }}" style="display:none;margin-top:.5rem;font-size:.83rem;color:var(--muted);line-height:1.6;">{{ $faq->answer_en }}</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if(isset($updates) && $updates->count() > 0)
        <div class="card">
            <div class="card-title">Offering Updates <span style="font-size:.75rem;font-weight:400;color:var(--muted);">({{ $updates->count() }})</span></div>
            <div class="card-body">
                @foreach($updates as $u)
                <div style="padding:.8rem 0;border-bottom:1px solid var(--border);">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.3rem;">
                        <span style="font-weight:700;font-size:.88rem;">{{ $u->title_en }}</span>
                        <span style="font-size:.72rem;color:var(--muted);">{{ $u->created_at ? date('d M Y', strtotime($u->created_at)) : '' }}</span>
                    </div>
                    <p style="font-size:.83rem;color:var(--muted);line-height:1.6;">{{ $u->body_en }}</p>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <div>
        <div class="cta-card">
            <div class="cta-amount">{{ number_format($offering->min_investment) }} XAF</div>
            <div class="cta-sub">Minimum investment</div>
            @if($pledgeCount > 0)
                <div style="font-size:.75rem;color:#8899aa;margin-bottom:.5rem;">{{ $pledgeCount }} investor{{ $pledgeCount!=1?'s':'' }} have pledged</div>
            @endif
            @if($isOpen)
                @if(session('auth_user'))
                    <a class="btn-invest" href="/invest/{{ $offering->id }}">Invest Now</a>
                @else
                    <a class="btn-invest" href="/login?next=/invest/{{ $offering->id }}">Log in to Invest</a>
                    <a class="btn-secondary" href="/register">Create Free Account</a>
                @endif
            @else
                <div style="padding:.85rem;background:rgba(255,255,255,.06);border-radius:9px;color:#aab;font-size:.82rem;text-align:center;">
                    @if($offering->status === 'cmf_approved') Offering opens soon — register to be notified
                    @elseif($offering->status === 'pending_cmf') Awaiting CMF approval
                    @else This offering is {{ $stLabel }}
                    @endif
                </div>
                <a class="btn-secondary" href="/register">Register for Updates</a>
            @endif
        </div>

        <div class="card">
            <div class="card-title">Company</div>
            <div class="sidebar-info">
                <div class="s-row"><span class="s-lbl">Company</span><span class="s-val"><a href="/companies/{{ $offering->company_slug }}" style="color:var(--green)">{{ $offering->company_name }}</a></span></div>
                <div class="s-row"><span class="s-lbl">Region</span><span class="s-val">{{ $offering->region_name }}</span></div>
                @if($offering->city_name)<div class="s-row"><span class="s-lbl">City</span><span class="s-val">{{ $offering->city_name }}</span></div>@endif
                <div class="s-row"><span class="s-lbl">Verification</span><span class="s-val">{{ ucfirst($offering->company_vs) }}</span></div>
            </div>
        </div>

        <div class="card">
            <div class="card-title">Regulatory</div>
            <div class="sidebar-info">
                <div class="s-row"><span class="s-lbl">CMF Status</span><span class="s-val">{{ $stLabel }}</span></div>
                <div class="s-row"><span class="s-lbl">Regulator</span><span class="s-val">CMF Cameroon</span></div>
                <div style="font-size:.73rem;color:var(--muted);margin-top:.6rem;line-height:1.5;">
                    All offerings on Galerie virtuelle de l'artisanat du Cameroun are reviewed by the Commission des Marchés Financiers (CMF) before opening to the public.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleFaq(i){
    var a=document.getElementById('faq-ans-'+i);
    var ic=document.getElementById('faq-icon-'+i);
    if(a.style.display==='none'){a.style.display='block';ic.textContent='−';}
    else{a.style.display='none';ic.textContent='+';}
}
</script>
@include('partials.footer')
</body>
</html>
