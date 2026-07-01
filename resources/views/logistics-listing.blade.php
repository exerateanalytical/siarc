<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>{{ $listing->title }} — Logistics Exchange — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:1000px;margin:0 auto;padding:1.5rem;}
.grid2{display:grid;grid-template-columns:1fr 290px;gap:1.5rem;}
.card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);border:1px solid var(--border);margin-bottom:1rem;}
.card-body{padding:1.3rem;}
.card-head{padding:.75rem 1.1rem;font-weight:700;font-size:.88rem;border-bottom:1px solid var(--border);background:var(--light-bg);}
.route-big{display:flex;align-items:center;gap:.8rem;margin-bottom:.8rem;}
.route-city-big{font-size:1.3rem;font-weight:900;color:var(--text);}
.route-arrow-big{color:#0284c7;font-size:1.5rem;}
.type-pill{display:inline-block;padding:3px 12px;border-radius:99px;font-size:.72rem;font-weight:800;}
.pill-load{background:#fef3c7;color:#92400e;}
.pill-capacity{background:#d1fae5;color:#065f46;}
.info-grid{display:grid;grid-template-columns:1fr 1fr;gap:.6rem;margin:.9rem 0;}
.info-item{padding:.6rem .8rem;background:var(--light-bg);border-radius:8px;}
.info-lbl{font-size:.68rem;color:var(--muted);font-weight:600;margin-bottom:2px;}
.info-val{font-size:.88rem;font-weight:700;color:var(--text);}
.section-title{font-weight:700;font-size:.9rem;color:var(--text);margin:.9rem 0 .4rem;}
.desc-text{font-size:.87rem;color:var(--text);line-height:1.7;}
.bid-box{border:2px solid #0284c7;border-radius:var(--radius);padding:1.3rem;background:#fff;margin-bottom:1rem;}
.price-tag{font-size:1.4rem;font-weight:900;color:#0284c7;margin-bottom:.5rem;}
.form-group{margin-bottom:.75rem;}
.form-label{display:block;font-size:.8rem;font-weight:600;margin-bottom:.3rem;}
.form-control{width:100%;padding:.45rem .75rem;border:1px solid var(--border);border-radius:7px;font-size:.85rem;outline:none;font-family:inherit;box-sizing:border-box;}
.form-control:focus{border-color:#0284c7;}
textarea.form-control{resize:vertical;min-height:75px;}
.btn-bid{width:100%;padding:.6rem;background:#0284c7;color:#fff;border:none;border-radius:8px;font-size:.9rem;font-weight:700;cursor:pointer;}
.bid-row{padding:.6rem 0;border-bottom:1px solid var(--border);}
.bid-row:last-child{border-bottom:none;}
@media(max-width:700px){.grid2{grid-template-columns:1fr;}.info-grid{grid-template-columns:1fr;}}
</style>

@php
$cargoIcons = ['general'=>'package','perishable'=>'leaf','refrigerated'=>'snowflake','hazardous'=>'radiation','bulk'=>'wheat','liquid'=>'fuel','livestock'=>'beef','containers'=>'ship','oversized'=>'hard-hat','fragile'=>'bell','other'=>'package'];
$vehicleLabels = ['van'=>'Van','pickup'=>'Pickup','truck_small'=>'Small Truck','truck_medium'=>'Medium Truck','truck_large'=>'Large Truck','trailer'=>'Trailer','refrigerated_truck'=>'Reefer Truck','tanker'=>'Tanker','flatbed'=>'Flatbed','container_truck'=>'Container Truck','any'=>'Any Vehicle'];
$isLoad = $listing->type === 'load';
@endphp

<div class="page">
    <a href="/logistics?type={{ $listing->type }}" style="font-size:.82rem;color:var(--muted);display:inline-flex;gap:.3rem;margin-bottom:1rem;">← Logistics Exchange</a>
    <div class="grid2">
        <div>
            <div class="card">
                <div class="card-body">
                    <span class="type-pill {{ $isLoad?'pill-load':'pill-capacity' }}">{{ $isLoad?'<i data-lucide="package" class="lic"></i> LOAD NEEDING TRANSPORT':'<i data-lucide="truck" class="lic"></i> CAPACITY AVAILABLE' }}</span>
                    <div class="route-big" style="margin-top:.7rem;">
                        <div class="route-city-big">{{ $listing->origin_city }}</div>
                        <div class="route-arrow-big">→</div>
                        <div class="route-city-big">{{ $listing->destination_city }}</div>
                    </div>
                    <div style="font-size:1.05rem;font-weight:800;color:var(--text);margin-bottom:.3rem;">{{ $listing->title }}</div>
                    @if($company)<div style="font-size:.82rem;color:var(--muted);margin-bottom:.5rem;">Posted by <a href="/companies/{{ $company->slug }}" style="color:#0284c7;font-weight:600;">{{ $company->name }}</a></div>@endif
                    <div class="info-grid">
                        <div class="info-item"><div class="info-lbl">Cargo Type</div><div class="info-val"><i data-lucide="{{ $cargoIcons[$listing->cargo_type]??'package' }}" class="lic"></i> {{ ucfirst(str_replace('_',' ',$listing->cargo_type)) }}</div></div>
                        <div class="info-item"><div class="info-lbl">Vehicle</div><div class="info-val">{{ $vehicleLabels[$listing->vehicle_type]??'Any' }}</div></div>
                        @if($listing->weight_kg)<div class="info-item"><div class="info-lbl">Weight</div><div class="info-val">{{ number_format($listing->weight_kg/1000,1) }} tonnes</div></div>@endif
                        @if($listing->volume_m3)<div class="info-item"><div class="info-lbl">Volume</div><div class="info-val">{{ $listing->volume_m3 }} m³</div></div>@endif
                        @if($listing->available_date)<div class="info-item"><div class="info-lbl">{{ $isLoad?'Pickup Date':'Available From' }}</div><div class="info-val">{{ date('d M Y', strtotime($listing->available_date)) }}</div></div>@endif
                        <div class="info-item"><div class="info-lbl">Views</div><div class="info-val">{{ number_format($listing->view_count+1) }}</div></div>
                    </div>
                    @if($listing->description)
                    <div class="section-title">Details</div>
                    <div class="desc-text">{{ $listing->description }}</div>
                    @endif
                </div>
            </div>

            @if($bids->count() > 0 && $isOwner)
            <div class="card">
                <div class="card-head">{{ $isLoad?'Carrier Bids':'Booking Requests' }} ({{ $bids->count() }})</div>
                <div class="card-body">
                    @foreach($bids as $bid)
                    <div class="bid-row">
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <span style="font-weight:700;font-size:.85rem;">{{ ($bid->first_name??'').' '.($bid->last_name??'') }}{{ $bid->company_name ? ' · '.$bid->company_name : '' }}</span>
                            @if($bid->bid_amount)<span style="font-weight:800;color:#0284c7;">{{ number_format($bid->bid_amount) }} XAF</span>@endif
                        </div>
                        @if($bid->message)<div style="font-size:.8rem;color:var(--muted);margin-top:.3rem;">{{ $bid->message }}</div>@endif
                        @if($bid->user_id)<a href="/messages/{{ $bid->user_id }}" style="display:inline-block;margin-top:.3rem;font-size:.74rem;color:var(--green);font-weight:700;"><i data-lucide="message-circle" class="lic"></i> Message</a>@endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <div>
            @if($alreadyBid)
            <div class="bid-box" style="border-color:var(--muted);">
                <div style="text-align:center;padding:.4rem 0;">
                    <div style="font-size:1.5rem;margin-bottom:.4rem;"><i data-lucide="check" class="lic"></i></div>
                    <div style="font-weight:700;">{{ $isLoad?'Bid Submitted!':'Booking Requested!' }}</div>
                    <div style="font-size:.8rem;color:var(--muted);margin-top:.3rem;">The poster will review and respond.</div>
                </div>
            </div>
            @elseif($authUser && !$isOwner && $listing->status==='open')
            <div class="bid-box">
                @if($listing->price)<div class="price-tag">{{ number_format($listing->price) }} {{ $listing->currency }}</div>@else<div style="font-weight:800;margin-bottom:.6rem;">Price Negotiable</div>@endif
                <div style="font-weight:800;font-size:.92rem;margin-bottom:.7rem;">{{ $isLoad?'Submit a Carrier Bid':'Request Booking' }}</div>
                <form method="POST" action="/logistics/{{ $listing->id }}/bid">
                    @csrf
                    @if($myCompanies->count() > 0)
                    <div class="form-group"><label class="form-label">As</label>
                        <select class="form-control" name="company_id"><option value="">Individual</option>@foreach($myCompanies as $mc)<option value="{{ $mc->id }}">{{ $mc->name }}</option>@endforeach</select>
                    </div>
                    @endif
                    <div class="form-group"><label class="form-label">Your {{ $isLoad?'Bid':'Offer' }} (XAF)</label><input type="number" class="form-control" name="bid_amount" min="0" placeholder="{{ $listing->price ?: 'Your price' }}"></div>
                    <div class="form-group"><label class="form-label">Message</label><textarea class="form-control" name="message" required placeholder="Introduce yourself and your capacity…"></textarea></div>
                    <button type="submit" class="btn-bid">{{ $isLoad?'Submit Bid →':'Request Booking →' }}</button>
                </form>
            </div>
            @elseif($isOwner)
            <div class="bid-box" style="border-color:var(--muted);">
                @if($listing->price)<div class="price-tag">{{ number_format($listing->price) }} {{ $listing->currency }}</div>@endif
                <div style="font-size:.85rem;color:var(--text);text-align:center;">This is your listing. Review {{ $isLoad?'bids':'booking requests' }} on the left.</div>
            </div>
            @elseif(!$authUser)
            <div class="bid-box" style="border-color:var(--muted);">
                <div style="font-size:.85rem;margin-bottom:.75rem;">Sign in to {{ $isLoad?'bid on this load':'book this capacity' }}.</div>
                <a href="/auth/login" class="btn-bid" style="display:block;text-align:center;text-decoration:none;">Sign In →</a>
            </div>
            @endif

            @if($listing->contact_phone)
            <div style="background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);padding:1.1rem;border:1px solid var(--border);">
                <div style="font-size:.72rem;color:var(--muted);font-weight:600;margin-bottom:.3rem;"><i data-lucide="phone" class="lic"></i> Direct Contact</div>
                <div style="font-weight:700;font-size:.95rem;color:var(--text);">{{ $listing->contact_phone }}</div>
            </div>
            @endif
        </div>
    </div>
</div>
@include('partials.footer')
</body>
</html>
