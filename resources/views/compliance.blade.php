<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Compliance Intelligence — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:1100px;margin:0 auto;padding:1.5rem;}
.hero{background:linear-gradient(135deg,#1e3a8a,#1e40af);border-radius:var(--radius);padding:2.5rem 2rem;margin-bottom:1.5rem;color:#fff;}
.hero-title{font-size:1.8rem;font-weight:900;margin-bottom:.4rem;}
.hero-sub{font-size:.9rem;color:#bfdbfe;}
.tracker{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);border:1px solid var(--border);margin-bottom:1.5rem;overflow:hidden;}
.tracker-head{padding:.9rem 1.2rem;background:var(--light-bg);border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;}
.tracker-title{font-weight:800;font-size:.95rem;}
.score-pills{display:flex;gap:.5rem;flex-wrap:wrap;}
.score-pill{padding:3px 10px;border-radius:99px;font-size:.72rem;font-weight:700;}
.tracker-row{display:flex;align-items:center;gap:.75rem;padding:.7rem 1.2rem;border-bottom:1px solid var(--border);font-size:.85rem;}
.tracker-row:last-child{border-bottom:none;}
.status-dot{width:10px;height:10px;border-radius:50%;flex-shrink:0;}
.status-badge{padding:2px 9px;border-radius:99px;font-size:.7rem;font-weight:700;margin-left:auto;}
.section-title{font-weight:800;font-size:1rem;color:var(--text);margin:1.2rem 0 .8rem;}
.cat-tabs{display:flex;gap:.4rem;flex-wrap:wrap;margin-bottom:1rem;}
.cat-tab{padding:.3rem .9rem;border-radius:99px;font-size:.78rem;font-weight:600;cursor:pointer;border:1px solid var(--border);background:#fff;color:var(--muted);transition:all .15s;}
.cat-tab.active,.cat-tab:hover{background:#1e40af;color:#fff;border-color:#1e40af;}
.req-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:1rem;}
.req-card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);border:1px solid var(--border);border-left:4px solid #1e40af;padding:1.1rem;transition:box-shadow .15s;}
.req-card:hover{box-shadow:var(--shadow-hover);}
.req-title{font-weight:800;font-size:.92rem;color:var(--text);margin-bottom:.3rem;line-height:1.3;}
.badge{display:inline-block;padding:2px 9px;border-radius:99px;font-size:.7rem;font-weight:700;border:1px solid var(--border);background:var(--light-bg);color:var(--muted);}
.req-meta{display:flex;gap:.4rem;flex-wrap:wrap;margin:.5rem 0;}
.req-desc{font-size:.8rem;color:var(--muted);line-height:1.5;}
.req-auth{font-size:.74rem;color:#1e40af;font-weight:700;margin-top:.5rem;}
</style>

@php
$authUser = webUser();
$category = request('category','');
$query = DB::table('compliance_requirements')->where('is_published',1);
if ($category) $query->where('category',$category);
$reqs = $query->orderBy('title')->get();

// Per-company tracker (if logged in and owns a company)
$tracker = collect(); $myCompanyName = null;
if ($authUser) {
    $myCo = DB::table('company_users')
        ->join('companies','company_users.company_id','=','companies.id')
        ->where('company_users.user_id',$authUser->id)->where('company_users.is_active',1)
        ->whereNull('companies.deleted_at')
        ->select('companies.id','companies.name')->first();
    if ($myCo) {
        $myCompanyName = $myCo->name;
        $tracker = DB::table('compliance_tracker')->where('company_id',$myCo->id)->orderBy('due_date')->get();
    }
}
$catLabels = ['tax'=>'Tax','labour'=>'Labour','environmental'=>'Environmental','sector_license'=>'Sector License','data_protection'=>'Data Protection','health_safety'=>'Health & Safety','customs'=>'Customs','financial'=>'Financial','corporate'=>'Corporate','intellectual_property'=>'IP','other'=>'Other'];
$catIcons = ['tax'=>'banknote','labour'=>'hard-hat','environmental'=>'trees','sector_license'=>'clipboard-list','data_protection'=>'lock','health_safety'=>'hard-hat','customs'=>'stamp','financial'=>'landmark','corporate'=>'landmark','intellectual_property'=>'™','other'=>'file-text'];
$statusColors = ['compliant'=>['#16a34a','#d1fae5','#065f46'],'in_progress'=>['#d97706','#fef3c7','#92400e'],'pending'=>['#6b7280','#f3f4f6','#374151'],'overdue'=>['#dc2626','#fef2f2','#991b1b'],'not_applicable'=>['#9ca3af','#f9fafb','#6b7280']];
$freqLabels = ['one_time'=>'One-time','monthly'=>'Monthly','quarterly'=>'Quarterly','biannual'=>'Biannual','annual'=>'Annual','as_needed'=>'As needed'];
@endphp

<div class="page">
    <div class="hero">
        <div class="hero-title"><i data-lucide="shield" class="lic"></i> Compliance Intelligence</div>
        <div class="hero-sub">Stay compliant in Cameroon — regulatory obligations, deadlines, and a tracker for your business</div>
    </div>

    @if($tracker->count() > 0)
    @php
    $compliant = $tracker->where('status','compliant')->count();
    $total = $tracker->count();
    $pct = $total > 0 ? round($compliant/$total*100) : 0;
    @endphp
    <div class="tracker">
        <div class="tracker-head">
            <div class="tracker-title"><i data-lucide="bar-chart-3" class="lic"></i> {{ $myCompanyName }} — Compliance Tracker</div>
            <div class="score-pills">
                <span class="score-pill" style="background:#d1fae5;color:#065f46;">{{ $pct }}% compliant</span>
                <span class="score-pill" style="background:#f3f4f6;color:#374151;">{{ $total }} items</span>
            </div>
        </div>
        @foreach($tracker as $t)
        @php $sc = $statusColors[$t->status] ?? $statusColors['pending']; @endphp
        <div class="tracker-row">
            <div class="status-dot" style="background:{{ $sc[0] }};"></div>
            <div style="flex:1;">
                <span style="font-weight:600;">{{ $t->title }}</span>
                @if($t->due_date)<span style="color:var(--muted);font-size:.76rem;"> · due {{ date('d M Y', strtotime($t->due_date)) }}</span>@endif
            </div>
            <span class="status-badge" style="background:{{ $sc[1] }};color:{{ $sc[2] }};">{{ ucfirst(str_replace('_',' ',$t->status)) }}</span>
        </div>
        @endforeach
    </div>
    @endif

    <div class="section-title"><i data-lucide="book-open" class="lic"></i> Regulatory Requirements Library</div>
    <div class="cat-tabs">
        <a class="cat-tab {{ !$category?'active':'' }}" href="/compliance">All</a>
        @foreach($catLabels as $k=>$v)<a class="cat-tab {{ $category===$k?'active':'' }}" href="/compliance?category={{ $k }}"><i data-lucide="{{ $catIcons[$k] }}" class="lic"></i> {{ $v }}</a>@endforeach
    </div>

    @if($reqs->isEmpty())
    <div style="text-align:center;padding:2rem;background:#fff;border-radius:var(--radius);border:1px solid var(--border);color:var(--muted);">No requirements in this category.</div>
    @else
    <div class="req-grid">
        @foreach($reqs as $r)
        <a href="/compliance/{{ $r->slug }}" class="req-card" style="display:block;">
            <div class="req-title">{{ $r->title }}</div>
            <div class="req-meta">
                <span class="badge"><i data-lucide="{{ $catIcons[$r->category]??'file-text' }}" class="lic"></i> {{ $catLabels[$r->category]??ucfirst($r->category) }}</span>
                <span class="badge"><i data-lucide="repeat" class="lic"></i> {{ $freqLabels[$r->frequency]??ucfirst($r->frequency) }}</span>
            </div>
            <div class="req-desc">{{ Str::limit($r->description,110) }}</div>
            @if($r->authority)<div class="req-auth"><i data-lucide="landmark" class="lic"></i> {{ $r->authority }}</div>@endif
        </a>
        @endforeach
    </div>
    @endif
</div>
@include('partials.footer')
</body>
</html>
