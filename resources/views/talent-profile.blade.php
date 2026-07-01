<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>{{ trim(($profile->first_name??'').' '.($profile->last_name??'')) }} — Talent — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:880px;margin:0 auto;padding:1.5rem;}
.back{font-size:.82rem;color:var(--muted);display:inline-flex;gap:.3rem;margin-bottom:1rem;}
.grid2{display:grid;grid-template-columns:1fr 280px;gap:1.5rem;}
.card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);border:1px solid var(--border);margin-bottom:1rem;}
.card-body{padding:1.3rem;}
.card-head{padding:.75rem 1.1rem;font-weight:700;font-size:.88rem;border-bottom:1px solid var(--border);background:var(--light-bg);}
.hero{display:flex;gap:1rem;align-items:center;margin-bottom:1rem;}
.avatar{width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,#581c87,#7c3aed);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:1.3rem;flex-shrink:0;}
.name{font-size:1.4rem;font-weight:900;}
.headline{font-size:.9rem;color:#7c3aed;font-weight:600;}
.otw{display:inline-block;font-size:.7rem;font-weight:700;padding:2px 10px;border-radius:99px;background:#d1fae5;color:#065f46;margin-top:.3rem;}
.section-title{font-weight:700;font-size:.85rem;color:#7c3aed;margin-bottom:.5rem;text-transform:uppercase;letter-spacing:.5px;}
.summary{font-size:.88rem;color:var(--text);line-height:1.7;}
.entry{margin-bottom:.9rem;}
.entry-header{display:flex;justify-content:space-between;align-items:flex-start;gap:.5rem;}
.entry-title{font-weight:700;font-size:.88rem;}
.entry-date{font-size:.75rem;color:var(--muted);white-space:nowrap;}
.entry-sub{font-size:.8rem;color:var(--muted);margin-top:.1rem;}
.entry-desc{font-size:.82rem;color:#555;margin-top:.3rem;line-height:1.5;}
.skill{display:inline-block;font-size:.74rem;padding:3px 10px;border-radius:99px;background:#f3effe;color:#5b21b6;border:1px solid #e4d8fb;margin:.15rem;}
.side-item{font-size:.83rem;margin-bottom:.4rem;}
.btn-contact{display:block;width:100%;padding:.6rem;background:#7c3aed;color:#fff;border-radius:8px;font-weight:700;font-size:.85rem;text-align:center;text-decoration:none;margin-bottom:.5rem;}
.btn-cv{display:block;width:100%;padding:.55rem;border:1px solid var(--border);color:var(--text);border-radius:8px;font-weight:600;font-size:.82rem;text-align:center;text-decoration:none;}
@media(max-width:700px){.grid2{grid-template-columns:1fr;}}
</style>

@php
$skills = json_decode($profile->skills ?? '[]', true) ?: [];
$exp    = json_decode($profile->experience ?? '[]', true) ?: [];
$edu    = json_decode($profile->education ?? '[]', true) ?: [];
$langs  = json_decode($profile->languages ?? '[]', true) ?: [];
$certs  = json_decode($profile->certifications ?? '[]', true) ?: [];
$jobTypeLabels = ['full_time'=>'Full-time','part_time'=>'Part-time','contract'=>'Contract','internship'=>'Internship','freelance'=>'Freelance'];
$fullName = trim(($profile->first_name??'').' '.($profile->last_name??''));
@endphp

<div class="page">
    <a class="back" href="/talent">← Talent Directory</a>
    <div class="grid2">
        <div>
            <div class="card">
                <div class="card-body">
                    <div class="hero">
                        <div class="avatar">{{ strtoupper(substr($profile->first_name??'A',0,1).substr($profile->last_name??'',0,1)) }}</div>
                        <div>
                            <div class="name">{{ $fullName }}</div>
                            <div class="headline">{{ $profile->headline ?? 'Professional' }}</div>
                            <span class="otw">● Open to work{{ $profile->job_type_preference ? ' · '.($jobTypeLabels[$profile->job_type_preference]??'') : '' }}</span>
                        </div>
                    </div>
                    @if($profile->location)<div style="font-size:.82rem;color:var(--muted);margin-bottom:.9rem;"><i data-lucide="map-pin" class="lic"></i> {{ $profile->location }}</div>@endif
                    @if($profile->summary)
                    <div class="section-title">About</div>
                    <div class="summary">{{ $profile->summary }}</div>
                    @endif
                </div>
            </div>

            @if(count($exp))
            <div class="card">
                <div class="card-head">Experience</div>
                <div class="card-body">
                    @foreach($exp as $e)
                    <div class="entry">
                        <div class="entry-header">
                            <span class="entry-title">{{ $e['title'] ?? '' }}</span>
                            <span class="entry-date">{{ $e['start'] ?? '' }}{{ isset($e['end']) && $e['end'] ? ' – '.$e['end'] : ' – Present' }}</span>
                        </div>
                        <div class="entry-sub">{{ $e['company'] ?? '' }}{{ isset($e['location']) ? ', '.$e['location'] : '' }}</div>
                        @if(isset($e['description']))<div class="entry-desc">{{ $e['description'] }}</div>@endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if(count($edu))
            <div class="card">
                <div class="card-head">Education</div>
                <div class="card-body">
                    @foreach($edu as $e)
                    <div class="entry">
                        <div class="entry-header">
                            <span class="entry-title">{{ $e['degree'] ?? '' }}</span>
                            <span class="entry-date">{{ $e['year'] ?? '' }}</span>
                        </div>
                        <div class="entry-sub">{{ $e['institution'] ?? '' }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <div>
            <div class="card">
                <div class="card-body">
                    @if($viewer)
                        @if((string)$viewer->id !== (string)$profile->user_id)
                        <a href="/messages/{{ $profile->user_id }}" class="btn-contact"><i data-lucide="message-circle" class="lic"></i> Message</a>
                        @endif
                        @if($publicCv)<a href="/cv/{{ $publicCv }}/view" class="btn-contact" style="background:#fff;color:#7c3aed;border:1px solid #7c3aed;"><i data-lucide="file-text" class="lic"></i> View Full CV</a>@endif
                        <a href="mailto:{{ $profile->email }}" class="btn-cv"><i data-lucide="mail" class="lic"></i> Email directly</a>
                    @else
                        <div style="font-size:.82rem;color:var(--muted);margin-bottom:.7rem;text-align:center;">Sign in to view contact details and full CV.</div>
                        <a href="/login?next=/talent/{{ $profile->user_id }}" class="btn-contact">Sign In →</a>
                    @endif
                </div>
            </div>

            @if(count($skills))
            <div class="card">
                <div class="card-head">Skills</div>
                <div class="card-body">
                    @foreach($skills as $sk)<span class="skill">{{ $sk }}</span>@endforeach
                </div>
            </div>
            @endif

            @if(count($langs))
            <div class="card">
                <div class="card-head">Languages</div>
                <div class="card-body">
                    @foreach($langs as $l)<div class="side-item">{{ is_array($l) ? ($l['name'] ?? reset($l)) : $l }}</div>@endforeach
                </div>
            </div>
            @endif

            @if(count($certs))
            <div class="card">
                <div class="card-head">Certifications</div>
                <div class="card-body">
                    @foreach($certs as $c)<div class="side-item"><i data-lucide="medal" class="lic"></i> {{ is_array($c) ? ($c['name'] ?? reset($c)) : $c }}</div>@endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@include('partials.footer')
</body>
</html>
