<!DOCTYPE html>
<html lang="{{ $cv->language ?? 'en' }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>{{ trim(($user->first_name??'').' '.($user->last_name??'')) ?? 'CV' }} — {{ $cv->title }}</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;900&display=swap');
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Inter',sans-serif;background:#f4f5f7;color:#1a1a2e;font-size:14px;}
.cv-wrap{max-width:780px;margin:0 auto;background:#fff;min-height:100vh;}
@media print{
  @page{size:A4;margin:11mm 0;}
  *{-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important;color-adjust:exact!important;}
  html,body{background:#fff!important;margin:0!important;padding:0!important;width:100%;}
  .no-print{display:none!important;}
  .cv-wrap{max-width:100%!important;width:100%!important;margin:0!important;box-shadow:none!important;min-height:0!important;}
  /* keep records intact across page breaks */
  .entry,.section{page-break-inside:avoid;break-inside:avoid;}
  .section-title,.sidebar-title{page-break-after:avoid;break-after:avoid;}
  .cv-header{page-break-after:avoid;break-after:avoid;}
  p{orphans:3;widows:3;}
  /* two-column templates print cleanly without column overflow */
  .classic .cv-body,.modern .cv-body{display:grid!important;}
  .modern .cv-sidebar,.classic .cv-sidebar{-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important;}
}

/* CLASSIC TEMPLATE */
.classic .cv-header{padding:2rem 2.5rem 1.5rem;border-bottom:3px solid var(--accent);}
.classic .cv-name{font-size:1.8rem;font-weight:900;letter-spacing:-1px;color:var(--accent);}
.classic .cv-headline{font-size:.92rem;color:#555;margin-top:.2rem;}
.classic .cv-contact{display:flex;gap:1.5rem;flex-wrap:wrap;margin-top:.8rem;font-size:.78rem;color:#666;}
.classic .cv-body{display:grid;grid-template-columns:1fr 240px;gap:0;}
.classic .cv-main{padding:1.5rem 2rem;}
.classic .cv-sidebar{padding:1.5rem 1.5rem;background:#f8f9fa;border-left:1px solid #eee;}
.classic .section-title{font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:1px;color:var(--accent);margin-bottom:.7rem;padding-bottom:.3rem;border-bottom:2px solid var(--accent);}
.classic .section{margin-bottom:1.3rem;}

/* MODERN TEMPLATE */
.modern .cv-header{background:var(--accent);color:#fff;padding:2rem 2.5rem;display:flex;align-items:center;gap:1.5rem;}
.modern .cv-avatar{width:70px;height:70px;border-radius:50%;background:rgba(255,255,255,.25);display:flex;align-items:center;justify-content:center;font-size:1.5rem;font-weight:900;color:#fff;flex-shrink:0;}
.modern .cv-name{font-size:1.6rem;font-weight:900;}
.modern .cv-headline{font-size:.88rem;opacity:.85;margin-top:.2rem;}
.modern .cv-body{display:grid;grid-template-columns:200px 1fr;}
.modern .cv-sidebar{padding:1.5rem;background:#1a1a2e;color:#eee;}
.modern .cv-main{padding:1.5rem 2rem;}
.modern .section-title{font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:1px;color:var(--accent);margin-bottom:.6rem;}
.modern .section{margin-bottom:1.2rem;}
.modern .sidebar-title{font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:1px;color:#aaa;margin-bottom:.5rem;}

/* MINIMAL TEMPLATE */
.minimal .cv-header{padding:2.5rem 3rem 1.5rem;border-bottom:1px solid #ddd;}
.minimal .cv-name{font-size:2rem;font-weight:300;letter-spacing:-1px;}
.minimal .cv-headline{font-size:.9rem;color:#888;margin-top:.3rem;font-weight:400;}
.minimal .cv-contact{font-size:.78rem;color:#888;margin-top:.6rem;}
.minimal .cv-body{padding:1.5rem 3rem;}
.minimal .section-title{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:2px;color:#888;margin-bottom:.7rem;margin-top:1.3rem;}
.minimal .section{border-left:2px solid #f0f0f0;padding-left:1rem;margin-bottom:1rem;}

/* PROFESSIONAL (executive) TEMPLATE */
.professional{font-family:Georgia,'Times New Roman',serif;}
.professional .cv-header{padding:2.2rem 2.5rem 1.4rem;text-align:center;border-bottom:3px double var(--accent);}
.professional .cv-name{font-size:2rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#1a1a2e;}
.professional .cv-headline{font-size:.95rem;color:var(--accent);margin-top:.3rem;font-style:italic;}
.professional .cv-contact{display:flex;gap:1.2rem;flex-wrap:wrap;justify-content:center;margin-top:.7rem;font-size:.78rem;color:#555;}
.professional .cv-body{padding:1.6rem 2.5rem;}
.professional .section{margin-bottom:1.3rem;}
.professional .section-title{font-size:.82rem;font-weight:700;text-transform:uppercase;letter-spacing:2px;color:var(--accent);margin-bottom:.6rem;padding-bottom:.25rem;border-bottom:1px solid #ccc;}
.professional .entry-title{font-size:.92rem;}

/* TECHNICAL TEMPLATE */
.technical{font-family:'Inter',sans-serif;}
.technical .cv-header{padding:1.8rem 2.2rem;background:#0f1623;color:#e6edf3;}
.technical .cv-name{font-size:1.7rem;font-weight:900;}
.technical .cv-headline{font-size:.9rem;color:#5eead4;margin-top:.2rem;font-family:'Courier New',monospace;}
.technical .cv-contact{display:flex;gap:1.2rem;flex-wrap:wrap;margin-top:.6rem;font-size:.76rem;color:#9fb0c0;font-family:'Courier New',monospace;}
.technical .skills-band{background:#13202e;padding:1rem 2.2rem;border-bottom:2px solid var(--accent);}
.technical .skills-band .section-title{color:#5eead4;font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:1px;margin-bottom:.5rem;}
.technical .tag-tech{display:inline-block;padding:3px 10px;border-radius:5px;font-size:.74rem;font-weight:600;margin:.15rem;background:rgba(94,234,212,.12);color:#5eead4;border:1px solid rgba(94,234,212,.3);font-family:'Courier New',monospace;}
.technical .cv-body{padding:1.5rem 2.2rem;}
.technical .section{margin-bottom:1.2rem;}
.technical .section-title{font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:1px;color:var(--accent);margin-bottom:.6rem;border-left:3px solid var(--accent);padding-left:.5rem;}

/* ATS-FRIENDLY TEMPLATE (single column, machine-readable) */
.ats{font-family:Arial,Helvetica,sans-serif;color:#000;}
.ats .cv-header{padding:2rem 2.5rem 1rem;}
.ats .cv-name{font-size:1.7rem;font-weight:700;color:#000;}
.ats .cv-headline{font-size:.95rem;color:#000;margin-top:.2rem;}
.ats .cv-contact{font-size:.82rem;color:#000;margin-top:.5rem;}
.ats .cv-body{padding:0 2.5rem 2rem;}
.ats .section{margin-bottom:1.1rem;}
.ats .section-title{font-size:.85rem;font-weight:700;text-transform:uppercase;color:#000;margin:1rem 0 .5rem;padding-bottom:.2rem;border-bottom:1px solid #000;}
.ats .entry-title{font-weight:700;}
.ats .tag-ats{display:inline;}
.ats .tag-ats:not(:last-child)::after{content:', ';}

/* SHARED */
.entry{margin-bottom:.9rem;}
.entry-header{display:flex;justify-content:space-between;align-items:flex-start;}
.entry-title{font-weight:700;font-size:.88rem;}
.entry-date{font-size:.75rem;color:#888;white-space:nowrap;}
.entry-sub{font-size:.8rem;color:#666;margin-top:.1rem;}
.entry-desc{font-size:.82rem;color:#555;margin-top:.3rem;line-height:1.5;}
.tag{display:inline-block;padding:2px 8px;border-radius:99px;font-size:.7rem;font-weight:600;margin:.15rem .1rem;}
.tag-skill{background:#e8f5e9;color:#007a33;}
.tag-skill-mod{background:#e3f2fd;color:#0056b3;}
.tag-skill-dark{background:#eee;color:#333;}
.cv-actions{position:fixed;bottom:1rem;right:1rem;display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;justify-content:flex-end;max-width:340px;}
.print-tip{display:none;font-size:.72rem;background:#1a1a2e;color:#fff;padding:.5rem .7rem;border-radius:8px;line-height:1.4;box-shadow:0 4px 16px rgba(0,0,0,.25);}
.print-tip b{color:#5eead4;}
.cv-action-btn{padding:.6rem 1.1rem;border-radius:8px;font-size:.82rem;font-weight:700;cursor:pointer;border:none;}
.btn-print{background:#1a1a2e;color:#fff;}
.btn-edit{background:var(--accent);color:#fff;text-decoration:none;}
</style>
<style>:root { --accent: {{ ['green'=>'#007a33','blue'=>'#0056b3','red'=>'#ce1126','dark'=>'#1a1a2e'][$cv->color_scheme??'green']??'#007a33' }}; }</style>
</head>
<body>
@php
    $profile = $profile ?? null;
    $exp = json_decode($profile->experience ?? '[]', true) ?: [];
    $edu = json_decode($profile->education ?? '[]', true) ?: [];
    $skills = json_decode($profile->skills ?? '[]', true) ?: [];
    $certs = json_decode($profile->certifications ?? '[]', true) ?: [];
    $langs = json_decode($profile->languages ?? '[]', true) ?: [];
    $template = $cv->template ?? 'classic';
    $ini = strtoupper(substr(trim(($user->first_name??'').' '.($user->last_name??'')) ?? 'U', 0, 2));
@endphp

<div class="cv-wrap {{ $template }}">
    @if($template === 'modern')
    <div class="cv-header">
        <div class="cv-avatar">{{ $ini }}</div>
        <div>
            <div class="cv-name">{{ trim(($user->first_name??'').' '.($user->last_name??'')) ?? '' }}</div>
            <div class="cv-headline">{{ $profile->headline ?? '' }}</div>
            <div style="font-size:.75rem;opacity:.75;margin-top:.5rem;">
                {{ $user->email ?? '' }}
                @if($profile->phone ?? '') · {{ $profile->phone }}@endif
                @if($profile->location ?? '') · {{ $profile->location }}@endif
            </div>
        </div>
    </div>
    <div class="cv-body">
        <div class="cv-sidebar">
            @if(count($skills))<div class="sidebar-title">Skills</div>@foreach($skills as $sk)<div class="tag tag-skill" style="background:rgba(255,255,255,.1);color:#eee;display:block;margin-bottom:.2rem;border-radius:4px;">{{ $sk }}</div>@endforeach<br>@endif
            @if(count($langs))<div class="sidebar-title">Languages</div>@foreach($langs as $l)<div style="font-size:.8rem;margin-bottom:.2rem;">{{ is_array($l)?($l['name']??$l):$l }}</div>@endforeach<br>@endif
            @if(count($certs))<div class="sidebar-title">Certifications</div>@foreach($certs as $c)<div style="font-size:.8rem;margin-bottom:.3rem;">{{ is_array($c)?($c['name']??$c):$c }}</div>@endforeach
@endif
        </div>
        <div class="cv-main">
            @if($profile->summary ?? '')<div class="section"><div class="section-title">About</div><p style="font-size:.85rem;color:#555;line-height:1.6;">{{ $profile->summary }}</p></div>@endif
            @if(count($exp))<div class="section"><div class="section-title">Experience</div>@foreach($exp as $e)<div class="entry"><div class="entry-header"><span class="entry-title">{{ $e['title']??'' }}</span><span class="entry-date">{{ $e['start']??'' }}{{ isset($e['end'])?' – '.$e['end']:'– Present' }}</span></div><div class="entry-sub">{{ $e['company']??'' }}{{ isset($e['location'])?', '.$e['location']:'' }}</div><div class="entry-desc">{{ $e['description']??'' }}</div></div>@endforeach</div>@endif
            @if(count($edu))<div class="section"><div class="section-title">Education</div>@foreach($edu as $e)<div class="entry"><div class="entry-header"><span class="entry-title">{{ $e['degree']??'' }}</span><span class="entry-date">{{ $e['year']??'' }}</span></div><div class="entry-sub">{{ $e['institution']??'' }}</div></div>@endforeach</div>@endif
        </div>
    </div>

    @elseif($template === 'minimal')
    <div class="cv-header">
        <div class="cv-name">{{ trim(($user->first_name??'').' '.($user->last_name??'')) ?? '' }}</div>
        <div class="cv-headline">{{ $profile->headline ?? '' }}</div>
        <div class="cv-contact">{{ $user->email ?? '' }}{{ ($profile->phone ?? '') ? ' · '.$profile->phone : '' }}{{ ($profile->location ?? '') ? ' · '.$profile->location : '' }}{{ ($profile->linkedin_url ?? '') ? ' · '.str_replace('https://','', $profile->linkedin_url) : '' }}</div>
    </div>
    <div class="cv-body">
        @if($profile->summary ?? '')<div class="section-title">Profile</div><div class="section"><p style="font-size:.85rem;color:#444;line-height:1.6;">{{ $profile->summary }}</p></div>@endif
        @if(count($exp))<div class="section-title">Experience</div>@foreach($exp as $e)<div class="section"><div class="entry-header"><span class="entry-title">{{ $e['title']??'' }}</span><span class="entry-date">{{ $e['start']??'' }}{{ isset($e['end'])?' – '.$e['end']:'– Present' }}</span></div><div class="entry-sub">{{ $e['company']??'' }}</div><div class="entry-desc">{{ $e['description']??'' }}</div></div>@endforeach
@endif
        @if(count($edu))<div class="section-title">Education</div>@foreach($edu as $e)<div class="section"><div class="entry-header"><span class="entry-title">{{ $e['degree']??'' }}</span><span class="entry-date">{{ $e['year']??'' }}</span></div><div class="entry-sub">{{ $e['institution']??'' }}</div></div>@endforeach
@endif
        @if(count($skills))<div class="section-title">Skills</div><div class="section">@foreach($skills as $sk)<span class="tag tag-skill-dark">{{ $sk }}</span>@endforeach</div>@endif
    </div>

    @elseif($template === 'professional')
    <div class="cv-header">
        <div class="cv-name">{{ trim(($user->first_name??'').' '.($user->last_name??'')) ?? '' }}</div>
        <div class="cv-headline">{{ $profile->headline ?? '' }}</div>
        <div class="cv-contact">
            <span>{{ $user->email ?? '' }}</span>
            @if($profile->phone ?? '')<span>{{ $profile->phone }}</span>@endif
            @if($profile->location ?? '')<span>{{ $profile->location }}</span>@endif
            @if($profile->linkedin_url ?? '')<span>{{ str_replace(['https://www.','https://'],'', $profile->linkedin_url) }}</span>@endif
        </div>
    </div>
    <div class="cv-body">
        @if($profile->summary ?? '')<div class="section"><div class="section-title">Executive Summary</div><p style="font-size:.88rem;line-height:1.7;color:#333;">{{ $profile->summary }}</p></div>@endif
        @if(count($exp))<div class="section"><div class="section-title">Professional Experience</div>@foreach($exp as $e)<div class="entry"><div class="entry-header"><span class="entry-title">{{ $e['title']??'' }}</span><span class="entry-date">{{ $e['start']??'' }}{{ isset($e['end'])?' – '.$e['end']:' – Present' }}</span></div><div class="entry-sub">{{ $e['company']??'' }}{{ isset($e['location'])?', '.$e['location']:'' }}</div><div class="entry-desc">{{ $e['description']??'' }}</div></div>@endforeach</div>@endif
        @if(count($edu))<div class="section"><div class="section-title">Education</div>@foreach($edu as $e)<div class="entry"><div class="entry-header"><span class="entry-title">{{ $e['degree']??'' }}</span><span class="entry-date">{{ $e['year']??'' }}</span></div><div class="entry-sub">{{ $e['institution']??'' }}</div></div>@endforeach</div>@endif
        @if(count($skills))<div class="section"><div class="section-title">Core Competencies</div><p style="font-size:.85rem;line-height:1.8;color:#333;">{{ implode('  •  ', $skills) }}</p></div>@endif
        @if(count($certs))<div class="section"><div class="section-title">Certifications</div>@foreach($certs as $c)<div style="font-size:.85rem;margin-bottom:.25rem;">{{ is_array($c)?($c['name']??reset($c)):$c }}</div>@endforeach</div>@endif
        @if(count($langs))<div class="section"><div class="section-title">Languages</div><p style="font-size:.85rem;">{{ implode('  •  ', array_map(fn($l)=>is_array($l)?($l['name']??reset($l)):$l, $langs)) }}</p></div>@endif
    </div>

    @elseif($template === 'technical')
    <div class="cv-header">
        <div class="cv-name">{{ trim(($user->first_name??'').' '.($user->last_name??'')) ?? '' }}</div>
        <div class="cv-headline">{{ $profile->headline ?? '' }}</div>
        <div class="cv-contact">
            <span>{{ $user->email ?? '' }}</span>
            @if($profile->phone ?? '')<span>{{ $profile->phone }}</span>@endif
            @if($profile->location ?? '')<span>{{ $profile->location }}</span>@endif
            @if($profile->github_url ?? '')<span>git: {{ str_replace(['https://www.','https://','github.com/'],'', $profile->github_url) }}</span>@endif
            @if($profile->portfolio_url ?? '')<span>{{ str_replace(['https://www.','https://'],'', $profile->portfolio_url) }}</span>@endif
        </div>
    </div>
    @if(count($skills))
    <div class="skills-band">
        <div class="section-title"><i data-lucide="settings" class="lic"></i> Technical Skills</div>
        @foreach($skills as $sk)<span class="tag-tech">{{ $sk }}</span>@endforeach
    </div>
    @endif
    <div class="cv-body">
        @if($profile->summary ?? '')<div class="section"><div class="section-title">Profile</div><p style="font-size:.85rem;line-height:1.6;color:#444;">{{ $profile->summary }}</p></div>@endif
        @if(count($exp))<div class="section"><div class="section-title">Experience</div>@foreach($exp as $e)<div class="entry"><div class="entry-header"><span class="entry-title">{{ $e['title']??'' }}</span><span class="entry-date">{{ $e['start']??'' }}{{ isset($e['end'])?' – '.$e['end']:' – Present' }}</span></div><div class="entry-sub">{{ $e['company']??'' }}{{ isset($e['location'])?', '.$e['location']:'' }}</div><div class="entry-desc">{{ $e['description']??'' }}</div></div>@endforeach</div>@endif
        @if(count($edu))<div class="section"><div class="section-title">Education</div>@foreach($edu as $e)<div class="entry"><div class="entry-header"><span class="entry-title">{{ $e['degree']??'' }}</span><span class="entry-date">{{ $e['year']??'' }}</span></div><div class="entry-sub">{{ $e['institution']??'' }}</div></div>@endforeach</div>@endif
        @if(count($certs))<div class="section"><div class="section-title">Certifications</div>@foreach($certs as $c)<div style="font-size:.82rem;margin-bottom:.25rem;">{{ is_array($c)?($c['name']??reset($c)):$c }}</div>@endforeach</div>@endif
        @if(count($langs))<div class="section"><div class="section-title">Languages</div><p style="font-size:.82rem;">{{ implode(' · ', array_map(fn($l)=>is_array($l)?($l['name']??reset($l)):$l, $langs)) }}</p></div>@endif
    </div>

    @elseif($template === 'ats')
    <div class="cv-header">
        <div class="cv-name">{{ trim(($user->first_name??'').' '.($user->last_name??'')) ?? '' }}</div>
        <div class="cv-headline">{{ $profile->headline ?? '' }}</div>
        <div class="cv-contact">
            {{ $user->email ?? '' }}{{ ($profile->phone ?? '') ? ' | '.$profile->phone : '' }}{{ ($profile->location ?? '') ? ' | '.$profile->location : '' }}{{ ($profile->linkedin_url ?? '') ? ' | '.str_replace(['https://www.','https://'],'', $profile->linkedin_url) : '' }}
        </div>
    </div>
    <div class="cv-body">
        @if($profile->summary ?? '')<div class="section"><div class="section-title">Summary</div><p style="font-size:.85rem;line-height:1.6;">{{ $profile->summary }}</p></div>@endif
        @if(count($exp))<div class="section"><div class="section-title">Work Experience</div>@foreach($exp as $e)<div class="entry"><div class="entry-title">{{ $e['title']??'' }}{{ isset($e['company'])?', '.$e['company']:'' }}</div><div class="entry-sub">{{ $e['start']??'' }}{{ isset($e['end'])?' – '.$e['end']:' – Present' }}{{ isset($e['location'])?' | '.$e['location']:'' }}</div><div class="entry-desc">{{ $e['description']??'' }}</div></div>@endforeach</div>@endif
        @if(count($edu))<div class="section"><div class="section-title">Education</div>@foreach($edu as $e)<div class="entry"><div class="entry-title">{{ $e['degree']??'' }}</div><div class="entry-sub">{{ $e['institution']??'' }}{{ isset($e['year'])?', '.$e['year']:'' }}</div></div>@endforeach</div>@endif
        @if(count($skills))<div class="section"><div class="section-title">Skills</div><p style="font-size:.85rem;">@foreach($skills as $sk)<span class="tag-ats">{{ $sk }}</span>@endforeach</p></div>@endif
        @if(count($certs))<div class="section"><div class="section-title">Certifications</div>@foreach($certs as $c)<div style="font-size:.85rem;">{{ is_array($c)?($c['name']??reset($c)):$c }}</div>@endforeach</div>@endif
        @if(count($langs))<div class="section"><div class="section-title">Languages</div><p style="font-size:.85rem;">{{ implode(', ', array_map(fn($l)=>is_array($l)?($l['name']??reset($l)):$l, $langs)) }}</p></div>@endif
    </div>

    @else {{-- CLASSIC --}}
    <div class="cv-header">
        <div class="cv-name">{{ trim(($user->first_name??'').' '.($user->last_name??'')) ?? '' }}</div>
        <div class="cv-headline">{{ $profile->headline ?? '' }}</div>
        <div class="cv-contact">
            <span><i data-lucide="mail" class="lic"></i> {{ $user->email ?? '' }}</span>
            @if($profile->phone ?? '')<span><i data-lucide="phone" class="lic"></i> {{ $profile->phone }}</span>@endif
            @if($profile->location ?? '')<span><i data-lucide="map-pin" class="lic"></i> {{ $profile->location }}</span>@endif
            @if($profile->linkedin_url ?? '')<span><i data-lucide="link" class="lic"></i> {{ str_replace('https://www.','', $profile->linkedin_url) }}</span>@endif
        </div>
    </div>
    <div class="cv-body">
        <div class="cv-main">
            @if($profile->summary ?? '')<div class="section"><div class="section-title">Professional Summary</div><p style="font-size:.85rem;line-height:1.6;color:#444;">{{ $profile->summary }}</p></div>@endif
            @if(count($exp))<div class="section"><div class="section-title">Experience</div>@foreach($exp as $e)<div class="entry"><div class="entry-header"><span class="entry-title">{{ $e['title']??'' }}</span><span class="entry-date">{{ $e['start']??'' }}{{ isset($e['end'])?' – '.$e['end']:'– Present' }}</span></div><div class="entry-sub">{{ $e['company']??'' }}{{ isset($e['location'])?', '.$e['location']:'' }}</div><div class="entry-desc">{{ $e['description']??'' }}</div></div>@endforeach</div>@endif
            @if(count($edu))<div class="section"><div class="section-title">Education</div>@foreach($edu as $e)<div class="entry"><div class="entry-header"><span class="entry-title">{{ $e['degree']??'' }}</span><span class="entry-date">{{ $e['year']??'' }}</span></div><div class="entry-sub">{{ $e['institution']??'' }}</div></div>@endforeach</div>@endif
        </div>
        <div class="cv-sidebar">
            @if(count($skills))<div class="section"><div class="section-title">Skills</div>@foreach($skills as $sk)<span class="tag tag-skill">{{ $sk }}</span>@endforeach</div>@endif
            @if(count($langs))<div class="section" style="margin-top:1rem;"><div class="section-title">Languages</div>@foreach($langs as $l)<div style="font-size:.82rem;margin-bottom:.3rem;">{{ is_array($l)?($l['name']??$l):$l }}</div>@endforeach</div>@endif
            @if(count($certs))<div class="section" style="margin-top:1rem;"><div class="section-title">Certifications</div>@foreach($certs as $c)<div style="font-size:.82rem;margin-bottom:.3rem;">{{ is_array($c)?($c['name']??$c):$c }}</div>@endforeach</div>@endif
        </div>
    </div>
    @endif
</div>

<div class="cv-actions no-print">
    <div class="print-tip" id="printTip"><i data-lucide="lightbulb" class="lic"></i> In the dialog, choose <b>Save as PDF</b> and enable <b>Background graphics</b> for colours.</div>
    <button class="cv-action-btn btn-print" onclick="downloadPdf()"><i data-lucide="download" class="lic"></i> Download PDF</button>
    @if($canEdit ?? false)
    <a class="cv-action-btn btn-edit" href="/cv/{{ $cv->id }}/settings"><i data-lucide="settings" class="lic"></i> Edit & Style</a>
    @endif
</div>
<script>
function downloadPdf(){
    var t=document.getElementById('printTip'); if(t){t.style.display='block';}
    setTimeout(function(){ window.print(); }, 350);
}
</script>
</body>
</html>
