<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Export Hub — Galerie virtuelle de l'artisanat du Cameroun</title>
<meta name="description" content="Export readiness tools, customs guides, trade agreement information, and HS code lookup for Cameroonian exporters.">
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:1100px;margin:0 auto;padding:1.5rem;}
.hero{background:linear-gradient(135deg,#0c2340,#1e3a5f);border-radius:var(--radius);padding:2.5rem 2rem;color:#fff;margin-bottom:2rem;position:relative;overflow:hidden;}
.hero::after{content:'';position:absolute;right:-30px;bottom:-30px;width:260px;height:260px;border-radius:50%;background:rgba(252,209,22,.06);}
.hero-title{font-size:1.8rem;font-weight:900;margin-bottom:.5rem;}
.hero-sub{color:#93c5fd;font-size:.9rem;max-width:560px;}
.h-stats{display:flex;gap:2rem;margin-top:1.5rem;flex-wrap:wrap;}
.h-stat-val{font-size:1.4rem;font-weight:800;color:var(--yellow);}
.h-stat-lbl{font-size:.72rem;color:#93c5fd;}
.readiness-banner{background:linear-gradient(135deg,rgba(0,122,51,.1),rgba(0,122,51,.05));border:1px solid rgba(0,122,51,.2);border-radius:var(--radius);padding:1.5rem;margin-bottom:1.5rem;display:flex;gap:1.5rem;align-items:center;flex-wrap:wrap;}
.readiness-text{flex:1;min-width:200px;}
.readiness-title{font-weight:800;font-size:1rem;color:var(--text);margin-bottom:.3rem;}
.readiness-desc{font-size:.84rem;color:var(--muted);line-height:1.5;}
.readiness-btn{padding:.6rem 1.3rem;background:var(--green);color:#fff;border-radius:8px;font-weight:700;font-size:.88rem;text-decoration:none;white-space:nowrap;display:inline-block;}
.readiness-btn:hover{background:#00962e;}
.cat-tabs{display:flex;gap:.4rem;flex-wrap:wrap;margin-bottom:1.2rem;}
.cat-tab{padding:.32rem .85rem;border-radius:99px;font-size:.78rem;font-weight:600;cursor:pointer;border:1px solid var(--border);background:#fff;color:var(--muted);text-decoration:none;transition:all .15s;}
.cat-tab.active,.cat-tab:hover{background:var(--dark);color:#fff;border-color:var(--dark);}
.section-title{font-size:1.05rem;font-weight:800;color:var(--text);margin:1.5rem 0 .9rem;}
.feat-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:.8rem;margin-bottom:2rem;}
.feat-card{background:#fff;border-radius:var(--radius);padding:1.2rem;border:1px solid var(--border);box-shadow:var(--shadow);text-align:center;}
.feat-icon{font-size:1.8rem;margin-bottom:.5rem;}
.feat-title{font-weight:700;font-size:.85rem;color:var(--text);margin-bottom:.3rem;}
.feat-desc{font-size:.75rem;color:var(--muted);line-height:1.4;}
.resource-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(310px,1fr));gap:1rem;}
.resource-card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);padding:1.3rem;border:1px solid var(--border);transition:box-shadow .2s;}
.resource-card:hover{box-shadow:0 6px 24px rgba(0,0,0,.12);}
.rc-cat{display:inline-block;padding:2px 9px;border-radius:99px;font-size:.67rem;font-weight:700;background:var(--light-bg);color:var(--muted);border:1px solid var(--border);margin-bottom:.5rem;}
.rc-title{font-weight:800;font-size:.92rem;color:var(--text);line-height:1.3;margin-bottom:.4rem;}
.rc-preview{font-size:.8rem;color:var(--muted);line-height:1.5;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
.rc-meta{display:flex;gap:.75rem;font-size:.72rem;color:var(--muted);margin-top:.6rem;}
.rc-footer{margin-top:.8rem;padding-top:.7rem;border-top:1px solid var(--border);}
.featured-badge{display:inline-block;background:var(--yellow);color:var(--dark);font-size:.64rem;font-weight:800;padding:1px 7px;border-radius:99px;margin-left:.4rem;}
.steps-flow{display:flex;gap:0;margin:1.5rem 0;overflow-x:auto;}
.step{display:flex;align-items:center;flex-direction:column;min-width:120px;text-align:center;}
.step-num{width:40px;height:40px;border-radius:50%;background:var(--green);color:#fff;font-weight:800;display:flex;align-items:center;justify-content:center;margin-bottom:.4rem;font-size:.9rem;}
.step-label{font-size:.72rem;font-weight:600;color:var(--text);}
.step-arrow{color:var(--muted);font-size:1.2rem;align-self:center;padding-bottom:1.2rem;flex-shrink:0;}
@media(max-width:640px){.feat-grid{grid-template-columns:1fr 1fr;}.resource-grid{grid-template-columns:1fr;}.steps-flow{flex-direction:column;}.step-arrow{transform:rotate(90deg);}}
</style>

@php
$authUser = session('auth_user');
$cat   = request('cat','');
$query = DB::table('export_resources')->where('is_published',1);
if($cat) $query->where('category',$cat);
$resources = $query->orderByRaw('is_featured DESC')->orderBy('title')->get();
$cats = DB::table('export_resources')->where('is_published',1)->select('category')->distinct()->orderBy('category')->pluck('category');
$catLabels = ['customs'=>'Customs','certification'=>'Certification','trade_agreements'=>'Trade Agreements','markets'=>'Markets','financing'=>'Financing','packaging'=>'Packaging','labelling'=>'Labelling','hs_codes'=>'HS Codes','shipping'=>'Shipping','insurance'=>'Insurance','other'=>'Other'];
$myCompanies = $authUser ? DB::table('company_users')
    ->join('companies','company_users.company_id','=','companies.id')
    ->where('company_users.user_id',$authUser['id'])
    ->where('company_users.status','approved')
    ->whereNull('companies.deleted_at')
    ->select('companies.id','companies.name')->get() : collect();
@endphp

<div class="page">
    <div class="hero">
        <div style="position:relative;z-index:1;">
            <div style="display:inline-block;background:rgba(252,209,22,.15);border:1px solid rgba(252,209,22,.3);color:var(--yellow);padding:3px 12px;border-radius:99px;font-size:.72rem;font-weight:700;margin-bottom:.7rem;"><i data-lucide="globe" class="lic"></i> EXPORT HUB</div>
            <div class="hero-title">Your Export Gateway</div>
            <div class="hero-sub">Everything Cameroonian businesses need to export: customs procedures, certification guides, trade agreement benefits, HS codes, and market intelligence.</div>
            <div class="h-stats">
                <div><div class="h-stat-val">{{ DB::table('export_resources')->where('is_published',1)->count() }}</div><div class="h-stat-lbl">Export Resources</div></div>
                <div><div class="h-stat-val">{{ DB::table('export_assessments')->count() }}</div><div class="h-stat-lbl">Assessments Taken</div></div>
                <div><div class="h-stat-val">50+</div><div class="h-stat-lbl">Destination Markets</div></div>
            </div>
        </div>
    </div>

    <div class="readiness-banner">
        <div style="font-size:2.5rem;"><i data-lucide="rocket" class="lic"></i></div>
        <div class="readiness-text">
            <div class="readiness-title">Are You Export Ready?</div>
            <div class="readiness-desc">Take our free 10-minute export readiness assessment and get a personalised action plan with specific steps to prepare your product for international markets.</div>
        </div>
        @if($authUser && $myCompanies->count() > 0)
            <a href="/export-hub/assessment" class="readiness-btn">Start Assessment →</a>
        @else
            <a href="/auth/login?redirect=/export-hub/assessment" class="readiness-btn">Sign In to Assess →</a>
        @endif
    </div>

    <div class="section-title">Export Process Overview</div>
    <div class="steps-flow">
        @foreach(['Company Registration','Product Classification','Certifications','Customs Docs','Pre-Shipment Inspection','Port Clearance','Shipping','Destination Customs','Payment'] as $i => $step)
        <div class="step">
            <div class="step-num">{{ $i+1 }}</div>
            <div class="step-label">{{ $step }}</div>
        </div>
        @if($i < 8)<div class="step-arrow">→</div>@endif
        @endforeach
    </div>

    <div class="section-title">Key Export Services</div>
    <div class="feat-grid">
        <div class="feat-card"><div class="feat-icon"><i data-lucide="clipboard-list" class="lic"></i></div><div class="feat-title">Certificate of Origin</div><div class="feat-desc">Step-by-step guide to obtaining CoO from CCIMA</div></div>
        <div class="feat-card"><div class="feat-icon"><i data-lucide="hash" class="lic"></i></div><div class="feat-title">HS Code Lookup</div><div class="feat-desc">Find the right tariff code for your product</div></div>
        <div class="feat-card"><div class="feat-icon"><i data-lucide="handshake" class="lic"></i></div><div class="feat-title">Trade Agreements</div><div class="feat-desc">EPA, CEMAC, AfCFTA preferential rates</div></div>
        <div class="feat-card"><div class="feat-icon"><i data-lucide="heart-pulse" class="lic"></i></div><div class="feat-title">Phytosanitary</div><div class="feat-desc">Agriculture product certification requirements</div></div>
        <div class="feat-card"><div class="feat-icon"><i data-lucide="ship" class="lic"></i></div><div class="feat-title">Port of Douala</div><div class="feat-desc">Shipping, booking, and freight guides</div></div>
        <div class="feat-card"><div class="feat-icon"><i data-lucide="banknote" class="lic"></i></div><div class="feat-title">Export Finance</div><div class="feat-desc">Pre-export credit and trade finance options</div></div>
        <div class="feat-card"><div class="feat-icon"><i data-lucide="search" class="lic"></i></div><div class="feat-title">BIVAC Inspection</div><div class="feat-desc">Pre-shipment inspection requirements</div></div>
        <div class="feat-card"><div class="feat-icon"><i data-lucide="globe" class="lic"></i></div><div class="feat-title">Market Intelligence</div><div class="feat-desc">Destination market profiles and entry guides</div></div>
    </div>

    <div class="section-title">Export Resources</div>
    <div class="cat-tabs">
        <a href="/export-hub" class="cat-tab {{ !$cat?'active':'' }}">All</a>
        @foreach($cats as $c)<a href="/export-hub?cat={{ $c }}" class="cat-tab {{ $cat===$c?'active':'' }}">{{ $catLabels[$c]??ucfirst(str_replace('_',' ',$c)) }}</a>@endforeach
    </div>

    @if($resources->isEmpty())
        <div style="text-align:center;padding:3rem;color:var(--muted);">No resources found. <a href="/export-hub" style="color:var(--green);">Clear filters →</a></div>
    @else
        <div class="resource-grid">
        @foreach($resources as $r)
            <div class="resource-card">
                <div>
                    <span class="rc-cat">{{ $catLabels[$r->category]??ucfirst(str_replace('_',' ',$r->category)) }}</span>
                    @if($r->is_featured)<span class="featured-badge">Featured</span>@endif
                </div>
                <div class="rc-title"><a href="/export-hub/{{ $r->slug }}" style="color:var(--text);">{{ $r->title }}</a></div>
                <div class="rc-preview">{{ $r->body }}</div>
                <div class="rc-meta">
                    <span><i data-lucide="eye" class="lic"></i> {{ number_format($r->view_count) }} reads</span>
                    @if($r->country)<span><i data-lucide="globe" class="lic"></i> {{ $r->country }}</span>@endif
                </div>
                <div class="rc-footer">
                    <a href="/export-hub/{{ $r->slug }}" style="color:var(--green);font-size:.8rem;font-weight:700;">Read Guide →</a>
                </div>
            </div>
        @endforeach
        </div>
    @endif

    <div style="background:var(--light-bg);border-radius:var(--radius);padding:1.8rem;margin-top:2rem;text-align:center;">
        <div style="font-weight:800;font-size:1.05rem;color:var(--text);margin-bottom:.4rem;"><i data-lucide="phone" class="lic"></i> Need personalised export advice?</div>
        <div style="font-size:.85rem;color:var(--muted);margin-bottom:1rem;">Our export specialists can guide you through the entire process — from product classification to your first shipment.</div>
        <a href="/support" style="display:inline-block;padding:.55rem 1.3rem;background:var(--dark);color:#fff;border-radius:8px;font-weight:700;font-size:.88rem;">Contact Export Specialist →</a>
    </div>
</div>
@include('partials.footer')
</body>
</html>
