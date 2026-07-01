<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Talent Directory — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:1100px;margin:0 auto;padding:1.5rem;}
.hero{background:linear-gradient(135deg,#581c87,#7c3aed);border-radius:var(--radius);padding:2.5rem 2rem;margin-bottom:1.5rem;color:#fff;}
.hero-title{font-size:1.8rem;font-weight:900;margin-bottom:.4rem;}
.hero-sub{font-size:.9rem;color:#ddd6fe;}
.filters{display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1.2rem;}
.filters input,.filters select{padding:.45rem .75rem;border:1px solid var(--border);border-radius:7px;font-size:.83rem;outline:none;}
.filters input:focus,.filters select:focus{border-color:#7c3aed;}
.btn-search{padding:.45rem 1.1rem;background:#7c3aed;color:#fff;border:none;border-radius:7px;font-size:.83rem;font-weight:600;cursor:pointer;}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:1rem;}
.cand-card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);border:1px solid var(--border);padding:1.2rem;display:flex;flex-direction:column;}
.cand-card:hover{box-shadow:var(--shadow-hover);}
.cand-top{display:flex;gap:.8rem;align-items:center;margin-bottom:.7rem;}
.avatar{width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg,#581c87,#7c3aed);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:1rem;flex-shrink:0;}
.cand-name{font-weight:800;font-size:1rem;color:var(--text);}
.cand-headline{font-size:.8rem;color:#7c3aed;font-weight:600;}
.otw{display:inline-block;font-size:.66rem;font-weight:700;padding:1px 8px;border-radius:99px;background:#d1fae5;color:#065f46;margin-top:.2rem;}
.cand-loc{font-size:.78rem;color:var(--muted);margin-bottom:.5rem;}
.cand-summary{font-size:.8rem;color:var(--muted);line-height:1.5;margin-bottom:.7rem;flex:1;}
.skills{display:flex;flex-wrap:wrap;gap:.3rem;margin-bottom:.8rem;}
.skill{font-size:.68rem;padding:2px 8px;border-radius:99px;background:#f3effe;color:#5b21b6;border:1px solid #e4d8fb;}
.cand-actions{display:flex;gap:.5rem;}
.btn{padding:.4rem .85rem;border-radius:7px;font-size:.78rem;font-weight:700;text-decoration:none;text-align:center;}
.btn-primary{background:#7c3aed;color:#fff;flex:1;}
.btn-ghost{border:1px solid var(--border);color:var(--text);flex:1;}
</style>

@php
$jobTypeLabels = ['full_time'=>'Full-time','part_time'=>'Part-time','contract'=>'Contract','internship'=>'Internship','freelance'=>'Freelance'];
@endphp

<div class="page">
    <div class="hero">
        <div class="hero-title"><i data-lucide="sparkles" style="width:22px;height:22px;display:inline;vertical-align:-3px;"></i> Talent Directory</div>
        <div class="hero-sub">Discover {{ $total }} professional{{ $total!=1?'s':'' }} across Cameroon who are open to work — search by skill, location, or availability.</div>
    </div>

    <form method="GET" action="/talent" class="filters">
        <input type="text" name="skill" value="{{ $skill }}" placeholder="Skill (e.g. Laravel, Accounting)" style="flex:1;min-width:180px;">
        <input type="text" name="location" value="{{ $loc }}" placeholder="Location (e.g. Douala)">
        <select name="job_type">
            <option value="">Any type</option>
            @foreach($jobTypeLabels as $k=>$v)<option value="{{ $k }}" {{ $jobType===$k?'selected':'' }}>{{ $v }}</option>@endforeach
        </select>
        <button type="submit" class="btn-search">Search</button>
        <span style="font-size:.78rem;color:var(--muted);align-self:center;margin-left:auto;">{{ $candidates->count() }} shown</span>
    </form>

    @if($candidates->isEmpty())
    <div style="text-align:center;padding:3rem;background:#fff;border-radius:var(--radius);border:1px solid var(--border);">
        <i data-lucide="search-x" style="width:38px;height:38px;color:var(--muted);margin-bottom:.4rem;"></i>
        <div style="font-weight:700;margin-bottom:.3rem;">No candidates match</div>
        <div style="font-size:.85rem;color:var(--muted);">Try a broader search.</div>
    </div>
    @else
    <div class="grid">
        @foreach($candidates as $c)
        @php $skills = json_decode($c->skills ?? '[]', true) ?: []; @endphp
        <div class="cand-card">
            <div class="cand-top">
                <div class="avatar">{{ strtoupper(substr($c->first_name??'A',0,1).substr($c->last_name??'',0,1)) }}</div>
                <div>
                    <div class="cand-name">{{ trim(($c->first_name??'').' '.($c->last_name??'')) }}</div>
                    <div class="cand-headline">{{ $c->headline ?? 'Professional' }}</div>
                    <span class="otw">● Open to work</span>
                </div>
            </div>
            @if($c->location)<div class="cand-loc"><i data-lucide="map-pin" style="width:12px;height:12px;display:inline;vertical-align:-2px;"></i> {{ $c->location }}{{ $c->job_type_preference ? ' · '.($jobTypeLabels[$c->job_type_preference]??'') : '' }}</div>@endif
            @if($c->summary)<div class="cand-summary">{{ Str::limit($c->summary, 120) }}</div>@endif
            @if(count($skills))
            <div class="skills">
                @foreach(array_slice($skills, 0, 6) as $sk)<span class="skill">{{ $sk }}</span>@endforeach
                @if(count($skills) > 6)<span class="skill" style="background:#fff;">+{{ count($skills)-6 }}</span>@endif
            </div>
            @endif
            <div class="cand-actions">
                <a href="/talent/{{ $c->user_id }}" class="btn btn-primary">View Profile</a>
                @if(isset($cvSlugs[$c->user_id]))<a href="/cv/{{ $cvSlugs[$c->user_id] }}/view" class="btn btn-ghost">View CV</a>@endif
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@include('partials.footer')
</body>
</html>
