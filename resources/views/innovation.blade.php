<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Innovation Hub — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:1100px;margin:0 auto;padding:1.5rem;}
.hero{background:linear-gradient(135deg,#312e81,#6d28d9);border-radius:var(--radius);padding:2.5rem 2rem;margin-bottom:1.5rem;color:#fff;}
.hero-title{font-size:1.8rem;font-weight:900;margin-bottom:.4rem;}
.hero-sub{font-size:.9rem;color:#c7d2fe;margin-bottom:1.2rem;}
.btn-white{padding:.55rem 1.3rem;background:#fff;color:#312e81;border-radius:8px;font-weight:700;font-size:.88rem;border:none;cursor:pointer;}
.btn-outline{padding:.55rem 1.3rem;border:1px solid rgba(255,255,255,.3);color:#fff;border-radius:8px;font-weight:600;font-size:.88rem;cursor:pointer;background:none;text-decoration:none;}
.cat-tabs{display:flex;gap:.4rem;flex-wrap:wrap;margin-bottom:1.2rem;}
.cat-tab{padding:.3rem .9rem;border-radius:99px;font-size:.78rem;font-weight:600;cursor:pointer;border:1px solid var(--border);background:#fff;color:var(--muted);transition:all .15s;}
.cat-tab.active,.cat-tab:hover{background:#312e81;color:#fff;border-color:#312e81;}
.proj-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(330px,1fr));gap:1rem;}
.proj-card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);border:1px solid var(--border);overflow:hidden;transition:box-shadow .15s;display:flex;flex-direction:column;}
.proj-card:hover{box-shadow:var(--shadow-hover);}
.proj-top{padding:1.1rem;border-bottom:1px solid var(--border);}
.proj-badges{display:flex;gap:.4rem;flex-wrap:wrap;margin-bottom:.5rem;}
.badge{display:inline-block;padding:2px 9px;border-radius:99px;font-size:.7rem;font-weight:700;border:1px solid var(--border);background:var(--light-bg);color:var(--muted);}
.badge-type{background:#ede9fe;color:#5b21b6;border-color:#c4b5fd;}
.badge-stage{background:#dbeafe;color:#1e40af;border-color:#93c5fd;}
.proj-title{font-weight:800;font-size:1rem;color:var(--text);margin-bottom:.3rem;line-height:1.3;}
.proj-body{padding:1rem 1.1rem;flex:1;}
.proj-desc{font-size:.82rem;color:var(--muted);line-height:1.55;margin-bottom:.7rem;}
.proj-looking{font-size:.76rem;color:var(--text);background:var(--light-bg);border-radius:7px;padding:.5rem .65rem;border-left:3px solid #6d28d9;}
.proj-foot{padding:.75rem 1.1rem;background:var(--light-bg);border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;}
.proj-prize{font-weight:800;color:#6d28d9;font-size:.85rem;}
.tag-chip{display:inline-block;font-size:.66rem;color:var(--muted);background:#fff;border:1px solid var(--border);border-radius:5px;padding:1px 6px;margin-right:3px;}
/* modal */
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:200;align-items:center;justify-content:center;}
.modal-overlay.open{display:flex;}
.modal{background:#fff;border-radius:var(--radius);padding:1.5rem;width:min(520px,95vw);max-height:90vh;overflow-y:auto;}
.form-group{margin-bottom:.85rem;}
.form-label{display:block;font-size:.8rem;font-weight:600;margin-bottom:.3rem;}
.form-control{width:100%;padding:.45rem .75rem;border:1px solid var(--border);border-radius:7px;font-size:.85rem;outline:none;font-family:inherit;box-sizing:border-box;}
.form-control:focus{border-color:#6d28d9;}
textarea.form-control{resize:vertical;min-height:75px;}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:.6rem;}
@media(max-width:600px){.form-row{grid-template-columns:1fr;}}
</style>

@php
$authUser = webUser();
$type   = request('type','');
$q      = request('q','');
$query = DB::table('innovation_projects')
    ->leftJoin('companies','innovation_projects.company_id','=','companies.id')
    ->select('innovation_projects.*','companies.name as company_name','companies.slug as company_slug');
if ($type) $query->where('innovation_projects.type',$type);
if ($q)    $query->where('innovation_projects.title','like',"%$q%");
$projects = $query->orderByDesc('innovation_projects.created_at')->get();

$typeLabels = ['research'=>'Joint Research','patent'=>'Patent','hackathon'=>'Hackathon','challenge'=>'Challenge','prototype'=>'Prototype','open_innovation'=>'Open Innovation','grant'=>'Grant','spinoff'=>'Spinoff','other'=>'Other'];
$typeIcons  = ['research'=>'microscope','patent'=>'scroll','hackathon'=>'laptop','challenge'=>'trophy','prototype'=>'wrench','open_innovation'=>'globe','grant'=>'banknote','spinoff'=>'rocket','other'=>'lightbulb'];
$stageLabels = ['idea'=>'Idea','prototype'=>'Prototype','pilot'=>'Pilot','scaling'=>'Scaling','market_ready'=>'Market Ready'];
@endphp

<div class="page">
    <div class="hero">
        <div class="hero-title"><i data-lucide="lightbulb" class="lic"></i> Innovation Hub</div>
        <div class="hero-sub">Joint research, patents, hackathons &amp; innovation challenges — where Cameroon builds the future together</div>
        @if($authUser)
        <button class="btn-white" onclick="document.getElementById('postModal').classList.add('open')">+ Post a Project</button>
        @else
        <a href="/auth/login" class="btn-outline">Sign In to Post</a>
        @endif
    </div>

    <div class="cat-tabs">
        <a class="cat-tab {{ !$type?'active':'' }}" href="/innovation">All</a>
        @foreach($typeLabels as $k=>$v)<a class="cat-tab {{ $type===$k?'active':'' }}" href="/innovation?type={{ $k }}"><i data-lucide="{{ $typeIcons[$k] }}" class="lic"></i> {{ $v }}</a>@endforeach
    </div>

    <div style="display:flex;gap:.5rem;margin-bottom:1.2rem;">
        <form method="GET" action="/innovation" style="display:flex;gap:.5rem;flex:1;flex-wrap:wrap;">
            <input type="text" name="q" value="{{ $q }}" placeholder="Search projects…" style="flex:1;min-width:200px;padding:.45rem .8rem;border:1px solid var(--border);border-radius:7px;font-size:.85rem;outline:none;">
            @if($type)<input type="hidden" name="type" value="{{ $type }}">@endif
            <button type="submit" style="padding:.45rem 1.1rem;background:#6d28d9;color:#fff;border:none;border-radius:7px;font-size:.83rem;font-weight:600;cursor:pointer;">Search</button>
        </form>
        <span style="font-size:.78rem;color:var(--muted);align-self:center;white-space:nowrap;">{{ $projects->count() }} projects</span>
    </div>

    @if($projects->isEmpty())
    <div style="text-align:center;padding:3rem;background:#fff;border-radius:var(--radius);border:1px solid var(--border);">
        <div style="font-size:2rem;margin-bottom:.5rem;"><i data-lucide="lightbulb" class="lic"></i></div>
        <div style="font-weight:700;margin-bottom:.3rem;">No projects found</div>
        <div style="font-size:.85rem;color:var(--muted);">Be the first to post an innovation project.</div>
    </div>
    @else
    <div class="proj-grid">
        @foreach($projects as $p)
        <div class="proj-card">
            <div class="proj-top">
                <div class="proj-badges">
                    <span class="badge badge-type"><i data-lucide="{{ $typeIcons[$p->type]??'lightbulb' }}" class="lic"></i> {{ $typeLabels[$p->type]??ucfirst($p->type) }}</span>
                    <span class="badge badge-stage">{{ $stageLabels[$p->stage]??ucfirst($p->stage) }}</span>
                    <span class="badge">{{ ucfirst($p->sector) }}</span>
                </div>
                <a href="/innovation/{{ $p->slug }}" style="color:var(--text);"><div class="proj-title">{{ $p->title }}</div></a>
                @if($p->company_name)<div style="font-size:.75rem;color:var(--muted);">by <a href="/companies/{{ $p->company_slug }}" style="color:#6d28d9;font-weight:600;">{{ $p->company_name }}</a></div>@endif
            </div>
            <div class="proj-body">
                <div class="proj-desc">{{ Str::limit($p->description,120) }}</div>
                @if($p->looking_for)<div class="proj-looking"><strong>Looking for:</strong> {{ Str::limit($p->looking_for,90) }}</div>@endif
                @if($p->tags)<div style="margin-top:.6rem;">@foreach(explode(',',$p->tags) as $tag)<span class="tag-chip">#{{ trim($tag) }}</span>@endforeach</div>@endif
            </div>
            <div class="proj-foot">
                <div>
                    @if($p->prize_amount)<span class="proj-prize"><i data-lucide="trophy" class="lic"></i> {{ number_format($p->prize_amount/1000000,1) }}M {{ $p->currency }} prize</span>
                    @elseif($p->budget)<span class="proj-prize"><i data-lucide="banknote" class="lic"></i> {{ number_format($p->budget/1000000,1) }}M {{ $p->currency }} budget</span>
                    @else<span style="font-size:.76rem;color:var(--muted);"><i data-lucide="users" class="lic"></i> {{ $p->participant_count }} participants</span>@endif
                </div>
                <a href="/innovation/{{ $p->slug }}" style="padding:.3rem .8rem;background:#6d28d9;color:#fff;border-radius:6px;font-size:.75rem;font-weight:700;">View →</a>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

@if($authUser)
<div class="modal-overlay" id="postModal">
    <div class="modal">
        <div style="font-weight:800;font-size:1rem;margin-bottom:1rem;"><i data-lucide="lightbulb" class="lic"></i> Post an Innovation Project</div>
        <form method="POST" action="/innovation">
            @csrf
            <div class="form-group"><label class="form-label">Project Title *</label><input type="text" class="form-control" name="title" required></div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Type *</label><select class="form-control" name="type" required>@foreach($typeLabels as $k=>$v)<option value="{{ $k }}"><i data-lucide="{{ $typeIcons[$k] }}" class="lic"></i> {{ $v }}</option>@endforeach</select></div>
                <div class="form-group"><label class="form-label">Stage</label><select class="form-control" name="stage">@foreach($stageLabels as $k=>$v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Sector</label>
                    <select class="form-control" name="sector">
                        @foreach(['ict','agriculture','cocoa','health','finance','energy','timber','construction','general'] as $s)<option value="{{ $s }}">{{ ucfirst($s) }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group"><label class="form-label">Deadline</label><input type="date" class="form-control" name="deadline"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Budget (XAF, optional)</label><input type="number" class="form-control" name="budget" min="0"></div>
                <div class="form-group"><label class="form-label">Prize (XAF, optional)</label><input type="number" class="form-control" name="prize_amount" min="0"></div>
            </div>
            <div class="form-group"><label class="form-label">Description *</label><textarea class="form-control" name="description" required placeholder="Describe the project, goals, and scope…"></textarea></div>
            <div class="form-group"><label class="form-label">Looking For</label><textarea class="form-control" name="looking_for" placeholder="What partners, skills, or resources do you need?"></textarea></div>
            <div class="form-group"><label class="form-label">Tags (comma-separated)</label><input type="text" class="form-control" name="tags" placeholder="e.g. blockchain, agritech, iot"></div>
            <div style="display:flex;gap:.6rem;justify-content:flex-end;margin-top:.5rem;">
                <button type="button" style="padding:.6rem 1.2rem;border:1px solid var(--border);background:#fff;border-radius:8px;font-weight:600;cursor:pointer;" onclick="document.getElementById('postModal').classList.remove('open')">Cancel</button>
                <button type="submit" style="padding:.6rem 1.5rem;background:#6d28d9;color:#fff;border:none;border-radius:8px;font-weight:700;cursor:pointer;">Post Project →</button>
            </div>
        </form>
    </div>
</div>
@endif
@include('partials.footer')
</body>
</html>
