<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>{{ $project->title }} — Innovation Hub — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:1000px;margin:0 auto;padding:1.5rem;}
.grid2{display:grid;grid-template-columns:1fr 290px;gap:1.5rem;}
.card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);border:1px solid var(--border);margin-bottom:1rem;}
.card-body{padding:1.3rem;}
.card-head{padding:.75rem 1.1rem;font-weight:700;font-size:.88rem;border-bottom:1px solid var(--border);background:var(--light-bg);}
.proj-title{font-size:1.4rem;font-weight:900;margin-bottom:.5rem;}
.badge{display:inline-block;padding:3px 12px;border-radius:99px;font-size:.75rem;font-weight:700;background:var(--light-bg);color:var(--muted);border:1px solid var(--border);}
.badge-type{background:#ede9fe;color:#5b21b6;border-color:#c4b5fd;}
.section-title{font-weight:700;font-size:.9rem;color:var(--text);margin:.9rem 0 .4rem;}
.desc-text{font-size:.87rem;color:var(--text);line-height:1.7;}
.looking-box{background:#faf5ff;border:1px solid #e9d5ff;border-radius:8px;padding:.9rem;font-size:.85rem;color:#5b21b6;line-height:1.6;margin-top:.5rem;}
.info-grid{display:grid;grid-template-columns:1fr 1fr;gap:.6rem;margin:.9rem 0;}
.info-item{padding:.6rem .8rem;background:var(--light-bg);border-radius:8px;}
.info-lbl{font-size:.68rem;color:var(--muted);font-weight:600;margin-bottom:2px;}
.info-val{font-size:.88rem;font-weight:700;color:var(--text);}
.join-box{border:2px solid #6d28d9;border-radius:var(--radius);padding:1.3rem;background:#fff;margin-bottom:1rem;}
.form-group{margin-bottom:.75rem;}
.form-label{display:block;font-size:.8rem;font-weight:600;margin-bottom:.3rem;}
.form-control{width:100%;padding:.45rem .75rem;border:1px solid var(--border);border-radius:7px;font-size:.85rem;outline:none;font-family:inherit;box-sizing:border-box;}
.form-control:focus{border-color:#6d28d9;}
textarea.form-control{resize:vertical;min-height:80px;}
.btn-join{width:100%;padding:.6rem;background:#6d28d9;color:#fff;border:none;border-radius:8px;font-size:.9rem;font-weight:700;cursor:pointer;}
.part-row{display:flex;align-items:center;gap:.5rem;padding:.4rem 0;border-bottom:1px solid var(--border);font-size:.82rem;}
.part-row:last-child{border-bottom:none;}
.p-avatar{width:28px;height:28px;border-radius:50%;background:#6d28d9;display:flex;align-items:center;justify-content:center;font-size:.68rem;font-weight:700;color:#fff;flex-shrink:0;}
.tag-chip{display:inline-block;font-size:.72rem;color:#5b21b6;background:#f5f3ff;border:1px solid #ddd6fe;border-radius:6px;padding:2px 8px;margin:0 4px 4px 0;}
@media(max-width:700px){.grid2{grid-template-columns:1fr;}.info-grid{grid-template-columns:1fr;}}
</style>

@php
$typeLabels = ['research'=>'Joint Research','patent'=>'Patent','hackathon'=>'Hackathon','challenge'=>'Challenge','prototype'=>'Prototype','open_innovation'=>'Open Innovation','grant'=>'Grant','spinoff'=>'Spinoff','other'=>'Other'];
$typeIcons  = ['research'=>'microscope','patent'=>'scroll','hackathon'=>'laptop','challenge'=>'trophy','prototype'=>'wrench','open_innovation'=>'globe','grant'=>'banknote','spinoff'=>'rocket','other'=>'lightbulb'];
$stageLabels = ['idea'=>'Idea','prototype'=>'Prototype','pilot'=>'Pilot','scaling'=>'Scaling','market_ready'=>'Market Ready'];
@endphp

<div class="page">
    <a href="/innovation" style="font-size:.82rem;color:var(--muted);display:inline-flex;gap:.3rem;margin-bottom:1rem;">← Innovation Hub</a>
    <div class="grid2">
        <div>
            <div class="card">
                <div class="card-body">
                    <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:.7rem;">
                        <span class="badge badge-type"><i data-lucide="{{ $typeIcons[$project->type]??'lightbulb' }}" class="lic"></i> {{ $typeLabels[$project->type]??ucfirst($project->type) }}</span>
                        <span class="badge">{{ $stageLabels[$project->stage]??ucfirst($project->stage) }}</span>
                        <span class="badge">{{ ucfirst($project->sector) }}</span>
                        <span class="badge" style="background:#d1fae5;color:#065f46;border-color:#6ee7b7;">{{ ucfirst(str_replace('_',' ',$project->status)) }}</span>
                    </div>
                    <div class="proj-title">{{ $project->title }}</div>
                    @if($company)<div style="font-size:.82rem;color:var(--muted);margin-bottom:.7rem;">by <a href="/companies/{{ $company->slug }}" style="color:#6d28d9;font-weight:600;">{{ $company->name }}</a></div>@endif
                    <div class="info-grid">
                        @if($project->budget)<div class="info-item"><div class="info-lbl">Budget</div><div class="info-val">{{ number_format($project->budget/1000000,1) }}M {{ $project->currency }}</div></div>@endif
                        @if($project->prize_amount)<div class="info-item"><div class="info-lbl">Prize Pool</div><div class="info-val" style="color:#6d28d9;">{{ number_format($project->prize_amount/1000000,1) }}M {{ $project->currency }}</div></div>@endif
                        @if($project->deadline)<div class="info-item"><div class="info-lbl">Deadline</div><div class="info-val">{{ date('d M Y', strtotime($project->deadline)) }}</div></div>@endif
                        <div class="info-item"><div class="info-lbl">Participants</div><div class="info-val">{{ $project->participant_count }}</div></div>
                        <div class="info-item"><div class="info-lbl">Views</div><div class="info-val">{{ number_format($project->view_count+1) }}</div></div>
                    </div>
                    <div class="section-title">About this Project</div>
                    <div class="desc-text">{{ $project->description }}</div>
                    @if($project->looking_for)
                    <div class="section-title"><i data-lucide="search" class="lic"></i> Looking For</div>
                    <div class="looking-box">{{ $project->looking_for }}</div>
                    @endif
                    @if($project->tags)
                    <div class="section-title">Tags</div>
                    <div>@foreach(explode(',',$project->tags) as $tag)<span class="tag-chip">#{{ trim($tag) }}</span>@endforeach</div>
                    @endif
                </div>
            </div>

            @if($participants->count() > 0)
            <div class="card">
                <div class="card-head">Participants &amp; Partners ({{ $participants->count() }})</div>
                <div class="card-body">
                    @foreach($participants as $pt)
                    <div class="part-row">
                        <div class="p-avatar">{{ strtoupper(substr($pt->first_name??'A',0,1)) }}</div>
                        <div style="flex:1;">
                            <span style="font-weight:600;">{{ ($pt->first_name??'').' '.($pt->last_name??'') }}</span>
                            @if($pt->company_name)<span style="color:var(--muted);font-size:.76rem;"> · {{ $pt->company_name }}</span>@endif
                        </div>
                        <span class="badge" style="font-size:.66rem;">{{ ucfirst($pt->role) }}</span>
                        @if($isOwner && $pt->user_id)<a href="/messages/{{ $pt->user_id }}" title="Message" style="font-size:.85rem;text-decoration:none;margin-left:.4rem;"><i data-lucide="message-circle" class="lic"></i></a>@endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <div>
            @if($alreadyJoined)
            <div class="join-box" style="border-color:var(--muted);">
                <div style="text-align:center;padding:.4rem 0;">
                    <div style="font-size:1.5rem;margin-bottom:.4rem;"><i data-lucide="check" class="lic"></i></div>
                    <div style="font-weight:700;">Request Submitted!</div>
                    <div style="font-size:.8rem;color:var(--muted);margin-top:.3rem;">The project lead will review your interest.</div>
                </div>
            </div>
            @elseif($authUser && !$isOwner)
            <div class="join-box">
                <div style="font-weight:800;font-size:.95rem;margin-bottom:.75rem;">Express Interest</div>
                <form method="POST" action="/innovation/{{ $project->slug }}/join">
                    @csrf
                    @if($myCompanies->count() > 0)
                    <div class="form-group"><label class="form-label">As</label>
                        <select class="form-control" name="company_id"><option value="">Individual</option>@foreach($myCompanies as $mc)<option value="{{ $mc->id }}">{{ $mc->name }}</option>@endforeach</select>
                    </div>
                    @endif
                    <div class="form-group"><label class="form-label">Role</label>
                        <select class="form-control" name="role">
                            <option value="partner">Partner</option><option value="researcher">Researcher</option>
                            <option value="sponsor">Sponsor</option><option value="mentor">Mentor</option>
                            <option value="participant">Participant</option><option value="applicant">Applicant</option>
                        </select>
                    </div>
                    <div class="form-group"><label class="form-label">Message</label><textarea class="form-control" name="message" required placeholder="How would you like to contribute?"></textarea></div>
                    <button type="submit" class="btn-join">Express Interest →</button>
                </form>
            </div>
            @elseif($isOwner)
            <div class="join-box" style="border-color:var(--muted);">
                <div style="font-size:.85rem;color:var(--text);text-align:center;">This is your project. Review interest from the participants list.</div>
            </div>
            @else
            <div class="join-box" style="border-color:var(--muted);">
                <div style="font-size:.85rem;margin-bottom:.75rem;">Sign in to express interest in this project.</div>
                <a href="/auth/login" class="btn-join" style="display:block;text-align:center;text-decoration:none;">Sign In →</a>
            </div>
            @endif

            @if($company)
            <div style="background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);padding:1.1rem;border:1px solid var(--border);">
                <div style="display:flex;gap:.5rem;align-items:center;margin-bottom:.75rem;">
                    <div style="width:36px;height:36px;border-radius:7px;background:linear-gradient(135deg,#312e81,#6d28d9);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.85rem;color:#fff;">{{ strtoupper(substr($company->name,0,2)) }}</div>
                    <div style="font-weight:800;font-size:.85rem;">{{ $company->name }}</div>
                </div>
                <a href="/companies/{{ $company->slug }}" style="display:block;text-align:center;border:1px solid var(--border);color:var(--text);padding:.4rem;border-radius:7px;font-size:.8rem;font-weight:600;">View Lead Organisation →</a>
            </div>
            @endif
        </div>
    </div>
</div>
@include('partials.footer')
</body>
</html>
