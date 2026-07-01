<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>CollabCam — Business Collaboration Platform — Galerie virtuelle de l'artisanat du Cameroun</title>
<meta name="description" content="CollabCam connects Cameroonian businesses to collaborate on supply chains, joint ventures, contracts, and value chains.">
</head>
<body>
@include('partials.nav')
<style>
/* CollabCam global */
.cc-page{max-width:1100px;margin:0 auto;padding:1.5rem;}
/* Hero */
.cc-hero{background:linear-gradient(135deg,#0a1628 0%,#142240 50%,#0d1f3c 100%);border-radius:14px;padding:3.5rem 2.5rem;color:#fff;margin-bottom:2rem;position:relative;overflow:hidden;text-align:center;}
.cc-hero::before{content:'';position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);width:600px;height:600px;border-radius:50%;background:radial-gradient(circle,rgba(0,122,51,.18) 0%,transparent 70%);pointer-events:none;}
.cc-badge{display:inline-flex;align-items:center;gap:.4rem;background:rgba(252,209,22,.15);border:1px solid rgba(252,209,22,.4);color:var(--yellow);padding:.35rem 1rem;border-radius:99px;font-size:.75rem;font-weight:700;margin-bottom:1.2rem;letter-spacing:.03em;}
.cc-hero-title{font-size:2.5rem;font-weight:900;line-height:1.15;margin-bottom:.75rem;background:linear-gradient(135deg,#fff 40%,#fcd116);-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
.cc-hero-sub{font-size:1.05rem;color:#99aabb;max-width:600px;margin:0 auto 2rem;line-height:1.6;}
.cc-hero-actions{display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;}
.cc-btn-primary{padding:.75rem 2rem;background:var(--green);color:#fff;border-radius:9px;font-size:.95rem;font-weight:700;transition:background .15s;display:inline-block;}
.cc-btn-primary:hover{background:#00962e;}
.cc-btn-ghost{padding:.75rem 2rem;border:1.5px solid rgba(255,255,255,.25);color:#fff;border-radius:9px;font-size:.95rem;font-weight:600;display:inline-block;}
.cc-btn-ghost:hover{background:rgba(255,255,255,.08);}
.cc-stats-row{display:flex;gap:2rem;justify-content:center;margin-top:2.5rem;flex-wrap:wrap;}
.cc-stat{text-align:center;}
.cc-stat-val{font-size:2rem;font-weight:900;color:var(--yellow);}
.cc-stat-lbl{font-size:.78rem;color:#7788aa;margin-top:3px;}
/* Value chain section */
.section-title{font-size:1.3rem;font-weight:900;color:var(--text);margin-bottom:.4rem;}
.section-sub{font-size:.88rem;color:var(--muted);margin-bottom:1.8rem;}
.vc-container{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);padding:1.5rem;margin-bottom:2rem;overflow-x:auto;}
.vc-tabs{display:flex;gap:.5rem;margin-bottom:1.5rem;flex-wrap:wrap;}
.vc-tab{padding:.35rem .9rem;border-radius:6px;font-size:.78rem;font-weight:600;cursor:pointer;border:1px solid var(--border);background:#fff;color:var(--muted);transition:all .15s;}
.vc-tab.active{background:var(--dark);color:#fff;border-color:var(--dark);}
.vc-chain{display:flex;align-items:center;gap:0;overflow-x:auto;padding:.5rem 0;}
.vc-node{display:flex;flex-direction:column;align-items:center;text-align:center;min-width:110px;}
.vc-icon{width:60px;height:60px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.5rem;margin:0 auto .5rem;box-shadow:0 2px 8px rgba(0,0,0,.12);}
.vc-label{font-size:.72rem;font-weight:700;color:var(--text);line-height:1.3;}
.vc-sublabel{font-size:.65rem;color:var(--muted);margin-top:2px;}
.vc-arrow{font-size:1.4rem;color:var(--green);margin:0 .2rem;flex-shrink:0;padding-bottom:1.2rem;}
/* Features grid */
.features-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1.1rem;margin-bottom:2rem;}
.feature-card{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);padding:1.3rem;border:1px solid var(--border);transition:box-shadow .2s;}
.feature-card:hover{box-shadow:var(--shadow-hover);}
.feature-icon{font-size:1.8rem;margin-bottom:.7rem;}
.feature-title{font-weight:800;font-size:.92rem;color:var(--text);margin-bottom:.35rem;}
.feature-desc{font-size:.8rem;color:var(--muted);line-height:1.5;}
/* How it works */
.how-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1.5rem;margin-bottom:2rem;}
.how-card{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);padding:1.5rem;text-align:center;position:relative;}
.how-num{width:40px;height:40px;border-radius:50%;background:var(--dark);color:var(--yellow);font-weight:900;font-size:1.1rem;display:flex;align-items:center;justify-content:center;margin:0 auto .9rem;}
.how-title{font-weight:800;font-size:.95rem;color:var(--text);margin-bottom:.4rem;}
.how-desc{font-size:.82rem;color:var(--muted);line-height:1.5;}
/* Opportunities preview */
.opp-list{display:flex;flex-direction:column;gap:.75rem;margin-bottom:2rem;}
.opp-card{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);padding:1.1rem 1.3rem;display:flex;gap:1rem;align-items:flex-start;border-left:3px solid var(--green);}
.opp-type-badge{padding:2px 10px;border-radius:99px;font-size:.68rem;font-weight:700;background:var(--light-bg);color:var(--muted);white-space:nowrap;flex-shrink:0;}
.opp-title{font-weight:700;font-size:.9rem;color:var(--text);}
.opp-meta{font-size:.75rem;color:var(--muted);margin-top:3px;}
/* CTA section */
.cc-cta{background:linear-gradient(135deg,var(--green),#00962e);border-radius:var(--radius);padding:2.5rem;text-align:center;color:#fff;margin-bottom:2rem;}
.cc-cta-title{font-size:1.5rem;font-weight:900;margin-bottom:.6rem;}
.cc-cta-sub{font-size:.9rem;opacity:.85;margin-bottom:1.5rem;}
/* Sectors showcase */
.sectors-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:.75rem;margin-bottom:2rem;}
.sector-tile{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);padding:1.1rem;text-align:center;transition:transform .15s,box-shadow .2s;cursor:pointer;border:1px solid var(--border);}
.sector-tile:hover{transform:translateY(-2px);box-shadow:var(--shadow-hover);}
.sector-tile-icon{font-size:1.8rem;margin-bottom:.5rem;}
.sector-tile-name{font-size:.8rem;font-weight:700;color:var(--text);}
.sector-tile-count{font-size:.7rem;color:var(--muted);margin-top:2px;}
@media(max-width:700px){.cc-hero-title{font-size:1.7rem;}.how-grid{grid-template-columns:1fr;}.cc-stats-row{gap:1.2rem;}}
</style>

@php
$authUser = session('auth_user');
$companyCount = DB::table('companies')->whereNull('deleted_at')->count();
$activeOpps   = DB::table('collabcam_opportunities')->where('status','active')->whereNull('deleted_at')->count();
$activeCollabs= DB::table('collabcam_collaborations')->where('status','active')->whereNull('deleted_at')->count();
$featuredOpps = DB::table('collabcam_opportunities')
    ->join('companies','collabcam_opportunities.company_id','=','companies.id')
    ->where('collabcam_opportunities.status','active')
    ->whereNull('collabcam_opportunities.deleted_at')
    ->whereNull('companies.deleted_at')
    ->select('collabcam_opportunities.*','companies.name as company_name','companies.slug as company_slug')
    ->orderByRaw('is_featured DESC')->orderByDesc('collabcam_opportunities.created_at')
    ->limit(4)->get();
$typeLabels = [
    'seeking_supplier'=>'Seeking Supplier','seeking_distributor'=>'Seeking Distributor',
    'seeking_manufacturer'=>'Seeking Manufacturer','seeking_investor'=>'Seeking Investor',
    'seeking_logistics'=>'Seeking Logistics','seeking_warehouse'=>'Seeking Warehouse',
    'seeking_technology'=>'Seeking Technology','seeking_research'=>'Seeking R&D Partner',
    'seeking_export'=>'Seeking Export Partner','seeking_joint_venture'=>'Joint Venture',
    'offering_capacity'=>'Offering Capacity','offering_equipment'=>'Offering Equipment',
    'seeking_packaging'=>'Seeking Packaging','seeking_processing'=>'Seeking Processing','other'=>'Partnership',
];
@endphp

<div class="cc-page">
    {{-- Hero --}}
    <div class="cc-hero">
        <div style="position:relative;z-index:1;">
            <div class="cc-badge"><i data-lucide="sparkles" class="lic"></i> Premium Feature · CollabCam</div>
            <div class="cc-hero-title">Cameroon's B2B<br>Collaboration Platform</div>
            <div class="cc-hero-sub">Connect with businesses across every industry. Form supply chains, joint ventures, and value chain partnerships. Sign contracts. Track collaboration in real time.</div>
            <div class="cc-hero-actions">
                <a href="/collabcam/explore" class="cc-btn-primary">Explore Companies</a>
                <a href="/collabcam/opportunities" class="cc-btn-ghost">Browse Opportunities</a>
                @if($authUser)<a href="/collabcam/hub" class="cc-btn-ghost">My Collaborations</a>@endif
            </div>
            <div class="cc-stats-row">
                <div class="cc-stat"><div class="cc-stat-val">{{ number_format($companyCount) }}</div><div class="cc-stat-lbl">Companies</div></div>
                <div class="cc-stat"><div class="cc-stat-val">{{ $activeOpps ?: '0' }}</div><div class="cc-stat-lbl">Active Opportunities</div></div>
                <div class="cc-stat"><div class="cc-stat-val">{{ $activeCollabs ?: '0' }}</div><div class="cc-stat-lbl">Collaborations Active</div></div>
                <div class="cc-stat"><div class="cc-stat-val">20+</div><div class="cc-stat-lbl">Sectors Covered</div></div>
            </div>
        </div>
    </div>

    {{-- Value Chain Visualizer --}}
    <div class="section-title">Industry Value Chains</div>
    <div class="section-sub">See how companies collaborate across complete industry value chains in Cameroon.</div>
    <div class="vc-container">
        <div class="vc-tabs">
            <button class="vc-tab active" onclick="showChain(this,'cocoa')"><i data-lucide="candy" class="lic"></i> Cocoa</button>
            <button class="vc-tab" onclick="showChain(this,'timber')"><i data-lucide="trees" class="lic"></i> Timber</button>
            <button class="vc-tab" onclick="showChain(this,'palm')"><i data-lucide="palmtree" class="lic"></i> Palm Oil</button>
            <button class="vc-tab" onclick="showChain(this,'ict')"><i data-lucide="laptop" class="lic"></i> ICT</button>
            <button class="vc-tab" onclick="showChain(this,'textile')"><i data-lucide="shirt" class="lic"></i> Textile</button>
        </div>
        <div id="chain-cocoa" class="vc-chain">
            <div class="vc-node"><div class="vc-icon" style="background:#f0fdf4;"><i data-lucide="sprout" class="lic"></i></div><div class="vc-label">Farmers</div><div class="vc-sublabel">APROCAM</div></div>
            <div class="vc-arrow">→</div>
            <div class="vc-node"><div class="vc-icon" style="background:#fef9c3;"><i data-lucide="flask-conical" class="lic"></i></div><div class="vc-label">Inputs</div><div class="vc-sublabel">Pesticides &amp; Tools</div></div>
            <div class="vc-arrow">→</div>
            <div class="vc-node"><div class="vc-icon" style="background:#fef3c7;"><i data-lucide="tractor" class="lic"></i></div><div class="vc-label">Harvest</div><div class="vc-sublabel">Equipment Hire</div></div>
            <div class="vc-arrow">→</div>
            <div class="vc-node"><div class="vc-icon" style="background:#ffe4c4;"><i data-lucide="factory" class="lic"></i></div><div class="vc-label">Processing</div><div class="vc-sublabel">Fermentation &amp; Drying</div></div>
            <div class="vc-arrow">→</div>
            <div class="vc-node"><div class="vc-icon" style="background:#e8d5b7;"><i data-lucide="candy" class="lic"></i></div><div class="vc-label">Grinding</div><div class="vc-sublabel">Cocoa Mass</div></div>
            <div class="vc-arrow">→</div>
            <div class="vc-node"><div class="vc-icon" style="background:#f0e6d3;"><i data-lucide="package" class="lic"></i></div><div class="vc-label">Packaging</div><div class="vc-sublabel">Export Ready</div></div>
            <div class="vc-arrow">→</div>
            <div class="vc-node"><div class="vc-icon" style="background:#dbeafe;"><i data-lucide="ship" class="lic"></i></div><div class="vc-label">Logistics</div><div class="vc-sublabel">Port of Douala</div></div>
            <div class="vc-arrow">→</div>
            <div class="vc-node"><div class="vc-icon" style="background:#dcfce7;"><i data-lucide="globe" class="lic"></i></div><div class="vc-label">Export</div><div class="vc-sublabel">EU / Asia Markets</div></div>
        </div>
        <div id="chain-timber" class="vc-chain" style="display:none;">
            <div class="vc-node"><div class="vc-icon" style="background:#f0fdf4;"><i data-lucide="trees" class="lic"></i></div><div class="vc-label">Concession</div><div class="vc-sublabel">Forest Rights</div></div>
            <div class="vc-arrow">→</div>
            <div class="vc-node"><div class="vc-icon" style="background:#fef9c3;"><i data-lucide="pickaxe" class="lic"></i></div><div class="vc-label">Logging</div><div class="vc-sublabel">Harvesting</div></div>
            <div class="vc-arrow">→</div>
            <div class="vc-node"><div class="vc-icon" style="background:#fef3c7;"><i data-lucide="truck" class="lic"></i></div><div class="vc-label">Transport</div><div class="vc-sublabel">Log Hauling</div></div>
            <div class="vc-arrow">→</div>
            <div class="vc-node"><div class="vc-icon" style="background:#ffe4c4;"><i data-lucide="axe" class="lic"></i></div><div class="vc-label">Sawmill</div><div class="vc-sublabel">Primary Processing</div></div>
            <div class="vc-arrow">→</div>
            <div class="vc-node"><div class="vc-icon" style="background:#e8d5b7;"><i data-lucide="armchair" class="lic"></i></div><div class="vc-label">Furniture</div><div class="vc-sublabel">Value Added</div></div>
            <div class="vc-arrow">→</div>
            <div class="vc-node"><div class="vc-icon" style="background:#dcfce7;"><i data-lucide="globe" class="lic"></i></div><div class="vc-label">Export / Retail</div><div class="vc-sublabel">End Markets</div></div>
        </div>
        <div id="chain-palm" class="vc-chain" style="display:none;">
            <div class="vc-node"><div class="vc-icon" style="background:#fef9c3;"><i data-lucide="palmtree" class="lic"></i></div><div class="vc-label">Plantation</div><div class="vc-sublabel">Palm Cultivation</div></div>
            <div class="vc-arrow">→</div>
            <div class="vc-node"><div class="vc-icon" style="background:#fef3c7;"><i data-lucide="user" class="lic"></i>‍<i data-lucide="wheat" class="lic"></i></div><div class="vc-label">Harvesting</div><div class="vc-sublabel">FFB Collection</div></div>
            <div class="vc-arrow">→</div>
            <div class="vc-node"><div class="vc-icon" style="background:#ffe4c4;"><i data-lucide="factory" class="lic"></i></div><div class="vc-label">Mill</div><div class="vc-sublabel">Oil Extraction</div></div>
            <div class="vc-arrow">→</div>
            <div class="vc-node"><div class="vc-icon" style="background:#e8d5b7;"><i data-lucide="flask-conical" class="lic"></i></div><div class="vc-label">Refinery</div><div class="vc-sublabel">CPO → RBD</div></div>
            <div class="vc-arrow">→</div>
            <div class="vc-node"><div class="vc-icon" style="background:#dbeafe;"><i data-lucide="package" class="lic"></i></div><div class="vc-label">Packaging</div><div class="vc-sublabel">Consumer Packs</div></div>
            <div class="vc-arrow">→</div>
            <div class="vc-node"><div class="vc-icon" style="background:#dcfce7;"><i data-lucide="shopping-cart" class="lic"></i></div><div class="vc-label">Distribution</div><div class="vc-sublabel">Retail / Export</div></div>
        </div>
        <div id="chain-ict" class="vc-chain" style="display:none;">
            <div class="vc-node"><div class="vc-icon" style="background:#dbeafe;"><i data-lucide="lightbulb" class="lic"></i></div><div class="vc-label">Innovation</div><div class="vc-sublabel">Product Idea</div></div>
            <div class="vc-arrow">→</div>
            <div class="vc-node"><div class="vc-icon" style="background:#ede9fe;"><i data-lucide="user" class="lic"></i>‍<i data-lucide="laptop" class="lic"></i></div><div class="vc-label">Development</div><div class="vc-sublabel">Software Build</div></div>
            <div class="vc-arrow">→</div>
            <div class="vc-node"><div class="vc-icon" style="background:#fce7f3;"><i data-lucide="lock" class="lic"></i></div><div class="vc-label">Security</div><div class="vc-sublabel">Cyber Testing</div></div>
            <div class="vc-arrow">→</div>
            <div class="vc-node"><div class="vc-icon" style="background:#fef9c3;"><i data-lucide="settings" class="lic"></i></div><div class="vc-label">Integration</div><div class="vc-sublabel">System Setup</div></div>
            <div class="vc-arrow">→</div>
            <div class="vc-node"><div class="vc-icon" style="background:#f0fdf4;"><i data-lucide="graduation-cap" class="lic"></i></div><div class="vc-label">Training</div><div class="vc-sublabel">User Onboarding</div></div>
            <div class="vc-arrow">→</div>
            <div class="vc-node"><div class="vc-icon" style="background:#dcfce7;"><i data-lucide="wrench" class="lic"></i></div><div class="vc-label">Support</div><div class="vc-sublabel">Maintenance</div></div>
        </div>
        <div id="chain-textile" class="vc-chain" style="display:none;">
            <div class="vc-node"><div class="vc-icon" style="background:#f0fdf4;"><i data-lucide="leaf" class="lic"></i></div><div class="vc-label">Cotton</div><div class="vc-sublabel">SODECOTON</div></div>
            <div class="vc-arrow">→</div>
            <div class="vc-node"><div class="vc-icon" style="background:#fef9c3;"><i data-lucide="spool" class="lic"></i></div><div class="vc-label">Spinning</div><div class="vc-sublabel">Yarn Production</div></div>
            <div class="vc-arrow">→</div>
            <div class="vc-node"><div class="vc-icon" style="background:#ffe4c4;"><i data-lucide="factory" class="lic"></i></div><div class="vc-label">Weaving</div><div class="vc-sublabel">Fabric Making</div></div>
            <div class="vc-arrow">→</div>
            <div class="vc-node"><div class="vc-icon" style="background:#fce7f3;"><i data-lucide="palette" class="lic"></i></div><div class="vc-label">Dyeing</div><div class="vc-sublabel">Colour &amp; Print</div></div>
            <div class="vc-arrow">→</div>
            <div class="vc-node"><div class="vc-icon" style="background:#ede9fe;"><i data-lucide="scissors" class="lic"></i></div><div class="vc-label">Garments</div><div class="vc-sublabel">Cut &amp; Sew</div></div>
            <div class="vc-arrow">→</div>
            <div class="vc-node"><div class="vc-icon" style="background:#dcfce7;"><i data-lucide="shirt" class="lic"></i></div><div class="vc-label">Retail / Export</div><div class="vc-sublabel">End Consumer</div></div>
        </div>
        <div style="margin-top:1rem;font-size:.78rem;color:var(--muted);text-align:center;">CollabCam connects every node in these chains — digitally, with contracts and workspaces.</div>
    </div>

    {{-- Features --}}
    <div class="section-title">Everything you need to collaborate</div>
    <div class="section-sub">25+ collaboration capabilities in one platform.</div>
    <div class="features-grid">
        <div class="feature-card"><div class="feature-icon"><i data-lucide="search" class="lic"></i></div><div class="feature-title">Company Discovery</div><div class="feature-desc">Find and filter businesses by sector, location, verification status, and collaboration type. Send collaboration requests in one click.</div></div>
        <div class="feature-card"><div class="feature-icon"><i data-lucide="clipboard-list" class="lic"></i></div><div class="feature-title">Opportunity Exchange</div><div class="feature-desc">Post what you're seeking — suppliers, distributors, manufacturers, logistics. Browse and respond to hundreds of live business opportunities.</div></div>
        <div class="feature-card"><div class="feature-icon"><i data-lucide="handshake" class="lic"></i></div><div class="feature-title">Collaboration Workspaces</div><div class="feature-desc">A dedicated workspace for every partnership. Track milestones, manage documents, monitor progress, and communicate within the collaboration.</div></div>
        <div class="feature-card"><div class="feature-icon"><i data-lucide="file-text" class="lic"></i></div><div class="feature-title">Digital Contracts & MoUs</div><div class="feature-desc">Create NDAs, MoUs, supply agreements, joint venture contracts, distribution agreements. Track signing status across all parties.</div></div>
        <div class="feature-card"><div class="feature-icon"><i data-lucide="factory" class="lic"></i></div><div class="feature-title">Supply Chain Management</div><div class="feature-desc">Build multi-tier supply chains. Map your suppliers, track orders, evaluate performance, and ensure business continuity.</div></div>
        <div class="feature-card"><div class="feature-icon"><i data-lucide="bar-chart-3" class="lic"></i></div><div class="feature-title">Procurement Portal</div><div class="feature-desc">Post tenders, receive bids, evaluate suppliers, and award contracts — all within a transparent digital procurement process.</div></div>
        <div class="feature-card"><div class="feature-icon"><i data-lucide="globe" class="lic"></i></div><div class="feature-title">Export Hub</div><div class="feature-desc">Find export partners, discover international markets, connect with freight forwarders, and navigate export compliance together.</div></div>
        <div class="feature-card"><div class="feature-icon"><i data-lucide="bot" class="lic"></i></div><div class="feature-title">AI Partner Matching</div><div class="feature-desc">Our AI analyses your business profile and suggests ideal collaboration partners, flagging complementary capabilities and market gaps.</div></div>
        <div class="feature-card"><div class="feature-icon"><i data-lucide="banknote" class="lic"></i></div><div class="feature-title">Trade Finance</div><div class="feature-desc">Access invoice financing, letters of credit, supply chain finance, and payment terms negotiation through our finance partners.</div></div>
        <div class="feature-card"><div class="feature-icon"><i data-lucide="trophy" class="lic"></i></div><div class="feature-title">Supplier Performance</div><div class="feature-desc">Rate and review collaboration partners. Build a verified track record. See performance scores before you commit to a partnership.</div></div>
        <div class="feature-card"><div class="feature-icon"><i data-lucide="sprout" class="lic"></i></div><div class="feature-title">ESG & Sustainability</div><div class="feature-desc">Track and report on environmental, social, and governance commitments across your collaboration network. Meet international standards.</div></div>
        <div class="feature-card"><div class="feature-icon"><i data-lucide="link" class="lic"></i></div><div class="feature-title">Federation Mode</div><div class="feature-desc">Industry sectors can form governed digital ecosystems — a federated network with shared governance, compliance, and standards. <span style="color:var(--yellow);font-weight:700;">Strategic feature.</span></div></div>
    </div>

    {{-- How it works --}}
    <div class="section-title">How CollabCam works</div>
    <div class="section-sub">Three steps from discovery to signed collaboration.</div>
    <div class="how-grid">
        <div class="how-card">
            <div class="how-num">1</div>
            <div class="how-title">Discover &amp; Connect</div>
            <div class="how-desc">Browse verified Cameroonian companies. Filter by sector, location, and capability. Send a collaboration request with your proposal and collaboration type.</div>
        </div>
        <div class="how-card">
            <div class="how-num">2</div>
            <div class="how-title">Build Your Workspace</div>
            <div class="how-desc">When both parties accept, a shared workspace is created. Define milestones, add team members, and draft your contract or MoU directly in the platform.</div>
        </div>
        <div class="how-card">
            <div class="how-num">3</div>
            <div class="how-title">Execute &amp; Track</div>
            <div class="how-desc">Sign agreements digitally, track progress against milestones, monitor supply chain deliveries, and log every interaction in a shared collaboration record.</div>
        </div>
    </div>

    {{-- Live opportunities preview --}}
    @if($featuredOpps->count() > 0)
    <div class="section-title">Live Opportunities</div>
    <div class="section-sub">Companies seeking collaboration partners right now.</div>
    <div class="opp-list">
        @foreach($featuredOpps as $o)
        <div class="opp-card">
            <div style="flex:1;min-width:0;">
                <div style="display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;">
                    <span class="opp-type-badge">{{ $typeLabels[$o->type]??ucfirst(str_replace('_',' ',$o->type)) }}</span>
                    @if($o->sector)<span style="font-size:.7rem;color:var(--muted);">{{ ucfirst($o->sector) }}</span>@endif
                </div>
                <div class="opp-title" style="margin-top:.4rem;">{{ $o->title_en }}</div>
                <div class="opp-meta">{{ $o->company_name }}{{ $o->location ? ' · '.$o->location : '' }}{{ $o->deadline ? ' · Deadline: '.date('d M Y',strtotime($o->deadline)) : '' }}</div>
            </div>
            <a href="/collabcam/opportunities/{{ $o->id }}" style="background:var(--green);color:#fff;padding:.4rem .9rem;border-radius:7px;font-size:.78rem;font-weight:600;white-space:nowrap;flex-shrink:0;display:inline-block;">View →</a>
        </div>
        @endforeach
    </div>
    <div style="text-align:center;margin-bottom:2rem;"><a href="/collabcam/opportunities" style="color:var(--green);font-weight:700;font-size:.9rem;">View all opportunities →</a></div>
    @endif

    {{-- Sector tiles --}}
    <div class="section-title">Collaborate across every sector</div>
    <div class="section-sub">CollabCam covers all major sectors of the Cameroonian economy.</div>
    <div class="sectors-grid">
        @php $sectorData = [['wheat','Agriculture','Cocoa, Coffee, Palm Oil'],['factory','Industry','Manufacturing & Processing'],['trees','Timber','Forestry & Wood Products'],['laptop','ICT','Tech & Digital Services'],['zap','Energy','Power & Mining'],['hard-hat','Construction','Infrastructure & Real Estate'],['truck','Logistics','Transport & Warehousing'],['landmark','Finance','Banking & Insurance'],['heart-pulse','Health','Pharma & Healthcare'],['shopping-basket','Textiles','Garments & Crafts'],['plane','Tourism','Hotels & Travel'],['smartphone','Telecom','Communications & Media']]; @endphp
        @foreach($sectorData as $s)
        <a href="/collabcam/explore?sector={{ strtolower($s[1]) }}" class="sector-tile">
            <div class="sector-tile-icon">{{ $s[0] }}</div>
            <div class="sector-tile-name">{{ $s[1] }}</div>
            <div class="sector-tile-count">{{ $s[2] }}</div>
        </a>
        @endforeach
    </div>

    {{-- CTA --}}
    <div class="cc-cta">
        <div class="cc-cta-title">Ready to start collaborating?</div>
        <div class="cc-cta-sub">Join hundreds of Cameroonian businesses already building partnerships on CollabCam.</div>
        @if($authUser)
        <a href="/collabcam/explore" class="cc-btn-primary" style="font-size:1rem;">Explore Companies →</a>
        @else
        <a href="/auth/register" class="cc-btn-primary" style="font-size:1rem;">Get Started Free →</a>
        &nbsp;&nbsp;
        <a href="/auth/login" style="color:#fff;font-size:.9rem;opacity:.8;">Already have an account? Sign in →</a>
        @endif
    </div>
</div>

<script>
function showChain(tab, id) {
    document.querySelectorAll('.vc-tab').forEach(t=>t.classList.remove('active'));
    document.querySelectorAll('.vc-chain').forEach(c=>c.style.display='none');
    tab.classList.add('active');
    document.getElementById('chain-'+id).style.display='flex';
}
</script>
@include('partials.footer')
</body>
</html>
