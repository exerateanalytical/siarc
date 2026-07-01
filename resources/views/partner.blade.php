<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>{{ $partner->partner_name }} — Partner — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:1000px;margin:0 auto;padding:1.5rem;}
.back{font-size:.82rem;color:var(--muted);display:inline-flex;gap:.3rem;margin-bottom:1rem;}
.grid2{display:grid;grid-template-columns:1fr 300px;gap:1.5rem;}
.card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);border:1px solid var(--border);margin-bottom:1rem;}
.card-body{padding:1.3rem;}
.card-head{padding:.75rem 1.1rem;font-weight:700;font-size:.88rem;border-bottom:1px solid var(--border);background:var(--light-bg);}
.p-hero{display:flex;gap:1rem;align-items:center;margin-bottom:1rem;}
.p-avatar-lg{width:60px;height:60px;border-radius:12px;background:linear-gradient(135deg,#0f1623,#334155);display:flex;align-items:center;justify-content:center;font-size:1.3rem;font-weight:800;color:#fff;flex-shrink:0;}
.p-name-lg{font-size:1.3rem;font-weight:900;}
.badge{display:inline-block;padding:2px 10px;border-radius:99px;font-size:.7rem;font-weight:700;background:var(--light-bg);color:var(--muted);border:1px solid var(--border);}
.info-grid{display:grid;grid-template-columns:1fr 1fr;gap:.6rem;margin:.5rem 0;}
.info-item{padding:.55rem .8rem;background:var(--light-bg);border-radius:8px;}
.info-lbl{font-size:.68rem;color:var(--muted);font-weight:600;}
.info-val{font-size:.85rem;font-weight:700;color:var(--text);}
.timeline-item{display:flex;gap:.7rem;padding:.7rem 0;border-bottom:1px solid var(--border);}
.timeline-item:last-child{border-bottom:none;}
.ti-icon{width:32px;height:32px;border-radius:50%;background:var(--light-bg);display:flex;align-items:center;justify-content:center;font-size:.9rem;flex-shrink:0;}
.ti-type{font-size:.7rem;color:var(--muted);font-weight:600;text-transform:uppercase;}
.ti-subject{font-weight:700;font-size:.85rem;}
.ti-summary{font-size:.82rem;color:var(--muted);margin-top:.2rem;line-height:1.5;}
.form-control{width:100%;padding:.45rem .75rem;border:1px solid var(--border);border-radius:7px;font-size:.85rem;outline:none;font-family:inherit;box-sizing:border-box;}
.form-control:focus{border-color:var(--green);}
.form-label{display:block;font-size:.78rem;font-weight:600;margin-bottom:.25rem;}
</style>

@php
$relLabels = ['supplier'=>'Supplier','customer'=>'Customer','distributor'=>'Distributor','reseller'=>'Reseller','strategic'=>'Strategic','joint_venture'=>'Joint Venture','vendor'=>'Vendor','contractor'=>'Contractor','investor'=>'Investor','other'=>'Other'];
$typeIcons = ['meeting'=>'handshake','call'=>'phone','email'=>'mail','contract'=>'file-text','note'=>'pen-line','deal'=>'banknote','milestone'=>'target'];
$tierColors = ['strategic'=>['#ede9fe','#5b21b6'],'preferred'=>['#dbeafe','#1e40af'],'standard'=>['#f3f4f6','#374151'],'trial'=>['#fef3c7','#92400e']];
$statusColors = ['active'=>['#d1fae5','#065f46'],'prospect'=>['#fef3c7','#92400e'],'inactive'=>['#f3f4f6','#6b7280'],'former'=>['#fee2e2','#991b1b']];
$tc = $tierColors[$partner->tier]??$tierColors['standard'];
$sc = $statusColors[$partner->status]??$statusColors['prospect'];
@endphp

<div class="page">
    <a class="back" href="/prm">← Partner Relationships</a>
    <div class="grid2">
        <div>
            <div class="card">
                <div class="card-body">
                    <div class="p-hero">
                        <div class="p-avatar-lg">{{ strtoupper(substr($partner->partner_name,0,2)) }}</div>
                        <div>
                            <div class="p-name-lg">{{ $partner->partner_name }}</div>
                            <div style="display:flex;gap:.4rem;margin-top:.3rem;flex-wrap:wrap;">
                                <span class="badge">{{ $relLabels[$partner->relationship_type]??ucfirst($partner->relationship_type) }}</span>
                                <span class="badge" style="background:{{ $tc[0] }};color:{{ $tc[1] }};border:none;">{{ ucfirst($partner->tier) }}</span>
                                <span class="badge" style="background:{{ $sc[0] }};color:{{ $sc[1] }};border:none;">{{ ucfirst($partner->status) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="info-grid">
                        @if($partner->contact_name)<div class="info-item"><div class="info-lbl">Contact</div><div class="info-val">{{ $partner->contact_name }}</div></div>@endif
                        @if($partner->contact_phone)<div class="info-item"><div class="info-lbl">Phone</div><div class="info-val">{{ $partner->contact_phone }}</div></div>@endif
                        @if($partner->contact_email)<div class="info-item"><div class="info-lbl">Email</div><div class="info-val">{{ $partner->contact_email }}</div></div>@endif
                        @if($partner->value_estimate)<div class="info-item"><div class="info-lbl">Est. Value</div><div class="info-val" style="color:var(--green);">{{ number_format($partner->value_estimate) }} {{ $partner->currency }}</div></div>@endif
                    </div>
                    @if($partner->notes)
                    <div style="margin-top:.7rem;"><div class="info-lbl" style="margin-bottom:.2rem;">Notes</div><div style="font-size:.85rem;color:var(--text);line-height:1.6;">{{ $partner->notes }}</div></div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-head"><i data-lucide="clipboard-list" class="lic"></i> Interaction Timeline ({{ $interactions->count() }})</div>
                <div class="card-body">
                    @forelse($interactions as $it)
                    <div class="timeline-item">
                        <div class="ti-icon"><i data-lucide="{{ $typeIcons[$it->type]??'pen-line' }}" class="lic"></i></div>
                        <div style="flex:1;">
                            <div style="display:flex;justify-content:space-between;align-items:center;">
                                <span class="ti-type">{{ ucfirst($it->type) }}</span>
                                @if($it->interaction_date)<span style="font-size:.74rem;color:var(--muted);">{{ date('d M Y', strtotime($it->interaction_date)) }}</span>@endif
                            </div>
                            @if($it->subject)<div class="ti-subject">{{ $it->subject }}</div>@endif
                            @if($it->summary)<div class="ti-summary">{{ $it->summary }}</div>@endif
                        </div>
                    </div>
                    @empty
                    <div style="text-align:center;padding:1rem;color:var(--muted);font-size:.85rem;">No interactions logged yet.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div>
            <div class="card">
                <div class="card-head">+ Log Interaction</div>
                <div class="card-body">
                    <form method="POST" action="/prm/{{ $partner->id }}/interaction">
                        @csrf
                        <div style="margin-bottom:.6rem;"><label class="form-label">Type</label>
                            <select class="form-control" name="type">@foreach($typeIcons as $k=>$icon)<option value="{{ $k }}">{{ $icon }} {{ ucfirst($k) }}</option>@endforeach</select>
                        </div>
                        <div style="margin-bottom:.6rem;"><label class="form-label">Subject</label><input type="text" class="form-control" name="subject" placeholder="e.g. Quarterly review"></div>
                        <div style="margin-bottom:.6rem;"><label class="form-label">Date</label><input type="date" class="form-control" name="interaction_date" value="{{ date('Y-m-d') }}"></div>
                        <div style="margin-bottom:.6rem;"><label class="form-label">Summary</label><textarea class="form-control" name="summary" style="resize:vertical;min-height:70px;" placeholder="What happened?"></textarea></div>
                        <button type="submit" style="width:100%;padding:.55rem;background:var(--green);color:#fff;border:none;border-radius:8px;font-weight:700;font-size:.85rem;cursor:pointer;">Log Interaction →</button>
                    </form>
                </div>
            </div>

            @if($partnerCompany)
            <div style="background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);padding:1.1rem;border:1px solid var(--border);">
                <div style="font-size:.72rem;color:var(--muted);font-weight:600;margin-bottom:.4rem;"><i data-lucide="building-2" class="lic"></i> Linked Company</div>
                <a href="/companies/{{ $partnerCompany->slug }}" style="font-weight:700;color:var(--green);font-size:.88rem;">{{ $partnerCompany->name }} →</a>
            </div>
            @endif
        </div>
    </div>
</div>
@include('partials.footer')
</body>
</html>
