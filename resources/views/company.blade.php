<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>{{ $company->name }} — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@php $activeTab = 'companies'; @endphp
@include('partials.nav')
<style>
.breadcrumb{padding:.75rem 2rem;font-size:.8rem;color:var(--muted);border-bottom:1px solid var(--border);background:var(--white);}
.breadcrumb a{color:var(--green);}
.hero{background:linear-gradient(135deg,var(--dark) 0%,var(--mid) 100%);padding:2rem 2rem 0;color:#fff;}
.hero-inner{max-width:1100px;margin:0 auto;}
.hero-top{display:flex;gap:1.2rem;align-items:flex-start;margin-bottom:1.2rem;}
.logo-big{width:72px;height:72px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.6rem;font-weight:800;color:#fff;flex-shrink:0;text-transform:uppercase;}
.company-name-lg{font-size:1.5rem;font-weight:800;margin-bottom:.2rem;}
.company-trade-lg{font-size:.9rem;color:#8899aa;margin-bottom:.5rem;}
.hero-badges{display:flex;gap:.4rem;flex-wrap:wrap;}
.hbadge{font-size:.72rem;font-weight:700;padding:3px 9px;border-radius:99px;text-transform:uppercase;}
.hb-vs-certified{background:#d4edda;color:#007a33;}
.hb-vs-verified{background:#cce5ff;color:#0056b3;}
.hb-vs-basic{background:#fff3cd;color:#856404;}
.hb-vs-unverified{background:rgba(255,255,255,.1);color:#aab;}
.hb-legal{background:rgba(255,255,255,.12);color:#fff;}
.hb-featured{background:var(--yellow);color:#7a5a00;}
.tabs{display:flex;gap:0;border-bottom:none;margin-top:1rem;}
.tab-link{padding:.65rem 1.1rem;color:#8899aa;font-size:.85rem;font-weight:600;border-bottom:3px solid transparent;cursor:pointer;text-decoration:none;transition:color .15s,border-color .15s;white-space:nowrap;}
.tab-link:hover{color:#fff;border-bottom-color:rgba(255,255,255,.2);}
.tab-link.active{color:#fff;border-bottom-color:var(--green);}
.tab-count{display:inline-flex;align-items:center;justify-content:center;min-width:18px;height:18px;background:rgba(255,255,255,.15);border-radius:99px;font-size:.65rem;font-weight:700;padding:0 5px;margin-left:5px;}
.main{max-width:1100px;margin:0 auto;padding:1.5rem;display:grid;grid-template-columns:1fr 300px;gap:1.5rem;align-items:start;}
.card{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden;margin-bottom:1rem;}
.card-title{padding:.8rem 1.1rem;font-weight:700;font-size:.88rem;border-bottom:1px solid var(--border);background:var(--light-bg);display:flex;justify-content:space-between;align-items:center;}
.card-body{padding:1.1rem;}
.info-row{display:flex;gap:.5rem;padding:.5rem 0;border-bottom:1px solid var(--border);font-size:.85rem;align-items:flex-start;}
.info-row:last-child{border-bottom:none;}
.info-icon{width:22px;flex-shrink:0;opacity:.5;text-align:center;}
.info-lbl{color:var(--muted);min-width:130px;flex-shrink:0;}
.info-val{font-weight:600;word-break:break-word;}
.desc-text{font-size:.87rem;line-height:1.7;color:var(--text);}
.offering-card-sm{border:1px solid var(--border);border-radius:9px;padding:.9rem;margin-bottom:.75rem;display:block;text-decoration:none;color:inherit;}
.offering-card-sm:last-child{margin-bottom:0;}
.offering-title-sm{font-weight:700;font-size:.88rem;margin-bottom:.2rem;}
.offering-summary-sm{font-size:.77rem;color:var(--muted);line-height:1.5;}
.offering-meta-sm{display:flex;gap:.35rem;flex-wrap:wrap;margin-top:.4rem;}
.ob{font-size:.7rem;font-weight:600;padding:1px 7px;border-radius:99px;}
.ob-open{background:#d4edda;color:#007a33;}
.ob-cmf-approved{background:#cce5ff;color:#0056b3;}
.ob-pending-cmf{background:#fff3cd;color:#856404;}
.ob-closed,.ob-completed{background:#f8f9fa;color:#666;}
.ob-type{background:#eef2f7;color:#4a5568;}
.progress-mini{height:5px;background:#eee;border-radius:99px;overflow:hidden;margin-top:.5rem;}
.progress-mini-fill{height:100%;border-radius:99px;background:var(--green);}
.doc-row{display:flex;align-items:center;gap:.75rem;padding:.65rem 0;border-bottom:1px solid var(--border);font-size:.85rem;}
.doc-row:last-child{border-bottom:none;}
.doc-icon{font-size:1.2rem;flex-shrink:0;}
.doc-title{font-weight:600;}
.doc-type{font-size:.72rem;color:var(--muted);margin-top:1px;text-transform:uppercase;}
.member-row{display:flex;align-items:center;gap:.75rem;padding:.65rem 0;border-bottom:1px solid var(--border);}
.member-row:last-child{border-bottom:none;}
.member-avatar{width:36px;height:36px;border-radius:50%;background:var(--green);display:flex;align-items:center;justify-content:center;font-size:.85rem;font-weight:700;color:#fff;flex-shrink:0;}
.member-name{font-weight:600;font-size:.87rem;}
.member-title-text{font-size:.76rem;color:var(--muted);margin-top:1px;}
.member-role{font-size:.7rem;padding:1px 7px;border-radius:99px;background:#eef2f7;color:#4a5568;margin-left:auto;font-weight:600;}
.ver-step{display:flex;gap:.75rem;align-items:flex-start;padding:.6rem 0;}
.ver-dot{width:24px;height:24px;border-radius:50%;background:var(--green);display:flex;align-items:center;justify-content:center;color:#fff;font-size:.7rem;flex-shrink:0;margin-top:2px;}
.ver-dot-pending{background:#e0e0e0;}
.ver-info{flex:1;}
.ver-title{font-weight:600;font-size:.87rem;}
.ver-desc{font-size:.77rem;color:var(--muted);margin-top:2px;}
.sidebar-card{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden;margin-bottom:1rem;}
.s-row{display:flex;justify-content:space-between;padding:.45rem 1.1rem;border-bottom:1px solid var(--border);font-size:.82rem;}
.s-row:last-child{border-bottom:none;}
.s-lbl{color:var(--muted);}
.s-val{font-weight:600;text-align:right;}
.vs-block{padding:1.1rem;}
.vs-status{font-size:.9rem;font-weight:700;margin-bottom:.4rem;}
.vs-desc{font-size:.8rem;color:var(--muted);line-height:1.55;}
.contact-btn{display:block;padding:.7rem;text-align:center;background:var(--green);color:#fff;border-radius:8px;font-weight:700;font-size:.85rem;margin:.9rem 1.1rem;transition:background .15s;}
.contact-btn:hover{background:#00962e;}
.stat-nums{display:grid;grid-template-columns:1fr 1fr;gap:.5rem;padding:1rem 1.1rem;}
.stat-num{text-align:center;padding:.6rem;background:var(--light-bg);border-radius:7px;}
.stat-n{font-size:1.1rem;font-weight:800;}
.stat-l{font-size:.68rem;color:var(--muted);margin-top:1px;}
.empty-tab{text-align:center;padding:2rem;color:var(--muted);font-size:.85rem;}
@media(max-width:750px){.main{grid-template-columns:1fr;}.tabs{overflow-x:auto;}}
</style>

@php
    $ini = strtoupper(substr($company->trade_name ?: $company->name, 0, 2));
    $clrs = ['#007a33','#ce1126','#0056b3','#7b2d8b','#c0392b','#16a085','#d35400','#2c3e50'];
    $clr = $clrs[crc32($company->id) % count($clrs)];
    $lm = ['sarl'=>'SARL','sa'=>'SA','snc'=>'SNC','scs'=>'SCS','ge'=>'GE','association'=>'ASBL','cooperative'=>'Cooperative','other'=>'EP'];
    $vsClass = match($company->verification_status){'certified'=>'hb-vs-certified','verified'=>'hb-vs-verified','basic'=>'hb-vs-basic',default=>'hb-vs-unverified'};
    $vsLabel = match($company->verification_status){'certified'=>'Certified','verified'=>'Verified','basic'=>'Basic',default=>'Unverified'};
    $docTypeLabel = ['rccm'=>'RCCM','niu'=>'NIU','statuts'=>'Statuts','ifu'=>'IFU','cnps'=>'CNPS','cmf_license'=>'CMF License','annual_report'=>'Annual Report','other'=>'Document'];
    $lang = $lang ?? 'en';
    $desc = $lang === 'fr' ? ($company->description_fr ?: $company->description_en) : ($company->description_en ?: $company->description_fr);
@endphp

<div class="breadcrumb">
    <a href="/">Home</a> / <a href="/">Companies</a> / {{ $company->name }}
</div>

<div class="hero">
    <div class="hero-inner">
        <div class="hero-top">
            <div class="logo-big" style="background:{{ $clr }}">{{ $ini }}</div>
            <div>
                <div class="company-name-lg">{{ $company->name }}</div>
                @if($company->trade_name && $company->trade_name !== $company->name)
                    <div class="company-trade-lg">{{ $company->trade_name }}</div>
                @endif
                <div class="hero-badges">
                    <span class="hbadge {{ $vsClass }}">{{ $vsLabel }}</span>
                    <span class="hbadge hb-legal">{{ $lm[$company->legal_form] ?? strtoupper($company->legal_form) }}</span>
                    @if($company->is_featured)<span class="hbadge hb-featured">Featured</span>@endif
                    @if($company->incorporation_date)<span class="hbadge hb-legal">Est. {{ date('Y',strtotime($company->incorporation_date)) }}</span>@endif
                </div>
            </div>
        </div>
        <div class="tabs">
            <a class="tab-link {{ $tab==='about'?'active':'' }}" href="?tab=about">About</a>
            <a class="tab-link {{ $tab==='activity'?'active':'' }}" href="?tab=activity">
                Activity{!! ($activityCount ?? 0) > 0 ? '<span class="tab-count">'.$activityCount.'</span>' : '' !!}
            </a>
            <a class="tab-link {{ $tab==='offerings'?'active':'' }}" href="?tab=offerings">
                Offerings<span class="tab-count">{{ $allOfferings->count() }}</span>
            </a>
            <a class="tab-link {{ $tab==='products'?'active':'' }}" href="?tab=products">
                Products{!! isset($products) && $products->count() > 0 ? '<span class="tab-count">'.$products->count().'</span>' : '' !!}
            </a>
            <a class="tab-link {{ $tab==='jobs'?'active':'' }}" href="?tab=jobs">
                Jobs{!! isset($jobs) && $jobs->count() > 0 ? '<span class="tab-count">'.$jobs->count().'</span>' : '' !!}
            </a>
            <a class="tab-link {{ $tab==='branches'?'active':'' }}" href="?tab=branches">
                Branches{!! isset($branches) && $branches->count() > 0 ? '<span class="tab-count">'.$branches->count().'</span>' : '' !!}
            </a>
            <a class="tab-link {{ $tab==='documents'?'active':'' }}" href="?tab=documents">
                Documents<span class="tab-count">{{ $documents->count() }}</span>
            </a>
            <a class="tab-link {{ $tab==='team'?'active':'' }}" href="?tab=team">
                Team<span class="tab-count">{{ $members->count() }}</span>
            </a>
            <a class="tab-link {{ $tab==='verification'?'active':'' }}" href="?tab=verification">Verification</a>
            <a class="tab-link {{ $tab==='reviews'?'active':'' }}" href="?tab=reviews">
                Reviews{!! $reviews->count() > 0 ? '<span class="tab-count">'.$reviews->count().'</span>' : '' !!}
            </a>
        </div>
    </div>
</div>

<div class="main">
    <div>
    @if($tab === 'about')
        {{-- Share buttons --}}
        <div style="display:flex;gap:.5rem;margin-bottom:1rem;flex-wrap:wrap;">
            <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(url()->current()) }}" target="_blank" rel="noopener" style="padding:.35rem .8rem;background:#0077b5;color:#fff;border-radius:6px;font-size:.78rem;font-weight:600;">LinkedIn</a>
            <a href="https://twitter.com/intent/tweet?text={{ urlencode($company->name.' on Galerie virtuelle de l'artisanat du Cameroun') }}&url={{ urlencode(url()->current()) }}" target="_blank" rel="noopener" style="padding:.35rem .8rem;background:#1da1f2;color:#fff;border-radius:6px;font-size:.78rem;font-weight:600;">X / Twitter</a>
            <button onclick="navigator.clipboard.writeText('{{ url()->current() }}').then(()=>alert('Link copied!'))" style="padding:.35rem .8rem;background:var(--light-bg);color:var(--text);border:1px solid var(--border);border-radius:6px;font-size:.78rem;font-weight:600;cursor:pointer;">Copy Link</button>
        </div>

        @if($desc)
        <div class="card">
            <div class="card-title">About</div>
            <div class="card-body">
                <p class="desc-text">{{ $desc }}</p>
                @if($company->description_en && $company->description_fr)
                    <div style="margin-top:.75rem;display:flex;gap:.5rem;">
                        <a href="?tab=about&lang=en" style="font-size:.75rem;color:var(--green){{ $lang==='en'?';font-weight:700':'' }}">English</a> |
                        <a href="?tab=about&lang=fr" style="font-size:.75rem;color:var(--green){{ $lang==='fr'?';font-weight:700':'' }}">Français</a>
                    </div>
                @endif
            </div>
        </div>
        @endif

        @if($industries->count() > 0)
        <div class="card">
            <div class="card-title">Industries</div>
            <div class="card-body" style="display:flex;gap:.4rem;flex-wrap:wrap;">
                @foreach($industries as $ind)
                    <span style="padding:3px 10px;background:{{ $ind->is_primary?'var(--green)':'var(--light-bg)' }};color:{{ $ind->is_primary?'#fff':'var(--text)' }};border-radius:99px;font-size:.78rem;font-weight:600;">{{ $ind->name_en }}</span>
                @endforeach
            </div>
        </div>
        @endif

        <div class="card">
            <div class="card-title">Company Information</div>
            <div class="card-body">
                @if($company->address)<div class="info-row"><span class="info-icon"><i data-lucide="map-pin" style="width:15px;height:15px;"></i></span><span class="info-lbl">Address</span><span class="info-val">{{ $company->address }}@if($company->city_name), {{ $company->city_name }}@endif</span></div>@endif
                <div class="info-row"><span class="info-icon"><i data-lucide="map" class="lic"></i></span><span class="info-lbl">Region</span><span class="info-val">{{ $company->region_name }}</span></div>
                @if($company->phone)<div class="info-row"><span class="info-icon"><i data-lucide="phone" style="width:15px;height:15px;"></i></span><span class="info-lbl">Phone</span><span class="info-val"><a href="tel:{{ $company->phone }}">{{ $company->phone }}</a></span></div>@endif
                @if($company->email)<div class="info-row"><span class="info-icon"><i data-lucide="mail" style="width:15px;height:15px;"></i></span><span class="info-lbl">Email</span><span class="info-val"><a href="mailto:{{ $company->email }}">{{ $company->email }}</a></span></div>@endif
                @if($company->website)<div class="info-row"><span class="info-icon"><i data-lucide="globe" style="width:15px;height:15px;"></i></span><span class="info-lbl">Website</span><span class="info-val"><a href="{{ $company->website }}" target="_blank" rel="noopener">{{ $company->website }}</a></span></div>@endif
                @if($company->incorporation_date)<div class="info-row"><span class="info-icon"><i data-lucide="calendar" style="width:15px;height:15px;"></i></span><span class="info-lbl">Incorporated</span><span class="info-val">{{ date('d M Y',strtotime($company->incorporation_date)) }}</span></div>@endif
                @if($company->share_capital)<div class="info-row"><span class="info-icon"><i data-lucide="banknote" style="width:15px;height:15px;"></i></span><span class="info-lbl">Share Capital</span><span class="info-val">{{ number_format($company->share_capital) }} XAF</span></div>@endif
                @if($company->employee_count_min || $company->employee_count_max)
                    @php $empRange = $company->employee_count_min && $company->employee_count_max ? number_format($company->employee_count_min).'–'.number_format($company->employee_count_max) : number_format($company->employee_count_min ?: $company->employee_count_max); @endphp
                    <div class="info-row"><span class="info-icon"><i data-lucide="users" style="width:15px;height:15px;"></i></span><span class="info-lbl">Employees</span><span class="info-val">{{ $empRange }}</span></div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-title">Registry Numbers</div>
            <div class="card-body">
                @if($company->rccm_number)<div class="info-row"><span class="info-icon"><i data-lucide="clipboard-list" class="lic"></i></span><span class="info-lbl">RCCM</span><span class="info-val">{{ $company->rccm_number }}</span></div>@endif
                @if($company->niu_number)<div class="info-row"><span class="info-icon"><i data-lucide="hash" class="lic"></i></span><span class="info-lbl">NIU</span><span class="info-val">{{ $company->niu_number }}</span></div>@endif
                @if($company->cnps_number)<div class="info-row"><span class="info-icon"><i data-lucide="heart-pulse" class="lic"></i></span><span class="info-lbl">CNPS</span><span class="info-val">{{ $company->cnps_number }}</span></div>@endif
                @if(!$company->rccm_number && !$company->niu_number && !$company->cnps_number)
                    <p style="color:var(--muted);font-size:.84rem;">No registry numbers recorded.</p>
                @endif
            </div>
        </div>

        @if($offerings->count() > 0)
        <div class="card">
            <div class="card-title">Active Offerings <a href="?tab=offerings" style="font-size:.78rem;color:var(--green);font-weight:400;">View all →</a></div>
            <div class="card-body">
                @foreach($offerings as $o)
                    @php
                        $pct = $o->target_amount > 0 ? round($o->amount_raised / $o->target_amount * 100) : 0;
                        $stC = 'ob-'.str_replace('_','-',$o->status);
                        $stL = match($o->status){'open'=>'Open','cmf_approved'=>'CMF Approved','pending_cmf'=>'Pending CMF',default=>ucfirst($o->status)};
                        $iL = match($o->instrument_type){'ordinary_shares'=>'Equity','bonds'=>'Bond','preference_shares'=>'Pref. Share','convertible_notes'=>'Conv. Note',default=>$o->instrument_type};
                    @endphp
                    <a class="offering-card-sm" href="/offerings/{{ $o->id }}">
                        <div class="offering-title-sm">{{ $o->title_en }}</div>
                        <div class="offering-summary-sm">Target: {{ number_format($o->target_amount/1000000,0) }}M XAF &middot; Min: {{ number_format($o->min_investment) }} XAF</div>
                        <div class="offering-meta-sm">
                            <span class="ob {{ $stC }}">{{ $stL }}</span>
                            <span class="ob ob-type">{{ $iL }}</span>
                        </div>
                        @if($o->target_amount > 0)
                            <div class="progress-mini"><div class="progress-mini-fill" style="width:{{ min($pct,100) }}%"></div></div>
                            <div style="font-size:.7rem;color:var(--muted);margin-top:2px;">{{ $pct }}% raised</div>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
        @endif

    @elseif($tab === 'products')
        @php $products = $products ?? collect(); @endphp
        <div class="card">
            <div class="card-title">Products &amp; Services</div>
            <div class="card-body">
                @if($products->isEmpty())
                    <div class="empty-tab">No products or services listed for this company.</div>
                @else
                    @foreach($products as $p)
                    <div style="padding:.9rem 0;border-bottom:1px solid var(--border);">
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:.5rem;">
                            <div>
                                <div style="font-weight:700;font-size:.9rem;">{{ $p->name_en ?? $p->name_fr }}</div>
                                @if($p->category)<span style="font-size:.7rem;padding:2px 8px;border-radius:99px;background:var(--light-bg);color:var(--muted);font-weight:600;">{{ $p->category }}</span>@endif
                            </div>
                            @if($p->price)<div style="font-size:.85rem;font-weight:700;color:var(--green);white-space:nowrap;">{{ number_format($p->price) }} XAF</div>@endif
                        </div>
                        @if($p->description_en || $p->description_fr)
                        <p style="font-size:.83rem;color:var(--muted);margin-top:.4rem;line-height:1.5;">{{ $lang==='fr' ? ($p->description_fr ?: $p->description_en) : ($p->description_en ?: $p->description_fr) }}</p>
                        @endif
                    </div>
                    @endforeach
                @endif
            </div>
        </div>

    @elseif($tab === 'offerings')
        <div class="card">
            <div class="card-title">All Share Offerings</div>
            <div class="card-body">
                @if($allOfferings->isEmpty())
                    <div class="empty-tab">No offerings recorded for this company.</div>
                @else
                    @foreach($allOfferings as $o)
                        @php
                            $pct = $o->target_amount > 0 ? round($o->amount_raised / $o->target_amount * 100) : 0;
                            $stC = 'ob-'.str_replace('_','-',$o->status);
                            $stL = match($o->status){'open'=>'Open','cmf_approved'=>'CMF Approved','pending_cmf'=>'Pending CMF','closed'=>'Closed','completed'=>'Completed','cancelled'=>'Cancelled','paused'=>'Paused','draft'=>'Draft',default=>ucfirst($o->status)};
                            $iL = match($o->instrument_type){'ordinary_shares'=>'Equity','bonds'=>'Bond','preference_shares'=>'Pref. Share','convertible_notes'=>'Conv. Note',default=>$o->instrument_type};
                        @endphp
                        <a class="offering-card-sm" href="/offerings/{{ $o->id }}">
                            <div class="offering-title-sm">{{ $o->title_en }}</div>
                            <div class="offering-summary-sm">Target: {{ number_format($o->target_amount/1000000,0) }}M XAF &middot; Min: {{ number_format($o->min_investment) }} XAF</div>
                            <div class="offering-meta-sm">
                                <span class="ob {{ $stC }}">{{ $stL }}</span>
                                <span class="ob ob-type">{{ $iL }}</span>
                                @if($o->close_date)<span class="ob ob-type">Closes {{ date('M Y',strtotime($o->close_date)) }}</span>@endif
                            </div>
                            @if($o->target_amount > 0)
                                <div class="progress-mini"><div class="progress-mini-fill" style="width:{{ min($pct,100) }}%"></div></div>
                                <div style="font-size:.7rem;color:var(--muted);margin-top:2px;">{{ $pct }}% raised ({{ number_format($o->amount_raised/1000000,1) }}M XAF)</div>
                            @endif
                        </a>
                    @endforeach
                @endif
            </div>
        </div>

    @elseif($tab === 'documents')
        <div class="card">
            <div class="card-title">Public Documents</div>
            <div class="card-body">
                @if($documents->isEmpty())
                    <div class="empty-tab">No public documents available for this company.</div>
                @else
                    @foreach($documents as $d)
                        @php
                            $docIcon = match($d->type){'rccm'=>'clipboard-list','niu'=>'hash','statuts'=>'scroll-text','annual_report'=>'bar-chart-3','cmf_license'=>'building','ifu'=>'receipt','cnps'=>'heart-pulse',default=>'file-text'};
                        @endphp
                        <div class="doc-row">
                            <div class="doc-icon"><i data-lucide="{{ $docIcon }}" style="width:20px;height:20px;"></i></div>
                            <div style="flex:1">
                                <div class="doc-title">{{ $d->title }}</div>
                                <div class="doc-type">{{ $docTypeLabel[$d->type] ?? ucfirst($d->type) }}</div>
                            </div>
                            @if($d->is_verified)<span style="font-size:.7rem;color:var(--green);font-weight:600;"><i data-lucide="check" class="lic"></i> Verified</span>@endif
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

    @elseif($tab === 'team')
        <div class="card">
            <div class="card-title">Company Team</div>
            <div class="card-body">
                @if($members->isEmpty())
                    <div class="empty-tab">No team members listed for this company.</div>
                @else
                    @foreach($members as $m)
                        @php $initials = strtoupper(substr($m->first_name,0,1).substr($m->last_name,0,1)); @endphp
                        <div class="member-row">
                            <div class="member-avatar">{{ $initials }}</div>
                            <div style="flex:1">
                                <div class="member-name">{{ $m->first_name }} {{ $m->last_name }}</div>
                                @if($m->title)<div class="member-title-text">{{ $m->title }}</div>@endif
                            </div>
                            <span class="member-role">{{ ucfirst($m->role) }}</span>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

    @elseif($tab === 'verification')
        <div class="card">
            <div class="card-title">Verification Status</div>
            <div class="card-body">
                @php
                    $tiers = [
                        ['name'=>'Basic Listing','desc'=>'Company name, legal form, and region confirmed.','done'=>in_array($company->verification_status,['basic','verified','certified'])],
                        ['name'=>'Registry Verification','desc'=>'RCCM and NIU numbers verified against government registries.','done'=>in_array($company->verification_status,['verified','certified'])],
                        ['name'=>'CMF Certified','desc'=>'Full compliance review by the Commission des Marchés Financiers.','done'=>$company->verification_status==='certified'],
                    ];
                @endphp
                @foreach($tiers as $i => $tier)
                    <div class="ver-step">
                        <div class="ver-dot {{ $tier['done']?'':'ver-dot-pending' }}">{{ $tier['done']?'check':($i+1) }}</div>
                        <div class="ver-info">
                            <div class="ver-title">{{ $tier['name'] }}</div>
                            <div class="ver-desc">{{ $tier['desc'] }}</div>
                        </div>
                    </div>
                @endforeach
                @if($verification->count() > 0)
                    <hr style="border:none;border-top:1px solid var(--border);margin:1rem 0;">
                    <div style="font-size:.82rem;font-weight:700;margin-bottom:.5rem;color:var(--muted)">Verification History</div>
                    @foreach($verification as $v)
                        <div style="padding:.5rem 0;border-bottom:1px solid var(--border);font-size:.82rem;">
                            <div style="font-weight:600;">{{ $v->tier_name }}</div>
                            <div style="color:var(--muted)">{{ ucfirst(str_replace('_',' ',$v->status)) }} &middot; {{ $v->created_at ? date('d M Y',strtotime($v->created_at)) : '' }}</div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    @elseif($tab === 'reviews')
        <div class="card">
            <div class="card-title">Company Reviews</div>
            <div class="card-body">
                @if($reviews->isEmpty())
                    <div class="empty-tab">No reviews yet for this company.</div>
                @else
                    @foreach($reviews as $rv)
                        @php $stars = round($rv->rating); @endphp
                        <div style="padding:.8rem 0;border-bottom:1px solid var(--border);">
                            <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:.3rem;">
                                <div style="width:30px;height:30px;border-radius:50%;background:var(--green);display:flex;align-items:center;justify-content:center;color:#fff;font-size:.75rem;font-weight:700;">{{ strtoupper(substr($rv->first_name,0,1).substr($rv->last_name,0,1)) }}</div>
                                <div>
                                    <div style="font-weight:600;font-size:.85rem;">{{ $rv->first_name }} {{ $rv->last_name }}</div>
                                    <div style="color:#f59e0b;font-size:.85rem;">{{ str_repeat('★',$stars).str_repeat('☆',5-$stars) }}</div>
                                </div>
                                <div style="margin-left:auto;font-size:.73rem;color:var(--muted);">{{ date('d M Y',strtotime($rv->created_at)) }}</div>
                            </div>
                            @if($rv->comment_en || $rv->comment_fr)
                                <p style="font-size:.83rem;color:var(--text);line-height:1.6;">{{ $lang==='fr'?($rv->comment_fr?:$rv->comment_en):($rv->comment_en?:$rv->comment_fr) }}</p>
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

    @elseif($tab === 'activity')
        @php
        $hs = $healthScore ?? null;
        $gradeColors = ['A'=>'#16a34a','B'=>'#0284c7','C'=>'#d97706','D'=>'#dc2626','E'=>'#6b7280'];
        @endphp
        @if($hs)
        <div class="card" style="margin-bottom:1rem;">
            <div class="card-body" style="display:flex;align-items:center;gap:1.2rem;flex-wrap:wrap;">
                <div style="width:70px;height:70px;border-radius:50%;border:6px solid {{ $gradeColors[$hs->grade]??'#6b7280' }};display:flex;align-items:center;justify-content:center;font-size:1.4rem;font-weight:900;color:{{ $gradeColors[$hs->grade]??'#6b7280' }};flex-shrink:0;">{{ $hs->overall_score }}</div>
                <div style="flex:1;min-width:180px;">
                    <div style="font-weight:800;font-size:.95rem;">Collaboration Health Score — Grade {{ $hs->grade }}</div>
                    <div style="font-size:.8rem;color:var(--muted);margin-top:.2rem;">Network {{ $hs->network_score }} · Activity {{ $hs->activity_score }} · Reputation {{ $hs->reputation_score }} · Sustainability {{ $hs->sustainability_score }} · Engagement {{ $hs->engagement_score }}</div>
                    <a href="/health-score/{{ $company->slug }}" style="font-size:.8rem;color:var(--green);font-weight:600;">View full breakdown →</a>
                </div>
            </div>
        </div>
        @endif

        @if(($activityCount ?? 0) === 0 && !$hs)
        <div class="card"><div class="card-body"><div class="empty-tab">This company has no public activity across platform modules yet.</div></div></div>
        @endif

        @if($supplierReviewStats['count'] > 0)
        <div class="card" style="margin-bottom:1rem;"><div class="card-body" style="display:flex;align-items:center;gap:1rem;">
            <div style="font-size:1.6rem;font-weight:900;color:#d97706;">{{ number_format($supplierReviewStats['avg'],1) }}<span style="font-size:.8rem;color:var(--muted);">/5</span></div>
            <div><div style="font-weight:700;font-size:.88rem;"><i data-lucide="star" style="width:14px;height:14px;display:inline;vertical-align:-2px;"></i> Supplier Performance Rating</div><div style="font-size:.8rem;color:var(--muted);">{{ $supplierReviewStats['count'] }} verified buyer review{{ $supplierReviewStats['count']!=1?'s':'' }} · <a href="/supplier-reviews" style="color:var(--green);">View →</a></div></div>
        </div></div>
        @endif

        @if($actFederations->count() > 0)
        <div class="card" style="margin-bottom:1rem;"><div class="card-title"><i data-lucide="landmark" style="width:16px;height:16px;display:inline;vertical-align:-3px;"></i> Federation Memberships</div><div class="card-body">
            @foreach($actFederations as $f)<a href="/federations/{{ $f->slug }}" style="display:inline-block;margin:.2rem .3rem .2rem 0;padding:.3rem .8rem;background:var(--light-bg);border:1px solid var(--border);border-radius:99px;font-size:.8rem;color:var(--text);">{{ $f->name }} <span style="color:var(--muted);font-size:.7rem;">· {{ ucfirst($f->role) }}</span></a>@endforeach
        </div></div>
        @endif

        @php
        $sections = [
            ['file-text','Tenders & Procurement', $actTenders, fn($t)=>['/tenders/'.$t->id, $t->title, ucfirst($t->status)]],
            ['hand-coins','Investment Opportunities', $actInvest, fn($t)=>['/invest-hub/'.$t->id, $t->title, number_format($t->amount_sought/1000000,1).'M '.$t->currency]],
            ['lightbulb','Innovation Projects', $actInnovation, fn($t)=>['/innovation/'.$t->slug, $t->title, ucfirst(str_replace('_',' ',$t->type))]],
            ['calendar','Events Hosted', $actEvents, fn($t)=>['/events/'.$t->id, $t->title, date('d M Y',strtotime($t->start_date))]],
            ['package','Shared Assets', $actAssets, fn($t)=>['/assets/'.$t->slug, $t->title, ucfirst($t->category)]],
            ['truck','Logistics Listings', $actLogistics, fn($t)=>['/logistics/'.$t->id, $t->title, $t->origin_city.' → '.$t->destination_city]],
            ['handshake','Collaboration Opportunities', $actOpps, fn($t)=>['/collabcam/opportunities/'.$t->id, $t->title_en, ucfirst(str_replace('_',' ',$t->type))]],
        ];
        @endphp
        @foreach($sections as [$sicon,$label,$items,$fmt])
            @if($items->count() > 0)
            <div class="card" style="margin-bottom:1rem;"><div class="card-title"><i data-lucide="{{ $sicon }}" style="width:16px;height:16px;display:inline;vertical-align:-3px;"></i> {{ $label }}</div><div class="card-body">
                @foreach($items as $it)@php [$url,$title,$meta] = $fmt($it); @endphp
                <a href="{{ $url }}" style="display:flex;justify-content:space-between;align-items:center;gap:.5rem;padding:.55rem 0;border-bottom:1px solid var(--border);">
                    <span style="font-weight:600;font-size:.85rem;color:var(--text);">{{ $title }}</span>
                    <span style="font-size:.75rem;color:var(--muted);white-space:nowrap;">{{ $meta }}</span>
                </a>
                @endforeach
            </div></div>
            @endif
        @endforeach

        @if($actEsg)
        <div class="card" style="margin-bottom:1rem;"><div class="card-title"><i data-lucide="leaf" style="width:16px;height:16px;display:inline;vertical-align:-3px;"></i> ESG & Sustainability ({{ $actEsg->year }})</div><div class="card-body" style="display:flex;gap:1rem;flex-wrap:wrap;">
            <div style="text-align:center;"><div style="font-size:1.4rem;font-weight:900;color:#16a34a;">{{ $actEsg->overall_esg_score }}</div><div style="font-size:.7rem;color:var(--muted);">Overall</div></div>
            <div style="text-align:center;"><div style="font-size:1.1rem;font-weight:700;">{{ $actEsg->env_score }}</div><div style="font-size:.7rem;color:var(--muted);">Environment</div></div>
            <div style="text-align:center;"><div style="font-size:1.1rem;font-weight:700;">{{ $actEsg->social_score }}</div><div style="font-size:.7rem;color:var(--muted);">Social</div></div>
            <div style="text-align:center;"><div style="font-size:1.1rem;font-weight:700;">{{ $actEsg->governance_score }}</div><div style="font-size:.7rem;color:var(--muted);">Governance</div></div>
            <a href="/esg" style="margin-left:auto;align-self:center;font-size:.8rem;color:var(--green);font-weight:600;">ESG Leaderboard →</a>
        </div></div>
        @endif

    @elseif($tab === 'jobs')
        @php $jobTypeLabels = ['full_time'=>'Full-time','part_time'=>'Part-time','contract'=>'Contract','internship'=>'Internship','temporary'=>'Temporary','freelance'=>'Freelance']; @endphp
        @if($jobs->isEmpty())
            <div class="card"><div class="card-body"><div class="empty-tab">This company has no job postings yet. <a href="/jobs" style="color:var(--green);">Browse all jobs →</a></div></div></div>
        @else
        <div class="card">
            <div class="card-title">Open Positions <a href="/jobs" style="font-size:.78rem;color:var(--green);font-weight:400;">All jobs →</a></div>
            <div class="card-body">
                @foreach($jobs as $job)
                <a href="/jobs/{{ $job->id }}" style="display:block;padding:.8rem 0;border-bottom:1px solid var(--border);">
                    <div style="display:flex;justify-content:space-between;align-items:center;gap:.5rem;flex-wrap:wrap;">
                        <div style="font-weight:700;font-size:.9rem;color:var(--text);">{{ $lang==='fr' ? ($job->title_fr ?: $job->title_en) : ($job->title_en ?: $job->title_fr) }}</div>
                        <span style="font-size:.7rem;font-weight:700;padding:2px 9px;border-radius:99px;background:{{ $job->status==='open'?'#d1fae5':'#f3f4f6' }};color:{{ $job->status==='open'?'#065f46':'#6b7280' }};">{{ ucfirst($job->status) }}</span>
                    </div>
                    <div style="font-size:.77rem;color:var(--muted);margin-top:.3rem;display:flex;gap:.8rem;flex-wrap:wrap;">
                        @if($job->type)<span><i data-lucide="briefcase" style="width:12px;height:12px;display:inline;vertical-align:-2px;"></i> {{ $jobTypeLabels[$job->type]??ucfirst(str_replace('_',' ',$job->type)) }}</span>@endif
                        @if($job->location)<span><i data-lucide="map-pin" style="width:12px;height:12px;display:inline;vertical-align:-2px;"></i> {{ $job->location }}</span>@endif
                        @if($job->department)<span><i data-lucide="building-2" style="width:12px;height:12px;display:inline;vertical-align:-2px;"></i> {{ $job->department }}</span>@endif
                        @if($job->salary_min && $job->salary_max)<span><i data-lucide="banknote" style="width:12px;height:12px;display:inline;vertical-align:-2px;"></i> {{ number_format($job->salary_min) }}–{{ number_format($job->salary_max) }} {{ $job->currency ?: 'XAF' }}</span>@endif
                        @if($job->deadline)<span><i data-lucide="clock" style="width:12px;height:12px;display:inline;vertical-align:-2px;"></i> {{ date('d M Y', strtotime($job->deadline)) }}</span>@endif
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        @if($companySalaries->count() > 0)
        <div class="card">
            <div class="card-title"><i data-lucide="banknote" style="width:16px;height:16px;display:inline;vertical-align:-3px;"></i> Reported Salaries <a href="/salaries" style="font-size:.78rem;color:var(--green);font-weight:400;">Salary insights →</a></div>
            <div class="card-body">
                @foreach($companySalaries as $cs)
                <a href="/salaries/{{ $cs->job_slug }}" style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid var(--border);font-size:.84rem;">
                    <span style="font-weight:600;">{{ $cs->job_title }} <span style="color:var(--muted);font-size:.74rem;">· {{ $cs->reports }} report{{ $cs->reports!=1?'s':'' }}</span></span>
                    <span style="font-weight:700;color:#0d9488;">{{ number_format($cs->monthly_avg) }} XAF/mo</span>
                </a>
                @endforeach
            </div>
        </div>
        @endif

    @elseif($tab === 'branches')
        @php
        $btIcons = ['headquarters'=>'landmark','regional_office'=>'building-2','branch'=>'store','factory'=>'factory','warehouse'=>'package','retail_outlet'=>'shopping-bag','service_center'=>'wrench','farm'=>'wheat','other'=>'map-pin'];
        $btLabels = ['headquarters'=>'Headquarters','regional_office'=>'Regional Office','branch'=>'Branch','factory'=>'Factory','warehouse'=>'Warehouse','retail_outlet'=>'Retail Outlet','service_center'=>'Service Center','farm'=>'Farm','other'=>'Location'];
        @endphp
        @if($branches->isEmpty())
            <div class="card"><div class="card-body"><div class="empty-tab">No branches or locations listed for this company yet.</div></div></div>
        @else
        <div class="card">
            <div class="card-title">Locations & Branches ({{ $branches->count() }})</div>
            <div class="card-body">
                @foreach($branches as $br)
                <div style="display:flex;gap:.8rem;padding:.85rem 0;border-bottom:1px solid var(--border);">
                    <div style="flex-shrink:0;color:var(--green);"><i data-lucide="{{ $btIcons[$br->branch_type]??'map-pin' }}" class="lic"></i>" style="width:24px;height:24px;"></i></div>
                    <div style="flex:1;">
                        <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;">
                            <span style="font-weight:700;font-size:.9rem;">{{ $br->name }}</span>
                            <span style="font-size:.68rem;font-weight:700;padding:1px 8px;border-radius:99px;background:var(--light-bg);border:1px solid var(--border);color:var(--muted);">{{ $btLabels[$br->branch_type]??'Location' }}</span>
                            @if($br->is_primary)<span style="font-size:.68rem;font-weight:700;padding:1px 8px;border-radius:99px;background:#d1fae5;color:#065f46;">Primary</span>@endif
                        </div>
                        <div style="font-size:.78rem;color:var(--muted);margin-top:.3rem;line-height:1.6;">
                            @if($br->address){{ $br->address }}@endif{{ $br->city ? ' · '.$br->city : '' }}{{ $br->region ? ', '.$br->region : '' }}<br>
                            @if($br->phone)<span><i data-lucide="phone" style="width:12px;height:12px;display:inline;vertical-align:-2px;"></i> {{ $br->phone }}</span>@endif
                            @if($br->email)<span style="margin-left:.6rem;"><i data-lucide="mail" style="width:12px;height:12px;display:inline;vertical-align:-2px;"></i> {{ $br->email }}</span>@endif
                            @if($br->manager_name)<span style="margin-left:.6rem;"><i data-lucide="user" style="width:12px;height:12px;display:inline;vertical-align:-2px;"></i> {{ $br->manager_name }}</span>@endif
                            @if($br->staff_count)<span style="margin-left:.6rem;"><i data-lucide="users" style="width:12px;height:12px;display:inline;vertical-align:-2px;"></i> {{ $br->staff_count }} staff</span>@endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    @endif
    </div>

    <div>
        @if($company->rating_avg > 0)
        <div class="sidebar-card" style="padding:1rem 1.1rem;text-align:center;">
            @php $stars = round($company->rating_avg * 2) / 2; @endphp
            <div style="font-size:1.5rem;font-weight:800;color:var(--text);">{{ number_format($company->rating_avg,1) }}</div>
            <div style="color:#f59e0b;font-size:1.1rem;letter-spacing:2px;margin:.2rem 0;">
                @for($i=1;$i<=5;$i++){{ $i <= $stars ? '★' : ($i - 0.5 === $stars ? '½' : '☆') }}@endfor
            </div>
            <div style="font-size:.75rem;color:var(--muted);">{{ $company->rating_count }} review{{ $company->rating_count!=1?'s':'' }}</div>
        </div>
        @endif

        <div class="sidebar-card">
            <div class="vs-block">
                <div class="vs-status">{{ $vsLabel }}</div>
                <div class="vs-desc">
                    @if($company->verification_status === 'certified') This company has been fully verified and certified by the CMF Cameroon and the Galerie virtuelle de l'artisanat du Cameroun platform.
                    @elseif($company->verification_status === 'verified') Registry details have been verified against official government data sources.
                    @elseif($company->verification_status === 'basic') Basic information has been collected but not yet independently verified.
                    @else This company listing has not yet been verified.
                    @endif
                </div>
            </div>
            @if(session('auth_user'))
            <form method="POST" action="/companies/{{ $company->slug }}/contact" id="contact-form" style="padding:0 1.1rem .3rem;">
                @csrf
                <input type="hidden" name="subject" value="General Inquiry">
                <textarea name="message" placeholder="Your message to {{ $company->name }}…" rows="3" style="width:100%;padding:.5rem .7rem;border:1px solid var(--border);border-radius:7px;font-size:.82rem;resize:vertical;font-family:inherit;margin-bottom:.4rem;" required></textarea>
                <button type="submit" class="contact-btn" style="margin:0;display:block;text-align:center;">Send Message</button>
            </form>
            @else
            <a class="contact-btn" href="/login?next=/companies/{{ $company->slug }}">Log in to Contact</a>
            @endif
            <div style="padding:0 1.1rem .7rem;display:flex;justify-content:space-between;align-items:center;">
                @if(session('auth_user'))
                <form method="POST" action="/watchlist/toggle" style="display:inline;">
                    @csrf
                    <input type="hidden" name="company_id" value="{{ $company->id }}">
                    <button type="submit" style="background:none;border:1px solid var(--border);border-radius:6px;padding:.28rem .7rem;font-size:.75rem;cursor:pointer;color:{{ $inWatchlist ?? false ? '#d97706' : 'var(--muted)' }};display:inline-flex;align-items:center;gap:.3rem;"><i data-lucide="star" style="width:13px;height:13px;{{ $inWatchlist ?? false ? 'fill:#d97706;' : '' }}"></i> {{ $inWatchlist ?? false ? 'Saved' : 'Watchlist' }}</button>
                </form>
                @endif
                @if(!($company->verification_status === 'certified' || $company->verification_status === 'verified'))
                <a href="/companies/{{ $company->slug }}/claim" style="font-size:.75rem;color:var(--muted);padding:.28rem .7rem;border:1px solid var(--border);border-radius:6px;">Claim →</a>
                @endif
            </div>
            <div class="stat-nums">
                <div class="stat-num"><div class="stat-n">{{ number_format($company->view_count) }}</div><div class="stat-l">Views</div></div>
                <div class="stat-num"><div class="stat-n">{{ $allOfferings->count() }}</div><div class="stat-l">Offerings</div></div>
            </div>
        </div>

        <div class="sidebar-card">
            <div class="card-title">Quick Facts</div>
            @if($company->legal_form)<div class="s-row"><span class="s-lbl">Legal Form</span><span class="s-val">{{ $lm[$company->legal_form] ?? strtoupper($company->legal_form) }}</span></div>@endif
            @if($company->region_name)<div class="s-row"><span class="s-lbl">Region</span><span class="s-val">{{ $company->region_name }}</span></div>@endif
            @if($company->city_name)<div class="s-row"><span class="s-lbl">City</span><span class="s-val">{{ $company->city_name }}</span></div>@endif
            @if($company->incorporation_date)<div class="s-row"><span class="s-lbl">Founded</span><span class="s-val">{{ date('Y',strtotime($company->incorporation_date)) }}</span></div>@endif
            @if($company->share_capital)<div class="s-row"><span class="s-lbl">Capital</span><span class="s-val">{{ number_format($company->share_capital/1000000,0) }}M XAF</span></div>@endif
            @if($company->employee_count_min)<div class="s-row"><span class="s-lbl">Employees</span><span class="s-val">{{ number_format($company->employee_count_min) }}{{ $company->employee_count_max?'–'.number_format($company->employee_count_max):'+' }}</span></div>@endif
            @if($company->rccm_number)<div class="s-row"><span class="s-lbl">RCCM</span><span class="s-val" style="font-size:.75rem;">{{ $company->rccm_number }}</span></div>@endif
        </div>

        @if($related->count() > 0)
        <div class="sidebar-card">
            <div class="card-title">Same Region</div>
            <div style="padding:.75rem;">
                @foreach($related as $r)
                    @php
                        $ri = strtoupper(substr($r->trade_name ?: $r->name, 0, 2));
                        $rc = $clrs[crc32($r->id) % count($clrs)];
                    @endphp
                    <a href="/companies/{{ $r->slug }}" style="text-decoration:none;color:inherit;display:flex;gap:.6rem;align-items:flex-start;padding:.45rem 0;border-bottom:1px solid var(--border);">
                        <div style="width:30px;height:30px;border-radius:6px;background:{{ $rc }};display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:800;color:#fff;flex-shrink:0;">{{ $ri }}</div>
                        <div>
                            <div style="font-size:.8rem;font-weight:600;">{{ $r->name }}</div>
                            <div style="font-size:.72rem;color:var(--muted);">{{ $r->city_name ?? $r->region_name }}</div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

@include('partials.footer')
</body>
</html>
