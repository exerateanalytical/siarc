<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Supplier Performance Center — Galerie virtuelle de l'artisanat du Cameroun</title>
<meta name="description" content="Supplier reviews and KPI tracking for Cameroonian businesses.">
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:1100px;margin:0 auto;padding:1.5rem;}
.hero{background:linear-gradient(135deg,#1e0a30,#3b0764);border-radius:var(--radius);padding:2.5rem 2rem;color:#fff;margin-bottom:2rem;position:relative;overflow:hidden;}
.hero::after{content:'';position:absolute;right:-40px;bottom:-40px;width:260px;height:260px;border-radius:50%;background:rgba(168,85,247,.1);}
.hero-title{font-size:1.8rem;font-weight:900;margin-bottom:.5rem;}
.hero-sub{color:#d8b4fe;font-size:.9rem;max-width:560px;}
.h-stats{display:flex;gap:2rem;margin-top:1.5rem;flex-wrap:wrap;}
.h-stat-val{font-size:1.4rem;font-weight:800;color:var(--yellow);}
.h-stat-lbl{font-size:.72rem;color:#c4b5fd;}
.filters{display:flex;gap:.6rem;flex-wrap:wrap;margin-bottom:1.2rem;align-items:center;}
.fi{padding:.42rem .85rem;border:1px solid var(--border);border-radius:7px;font-size:.83rem;outline:none;background:#fff;color:var(--text);}
.fi:focus{border-color:var(--green);}
.post-btn{padding:.42rem 1.1rem;background:var(--green);color:#fff;border:none;border-radius:7px;font-size:.83rem;font-weight:700;cursor:pointer;}
.post-btn:hover{background:#00962e;}
.supplier-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:1.1rem;}
.supplier-card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);padding:1.4rem;border:1px solid var(--border);transition:box-shadow .2s;}
.supplier-card:hover{box-shadow:0 6px 24px rgba(0,0,0,.12);}
.sc-header{display:flex;gap:.8rem;align-items:center;margin-bottom:.9rem;}
.sc-logo{width:44px;height:44px;border-radius:9px;background:linear-gradient(135deg,var(--dark),var(--mid));display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.9rem;color:var(--yellow);flex-shrink:0;}
.sc-name{font-weight:800;font-size:.93rem;color:var(--text);}
.sc-ver{font-size:.68rem;color:#166534;font-weight:600;}
.kpi-row{display:grid;grid-template-columns:repeat(5,1fr);gap:.4rem;margin-bottom:.8rem;}
.kpi-item{text-align:center;}
.kpi-score{font-size:1rem;font-weight:900;color:var(--text);}
.kpi-label{font-size:.62rem;color:var(--muted);margin-top:1px;}
.stars{color:#f59e0b;font-size:.8rem;}
.avg-score{font-size:1.3rem;font-weight:900;color:var(--green);}
.review-count{font-size:.75rem;color:var(--muted);}
.sc-footer{margin-top:.8rem;padding-top:.7rem;border-top:1px solid var(--border);display:flex;gap:.5rem;align-items:center;}
.btn-sm{padding:.32rem .8rem;border-radius:6px;font-size:.76rem;font-weight:600;display:inline-block;}
.btn-green{background:var(--green);color:#fff;}
.btn-outline{border:1px solid var(--border);color:var(--text);}
.empty{text-align:center;padding:3rem;color:var(--muted);}
.kpi-explain{display:grid;grid-template-columns:repeat(5,1fr);gap:.5rem;margin-bottom:1.5rem;}
.kpi-exp{background:#fff;border-radius:8px;padding:.7rem;border:1px solid var(--border);text-align:center;font-size:.72rem;}
.kpi-exp-icon{font-size:1.2rem;margin-bottom:.3rem;}
.modal-backdrop{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:200;align-items:center;justify-content:center;}
.modal-backdrop.show{display:flex;}
.modal{background:#fff;border-radius:12px;padding:2rem;width:100%;max-width:540px;max-height:90vh;overflow-y:auto;}
.modal-title{font-size:1.05rem;font-weight:800;margin-bottom:1.2rem;}
.form-group{margin-bottom:.9rem;}
.form-label{display:block;font-size:.8rem;font-weight:600;margin-bottom:.3rem;}
.form-control{width:100%;padding:.5rem .75rem;border:1px solid var(--border);border-radius:7px;font-size:.85rem;outline:none;font-family:inherit;box-sizing:border-box;}
.form-control:focus{border-color:var(--green);}
textarea.form-control{resize:vertical;min-height:80px;}
.star-input{display:flex;gap:.4rem;flex-direction:row-reverse;justify-content:flex-end;}
.star-input input{display:none;}
.star-input label{font-size:1.5rem;cursor:pointer;color:#d1d5db;}
.star-input input:checked~label,.star-input label:hover,.star-input label:hover~label{color:#f59e0b;}
.form-footer{display:flex;gap:.75rem;justify-content:flex-end;margin-top:1.2rem;}
.btn-cancel{padding:.5rem 1.2rem;border:1px solid var(--border);background:#fff;border-radius:7px;font-size:.85rem;font-weight:600;cursor:pointer;}
.btn-send{padding:.5rem 1.4rem;background:var(--green);color:#fff;border-radius:7px;font-size:.85rem;font-weight:700;cursor:pointer;border:none;}
@media(max-width:640px){.supplier-grid{grid-template-columns:1fr;}.kpi-explain{grid-template-columns:repeat(3,1fr);}}
</style>

@php
$authUser = session('auth_user');
$q = request('q','');
$minScore = request('min_score','');
$query = DB::table('supplier_reviews')
    ->join('companies as supplier','supplier_reviews.supplier_company_id','=','supplier.id')
    ->join('companies as reviewer','supplier_reviews.reviewer_company_id','=','reviewer.id')
    ->where('supplier_reviews.status','published')
    ->whereNull('supplier.deleted_at');
if($q) $query->where('supplier.name','like',"%$q%");
$reviews = $query->select(
    'supplier_reviews.*',
    'supplier.name as supplier_name',
    'supplier.slug as supplier_slug',
    'supplier.verification_status as supplier_ver',
    'reviewer.name as reviewer_name'
)->orderByDesc('created_at')->get();
// Group by supplier
$bySupplier = $reviews->groupBy('supplier_company_id');
$suppliers = $bySupplier->map(function($revs) {
    $first = $revs->first();
    return (object)[
        'id' => $first->supplier_company_id,
        'name' => $first->supplier_name,
        'slug' => $first->supplier_slug,
        'verified' => $first->supplier_ver === 'verified',
        'review_count' => $revs->count(),
        'avg_delivery' => round($revs->avg('score_delivery'),1),
        'avg_quality' => round($revs->avg('score_quality'),1),
        'avg_communication' => round($revs->avg('score_communication'),1),
        'avg_pricing' => round($revs->avg('score_pricing'),1),
        'avg_overall' => round($revs->avg('score_overall'),1),
        'avg_total' => round(($revs->avg('score_delivery')+$revs->avg('score_quality')+$revs->avg('score_communication')+$revs->avg('score_pricing')+$revs->avg('score_overall'))/5,1),
        'recommend_pct' => $revs->count() > 0 ? round($revs->where('would_recommend',1)->count()/$revs->count()*100) : 0,
    ];
})->sortByDesc('avg_total');
if($minScore) $suppliers = $suppliers->filter(fn($s) => $s->avg_total >= (float)$minScore);
$myCompanies = $authUser ? DB::table('company_users')
    ->join('companies','company_users.company_id','=','companies.id')
    ->where('company_users.user_id',$authUser['id'])
    ->where('company_users.status','approved')
    ->whereNull('companies.deleted_at')
    ->select('companies.id','companies.name')->get() : collect();
$allCompanies = DB::table('companies')->whereNull('deleted_at')->whereNotIn('id',$myCompanies->pluck('id')->toArray())->orderBy('name')->select('id','name')->limit(200)->get();
$renderStars = fn($score) => str_repeat('★', round($score)) . str_repeat('☆', 5 - round($score));
@endphp

<div class="page">
    <div class="hero">
        <div style="position:relative;z-index:1;">
            <div style="display:inline-block;background:rgba(168,85,247,.2);border:1px solid rgba(168,85,247,.4);color:#d8b4fe;padding:3px 12px;border-radius:99px;font-size:.72rem;font-weight:700;margin-bottom:.7rem;">SUPPLIER PERFORMANCE</div>
            <div class="hero-title">Supplier Performance Center</div>
            <div class="hero-sub">Verified reviews from real business relationships. Track delivery, quality, communication, pricing, and overall performance across your supply chain.</div>
            <div class="h-stats">
                <div><div class="h-stat-val">{{ $reviews->count() }}</div><div class="h-stat-lbl">Reviews Published</div></div>
                <div><div class="h-stat-val">{{ $suppliers->count() }}</div><div class="h-stat-lbl">Rated Suppliers</div></div>
                <div><div class="h-stat-val">{{ $reviews->count() > 0 ? round($reviews->avg('score_overall'),1) : '—' }}</div><div class="h-stat-lbl">Avg. Score</div></div>
            </div>
        </div>
    </div>

    <div class="kpi-explain">
        <div class="kpi-exp"><div class="kpi-exp-icon"><i data-lucide="truck" class="lic"></i></div><strong>Delivery</strong><br>On-time delivery rate</div>
        <div class="kpi-exp"><div class="kpi-exp-icon"><i data-lucide="star" class="lic"></i></div><strong>Quality</strong><br>Product/service quality</div>
        <div class="kpi-exp"><div class="kpi-exp-icon"><i data-lucide="message-circle" class="lic"></i></div><strong>Communication</strong><br>Responsiveness & clarity</div>
        <div class="kpi-exp"><div class="kpi-exp-icon"><i data-lucide="banknote" class="lic"></i></div><strong>Pricing</strong><br>Value for money</div>
        <div class="kpi-exp"><div class="kpi-exp-icon"><i data-lucide="target" class="lic"></i></div><strong>Overall</strong><br>General satisfaction</div>
    </div>

    <form method="GET" action="/supplier-reviews">
        <div class="filters">
            <input class="fi" type="text" name="q" value="{{ $q }}" placeholder="Search supplier name…" style="min-width:200px;">
            <select class="fi" name="min_score" onchange="this.form.submit()">
                <option value="">Any score</option>
                <option value="4" {{ $minScore==='4'?'selected':'' }}>4+ stars</option>
                <option value="3" {{ $minScore==='3'?'selected':'' }}>3+ stars</option>
            </select>
            <button type="submit" class="post-btn" style="background:var(--mid);">Search</button>
            @if($authUser && $myCompanies->count() > 0)
                <button type="button" class="post-btn" onclick="document.getElementById('reviewModal').classList.add('show')">+ Write Review</button>
            @endif
        </div>
    </form>

    @if($suppliers->isEmpty())
        <div class="empty">No supplier reviews yet. {{ $authUser ? '' : 'Sign in to' }} Write the first review!</div>
    @else
        <div class="supplier-grid">
        @foreach($suppliers as $s)
            <div class="supplier-card">
                <div class="sc-header">
                    <div class="sc-logo">{{ strtoupper(substr($s->name,0,2)) }}</div>
                    <div>
                        <div class="sc-name"><a href="/companies/{{ $s->slug }}" style="color:var(--text);">{{ $s->name }}</a></div>
                        @if($s->verified)<div class="sc-ver"><i data-lucide="check" class="lic"></i> Verified Supplier</div>@endif
                        <div style="margin-top:3px;display:flex;align-items:center;gap:.4rem;">
                            <span class="avg-score">{{ $s->avg_total }}</span>
                            <span class="stars">{{ str_repeat('★',round($s->avg_total)).str_repeat('☆',5-round($s->avg_total)) }}</span>
                            <span class="review-count">({{ $s->review_count }} review{{ $s->review_count!=1?'s':'' }})</span>
                        </div>
                    </div>
                </div>
                <div class="kpi-row">
                    @foreach([['truck','Delivery',$s->avg_delivery],['star','Quality',$s->avg_quality],['message-circle','Comms',$s->avg_communication],['banknote','Pricing',$s->avg_pricing],['target','Overall',$s->avg_overall]] as [$ico,$lbl,$val])
                    <div class="kpi-item">
                        <div class="kpi-score">{{ $val }}</div>
                        <div style="font-size:.75rem;color:#f59e0b;">{{ $ico }}</div>
                        <div class="kpi-label">{{ $lbl }}</div>
                    </div>
                    @endforeach
                </div>
                <div style="font-size:.75rem;color:var(--muted);"><i data-lucide="thumbs-up" class="lic"></i> {{ $s->recommend_pct }}% would recommend</div>
                <div class="sc-footer">
                    <a href="/companies/{{ $s->slug }}" class="btn-sm btn-green">View Company</a>
                    @if($authUser && $myCompanies->count() > 0)
                        <button type="button" class="btn-sm btn-outline" onclick="document.getElementById('reviewModal').classList.add('show');document.getElementById('review_supplier_id').value='{{ $s->id }}';">Review</button>
                    @endif
                </div>
            </div>
        @endforeach
        </div>
    @endif
</div>

@if($authUser && $myCompanies->count() > 0)
<div class="modal-backdrop" id="reviewModal">
    <div class="modal">
        <div class="modal-title">Write a Supplier Review</div>
        <form method="POST" action="/supplier-reviews">
            @csrf
            <div class="form-group">
                <label class="form-label">Your Company</label>
                <select class="form-control" name="reviewer_company_id" required>
                    @foreach($myCompanies as $mc)<option value="{{ $mc->id }}">{{ $mc->name }}</option>@endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Supplier Being Reviewed</label>
                <select class="form-control" name="supplier_company_id" id="review_supplier_id" required>
                    <option value="">Select supplier…</option>
                    @foreach($allCompanies as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                </select>
            </div>
            @foreach([['score_delivery','<i data-lucide="truck" class="lic"></i> On-Time Delivery'],['score_quality','<i data-lucide="star" class="lic"></i> Quality'],['score_communication','<i data-lucide="message-circle" class="lic"></i> Communication'],['score_pricing','<i data-lucide="banknote" class="lic"></i> Pricing'],['score_overall','<i data-lucide="target" class="lic"></i> Overall Satisfaction']] as [$field,$label])
            <div class="form-group">
                <label class="form-label">{{ $label }}</label>
                <div class="star-input">
                    @for($i=5;$i>=1;$i--)
                        <input type="radio" name="{{ $field }}" id="{{ $field }}_{{ $i }}" value="{{ $i }}" required>
                        <label for="{{ $field }}_{{ $i }}">★</label>
                    @endfor
                </div>
            </div>
            @endforeach
            <div class="form-group">
                <label class="form-label">Review (optional)</label>
                <textarea class="form-control" name="review_text" placeholder="Describe your experience working with this supplier…"></textarea>
            </div>
            <div class="form-group" style="display:flex;align-items:center;gap:.5rem;">
                <input type="checkbox" name="would_recommend" id="recommend" value="1" checked style="width:auto;">
                <label for="recommend" style="font-size:.85rem;font-weight:600;cursor:pointer;">I would recommend this supplier</label>
            </div>
            <div class="form-footer">
                <button type="button" class="btn-cancel" onclick="document.getElementById('reviewModal').classList.remove('show')">Cancel</button>
                <button type="submit" class="btn-send">Submit Review →</button>
            </div>
        </form>
    </div>
</div>
@endif

@include('partials.footer')
</body>
</html>
