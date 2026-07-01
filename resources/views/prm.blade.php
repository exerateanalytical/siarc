<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Partner Relationships — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:1100px;margin:0 auto;padding:1.5rem;}
.header-row{display:flex;justify-content:space-between;align-items:center;margin-bottom:1.2rem;flex-wrap:wrap;gap:.75rem;}
.h-title{font-size:1.5rem;font-weight:900;}
.h-sub{font-size:.85rem;color:var(--muted);}
.btn-add{padding:.5rem 1.2rem;background:var(--green);color:#fff;border:none;border-radius:8px;font-weight:700;font-size:.85rem;cursor:pointer;}
.stats-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:.8rem;margin-bottom:1.5rem;}
.stat-card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);border:1px solid var(--border);padding:1rem 1.2rem;}
.stat-num{font-size:1.6rem;font-weight:900;color:var(--text);}
.stat-lbl{font-size:.74rem;color:var(--muted);font-weight:600;margin-top:2px;}
.filter-tabs{display:flex;gap:.4rem;flex-wrap:wrap;margin-bottom:1rem;}
.filter-tab{padding:.3rem .9rem;border-radius:99px;font-size:.78rem;font-weight:600;cursor:pointer;border:1px solid var(--border);background:#fff;color:var(--muted);}
.filter-tab.active{background:var(--dark);color:#fff;border-color:var(--dark);}
.partner-table{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);border:1px solid var(--border);overflow:hidden;}
.p-row{display:flex;align-items:center;gap:.75rem;padding:.85rem 1.2rem;border-bottom:1px solid var(--border);text-decoration:none;color:var(--text);transition:background .12s;}
.p-row:last-child{border-bottom:none;}
.p-row:hover{background:var(--light-bg);}
.p-avatar{width:38px;height:38px;border-radius:9px;background:linear-gradient(135deg,#0f1623,#334155);display:flex;align-items:center;justify-content:center;font-size:.85rem;font-weight:800;color:#fff;flex-shrink:0;}
.p-name{font-weight:700;font-size:.9rem;}
.p-meta{font-size:.76rem;color:var(--muted);}
.tier-badge{padding:2px 8px;border-radius:99px;font-size:.68rem;font-weight:700;}
.status-badge{padding:2px 9px;border-radius:99px;font-size:.68rem;font-weight:700;}
.p-value{font-weight:800;font-size:.85rem;color:var(--green);text-align:right;}
/* modal */
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:200;align-items:center;justify-content:center;}
.modal-overlay.open{display:flex;}
.modal{background:#fff;border-radius:var(--radius);padding:1.5rem;width:min(520px,95vw);max-height:90vh;overflow-y:auto;}
.form-group{margin-bottom:.8rem;}
.form-label{display:block;font-size:.8rem;font-weight:600;margin-bottom:.3rem;}
.form-control{width:100%;padding:.45rem .75rem;border:1px solid var(--border);border-radius:7px;font-size:.85rem;outline:none;font-family:inherit;box-sizing:border-box;}
.form-control:focus{border-color:var(--green);}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:.6rem;}
@media(max-width:600px){.form-row{grid-template-columns:1fr;}.p-value,.p-meta-hide{display:none;}}
</style>

@php
$filter = request('status','');
$tierColors = ['strategic'=>['#ede9fe','#5b21b6'],'preferred'=>['#dbeafe','#1e40af'],'standard'=>['#f3f4f6','#374151'],'trial'=>['#fef3c7','#92400e']];
$statusColors = ['active'=>['#d1fae5','#065f46'],'prospect'=>['#fef3c7','#92400e'],'inactive'=>['#f3f4f6','#6b7280'],'former'=>['#fee2e2','#991b1b']];
$relLabels = ['supplier'=>'Supplier','customer'=>'Customer','distributor'=>'Distributor','reseller'=>'Reseller','strategic'=>'Strategic','joint_venture'=>'Joint Venture','vendor'=>'Vendor','contractor'=>'Contractor','investor'=>'Investor','other'=>'Other'];
@endphp

<div class="page">
    <div class="header-row">
        <div>
            <div class="h-title"><i data-lucide="handshake" class="lic"></i> Partner Relationships</div>
            <div class="h-sub">Manage your suppliers, customers, and strategic partners in one place</div>
        </div>
        <button class="btn-add" onclick="document.getElementById('addModal').classList.add('open')">+ Add Partner</button>
    </div>

    <div class="stats-row">
        <div class="stat-card"><div class="stat-num">{{ $stats['total'] }}</div><div class="stat-lbl">Total Partners</div></div>
        <div class="stat-card"><div class="stat-num" style="color:#16a34a;">{{ $stats['active'] }}</div><div class="stat-lbl">Active</div></div>
        <div class="stat-card"><div class="stat-num" style="color:#d97706;">{{ $stats['prospects'] }}</div><div class="stat-lbl">Prospects</div></div>
        <div class="stat-card"><div class="stat-num" style="color:#6d28d9;">{{ number_format($stats['value']/1000000,1) }}M</div><div class="stat-lbl">Est. Value (XAF)</div></div>
    </div>

    <div class="filter-tabs">
        <a class="filter-tab {{ !$filter?'active':'' }}" href="/prm">All</a>
        <a class="filter-tab {{ $filter==='active'?'active':'' }}" href="/prm?status=active">Active</a>
        <a class="filter-tab {{ $filter==='prospect'?'active':'' }}" href="/prm?status=prospect">Prospects</a>
        <a class="filter-tab {{ $filter==='inactive'?'active':'' }}" href="/prm?status=inactive">Inactive</a>
        <a class="filter-tab {{ $filter==='former'?'active':'' }}" href="/prm?status=former">Former</a>
    </div>

    @if($partners->isEmpty())
    <div style="text-align:center;padding:3rem;background:#fff;border-radius:var(--radius);border:1px solid var(--border);">
        <div style="font-size:2rem;margin-bottom:.5rem;"><i data-lucide="handshake" class="lic"></i></div>
        <div style="font-weight:700;margin-bottom:.3rem;">No partners yet</div>
        <div style="font-size:.85rem;color:var(--muted);margin-bottom:1rem;">Start building your partner network.</div>
        <button class="btn-add" onclick="document.getElementById('addModal').classList.add('open')">+ Add Your First Partner</button>
    </div>
    @else
    <div class="partner-table">
        @foreach($partners as $p)
        @php $tc = $tierColors[$p->tier]??$tierColors['standard']; $sc = $statusColors[$p->status]??$statusColors['prospect']; @endphp
        <a href="/prm/{{ $p->id }}" class="p-row">
            <div class="p-avatar">{{ strtoupper(substr($p->partner_name,0,2)) }}</div>
            <div style="flex:1;min-width:0;">
                <div class="p-name">{{ $p->partner_name }}</div>
                <div class="p-meta">{{ $relLabels[$p->relationship_type]??ucfirst($p->relationship_type) }}{{ $p->contact_name ? ' · '.$p->contact_name : '' }}</div>
            </div>
            <span class="tier-badge" style="background:{{ $tc[0] }};color:{{ $tc[1] }};">{{ ucfirst($p->tier) }}</span>
            <span class="status-badge" style="background:{{ $sc[0] }};color:{{ $sc[1] }};">{{ ucfirst($p->status) }}</span>
            <div class="p-value">{{ $p->value_estimate ? number_format($p->value_estimate/1000000,1).'M' : '—' }}</div>
        </a>
        @endforeach
    </div>
    @endif
</div>

<div class="modal-overlay" id="addModal">
    <div class="modal">
        <div style="font-weight:800;font-size:1rem;margin-bottom:1rem;"><i data-lucide="handshake" class="lic"></i> Add a Partner</div>
        <form method="POST" action="/prm">
            @csrf
            <div class="form-group"><label class="form-label">Partner Name *</label><input type="text" class="form-control" name="partner_name" required></div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Relationship Type</label><select class="form-control" name="relationship_type">@foreach($relLabels as $k=>$v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select></div>
                <div class="form-group"><label class="form-label">Status</label><select class="form-control" name="status"><option value="prospect">Prospect</option><option value="active">Active</option><option value="inactive">Inactive</option><option value="former">Former</option></select></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Tier</label><select class="form-control" name="tier"><option value="standard">Standard</option><option value="preferred">Preferred</option><option value="strategic">Strategic</option><option value="trial">Trial</option></select></div>
                <div class="form-group"><label class="form-label">Est. Value (XAF)</label><input type="number" class="form-control" name="value_estimate" min="0"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Contact Name</label><input type="text" class="form-control" name="contact_name"></div>
                <div class="form-group"><label class="form-label">Contact Phone</label><input type="text" class="form-control" name="contact_phone"></div>
            </div>
            <div class="form-group"><label class="form-label">Contact Email</label><input type="email" class="form-control" name="contact_email"></div>
            <div class="form-group"><label class="form-label">Notes</label><textarea class="form-control" name="notes" style="resize:vertical;min-height:60px;"></textarea></div>
            <div style="display:flex;gap:.6rem;justify-content:flex-end;">
                <button type="button" style="padding:.6rem 1.2rem;border:1px solid var(--border);background:#fff;border-radius:8px;font-weight:600;cursor:pointer;" onclick="document.getElementById('addModal').classList.remove('open')">Cancel</button>
                <button type="submit" style="padding:.6rem 1.5rem;background:var(--green);color:#fff;border:none;border-radius:8px;font-weight:700;cursor:pointer;">Add Partner →</button>
            </div>
        </form>
    </div>
</div>
@include('partials.footer')
</body>
</html>
