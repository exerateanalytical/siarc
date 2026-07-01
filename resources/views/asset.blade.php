<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>{{ $asset->title }} — Asset Sharing — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:1000px;margin:0 auto;padding:1.5rem;}
.grid2{display:grid;grid-template-columns:1fr 290px;gap:1.5rem;}
.card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);border:1px solid var(--border);margin-bottom:1rem;}
.card-body{padding:1.3rem;}
.card-head{padding:.75rem 1.1rem;font-weight:700;font-size:.88rem;border-bottom:1px solid var(--border);background:var(--light-bg);}
.asset-hero{height:140px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:4rem;background:linear-gradient(135deg,#fff7ed,#ffedd5);margin-bottom:1rem;}
.badge{display:inline-block;padding:3px 12px;border-radius:99px;font-size:.75rem;font-weight:700;background:var(--light-bg);color:var(--muted);border:1px solid var(--border);}
.badge-avail{background:#d1fae5;color:#065f46;border-color:#6ee7b7;}
.asset-title{font-size:1.4rem;font-weight:900;margin:.5rem 0;}
.info-grid{display:grid;grid-template-columns:1fr 1fr;gap:.6rem;margin:.9rem 0;}
.info-item{padding:.6rem .8rem;background:var(--light-bg);border-radius:8px;}
.info-lbl{font-size:.68rem;color:var(--muted);font-weight:600;margin-bottom:2px;}
.info-val{font-size:.88rem;font-weight:700;color:var(--text);}
.section-title{font-weight:700;font-size:.9rem;color:var(--text);margin:.9rem 0 .4rem;}
.desc-text{font-size:.87rem;color:var(--text);line-height:1.7;}
.inq-box{border:2px solid #c2410c;border-radius:var(--radius);padding:1.3rem;background:#fff;margin-bottom:1rem;}
.price-tag{font-size:1.5rem;font-weight:900;color:#c2410c;margin-bottom:.2rem;}
.price-unit{font-size:.78rem;color:var(--muted);font-weight:600;margin-bottom:.7rem;}
.form-group{margin-bottom:.75rem;}
.form-label{display:block;font-size:.8rem;font-weight:600;margin-bottom:.3rem;}
.form-control{width:100%;padding:.45rem .75rem;border:1px solid var(--border);border-radius:7px;font-size:.85rem;outline:none;font-family:inherit;box-sizing:border-box;}
.form-control:focus{border-color:#c2410c;}
textarea.form-control{resize:vertical;min-height:70px;}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:.5rem;}
.btn-inq{width:100%;padding:.6rem;background:#c2410c;color:#fff;border:none;border-radius:8px;font-size:.9rem;font-weight:700;cursor:pointer;}
.inq-row{padding:.6rem 0;border-bottom:1px solid var(--border);font-size:.83rem;}
.inq-row:last-child{border-bottom:none;}
@media(max-width:700px){.grid2{grid-template-columns:1fr;}.info-grid,.form-row{grid-template-columns:1fr;}}
</style>

@php
$catLabels = ['equipment'=>'Equipment','machinery'=>'Machinery','vehicle'=>'Vehicle','warehouse'=>'Warehouse','office_space'=>'Office Space','land'=>'Land','cold_storage'=>'Cold Storage','lab'=>'Lab','tools'=>'Tools','generator'=>'Generator','event_space'=>'Event Space','other'=>'Other'];
$catIcons = ['equipment'=>'settings','machinery'=>'hard-hat','vehicle'=>'truck','warehouse'=>'factory','office_space'=>'building-2','land'=>'globe','cold_storage'=>'snowflake','lab'=>'microscope','tools'=>'wrench','generator'=>'zap','event_space'=>'tent','other'=>'package'];
$pricingLabels = ['hourly'=>'per hour','daily'=>'per day','weekly'=>'per week','monthly'=>'per month','per_use'=>'per use','negotiable'=>'negotiable'];
@endphp

<div class="page">
    <a href="/assets" style="font-size:.82rem;color:var(--muted);display:inline-flex;gap:.3rem;margin-bottom:1rem;">← Asset Sharing</a>
    <div class="grid2">
        <div>
            <div class="card">
                <div class="card-body">
                    <div class="asset-hero"><i data-lucide="{{ $catIcons[$asset->category]??'package' }}" class="lic"></i></div>
                    <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
                        <span class="badge">{{ $catLabels[$asset->category]??ucfirst($asset->category) }}</span>
                        <span class="badge {{ $asset->availability==='available'?'badge-avail':'' }}">{{ ucfirst($asset->availability) }}</span>
                    </div>
                    <div class="asset-title">{{ $asset->title }}</div>
                    @if($company)<div style="font-size:.82rem;color:var(--muted);margin-bottom:.5rem;">Offered by <a href="/companies/{{ $company->slug }}" style="color:#c2410c;font-weight:600;">{{ $company->name }}</a></div>@endif
                    <div class="info-grid">
                        @if($asset->capacity_spec)<div class="info-item"><div class="info-lbl">Capacity / Spec</div><div class="info-val">{{ $asset->capacity_spec }}</div></div>@endif
                        @if($asset->condition)<div class="info-item"><div class="info-lbl">Condition</div><div class="info-val">{{ $asset->condition }}</div></div>@endif
                        @if($asset->location_city)<div class="info-item"><div class="info-lbl">Location</div><div class="info-val">{{ $asset->location_city }}, {{ $asset->location_country }}</div></div>@endif
                        <div class="info-item"><div class="info-lbl">Views</div><div class="info-val">{{ number_format($asset->view_count+1) }}</div></div>
                    </div>
                    @if($asset->description)
                    <div class="section-title">Description</div>
                    <div class="desc-text">{{ $asset->description }}</div>
                    @endif
                </div>
            </div>

            @if($inquiries->count() > 0 && $isOwner)
            <div class="card">
                <div class="card-head">Inquiries ({{ $inquiries->count() }})</div>
                <div class="card-body">
                    @foreach($inquiries as $inq)
                    <div class="inq-row">
                        <div style="display:flex;justify-content:space-between;">
                            <span style="font-weight:700;">{{ ($inq->first_name??'').' '.($inq->last_name??'') }}{{ $inq->company_name ? ' · '.$inq->company_name : '' }}</span>
                            @if($inq->start_date)<span style="color:var(--muted);font-size:.76rem;">{{ date('d M', strtotime($inq->start_date)) }}{{ $inq->end_date ? ' – '.date('d M', strtotime($inq->end_date)) : '' }}</span>@endif
                        </div>
                        @if($inq->message)<div style="font-size:.8rem;color:var(--muted);margin-top:.3rem;">{{ $inq->message }}</div>@endif
                        @if($inq->user_id)<a href="/messages/{{ $inq->user_id }}" style="display:inline-block;margin-top:.3rem;font-size:.74rem;color:var(--green);font-weight:700;"><i data-lucide="message-circle" class="lic"></i> Message</a>@endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <div>
            @if($alreadyInquired)
            <div class="inq-box" style="border-color:var(--muted);">
                <div style="text-align:center;padding:.4rem 0;">
                    <div style="font-size:1.5rem;margin-bottom:.4rem;"><i data-lucide="check" class="lic"></i></div>
                    <div style="font-weight:700;">Inquiry Sent!</div>
                    <div style="font-size:.8rem;color:var(--muted);margin-top:.3rem;">The owner will respond to your request.</div>
                </div>
            </div>
            @elseif($authUser && !$isOwner && $asset->availability==='available')
            <div class="inq-box">
                @if($asset->price)<div class="price-tag">{{ number_format($asset->price) }} {{ $asset->currency }}</div><div class="price-unit">{{ $pricingLabels[$asset->pricing_model]??'' }}</div>@else<div style="font-weight:800;margin-bottom:.6rem;">Price Negotiable</div>@endif
                <div style="font-weight:800;font-size:.92rem;margin-bottom:.7rem;">Request to Rent / Use</div>
                <form method="POST" action="/assets/{{ $asset->slug }}/inquire">
                    @csrf
                    @if($myCompanies->count() > 0)
                    <div class="form-group"><label class="form-label">As</label>
                        <select class="form-control" name="company_id"><option value="">Individual</option>@foreach($myCompanies as $mc)<option value="{{ $mc->id }}">{{ $mc->name }}</option>@endforeach</select>
                    </div>
                    @endif
                    <div class="form-row">
                        <div class="form-group"><label class="form-label">From</label><input type="date" class="form-control" name="start_date"></div>
                        <div class="form-group"><label class="form-label">To</label><input type="date" class="form-control" name="end_date"></div>
                    </div>
                    <div class="form-group"><label class="form-label">Message</label><textarea class="form-control" name="message" required placeholder="Describe your needs and timing…"></textarea></div>
                    <button type="submit" class="btn-inq">Send Inquiry →</button>
                </form>
            </div>
            @elseif($isOwner)
            <div class="inq-box" style="border-color:var(--muted);">
                @if($asset->price)<div class="price-tag">{{ number_format($asset->price) }} {{ $asset->currency }}</div><div class="price-unit">{{ $pricingLabels[$asset->pricing_model]??'' }}</div>@endif
                <div style="font-size:.85rem;color:var(--text);text-align:center;">This is your asset. Review inquiries on the left.</div>
            </div>
            @elseif(!$authUser)
            <div class="inq-box" style="border-color:var(--muted);">
                <div style="font-size:.85rem;margin-bottom:.75rem;">Sign in to inquire about this asset.</div>
                <a href="/auth/login" class="btn-inq" style="display:block;text-align:center;text-decoration:none;">Sign In →</a>
            </div>
            @endif

            @if($asset->contact_phone)
            <div style="background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);padding:1.1rem;border:1px solid var(--border);">
                <div style="font-size:.72rem;color:var(--muted);font-weight:600;margin-bottom:.3rem;"><i data-lucide="phone" class="lic"></i> Direct Contact</div>
                <div style="font-weight:700;font-size:.95rem;color:var(--text);">{{ $asset->contact_phone }}</div>
            </div>
            @endif
        </div>
    </div>
</div>
@include('partials.footer')
</body>
</html>
