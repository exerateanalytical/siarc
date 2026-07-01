<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>{{ trim(($dbUser->first_name??'').' '.($dbUser->last_name??'')) }} — {{ $letter->title }}</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;900&display=swap');
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Inter',sans-serif;background:#f4f5f7;color:#1a1a2e;font-size:14px;}
.cl-wrap{max-width:780px;margin:0 auto;background:#fff;min-height:100vh;}
.cl-doc{padding:2.5rem 3rem;}
/* header variants */
.classic .cl-head{border-bottom:3px solid var(--accent);padding-bottom:1rem;margin-bottom:1.5rem;}
.classic .cl-name{font-size:1.6rem;font-weight:900;color:var(--accent);}
.modern .cl-head{background:var(--accent);color:#fff;margin:-2.5rem -3rem 1.5rem;padding:1.8rem 3rem;}
.modern .cl-name{font-size:1.6rem;font-weight:900;}
.modern .cl-contact{color:rgba(255,255,255,.85)!important;}
.minimal .cl-head{margin-bottom:1.8rem;}
.minimal .cl-name{font-size:1.7rem;font-weight:300;letter-spacing:-.5px;}
.cl-headline{font-size:.9rem;color:#666;margin-top:.15rem;}
.modern .cl-headline{color:rgba(255,255,255,.9);}
.cl-contact{font-size:.8rem;color:#666;margin-top:.5rem;display:flex;gap:1rem;flex-wrap:wrap;}
.cl-meta{font-size:.85rem;color:#444;margin-bottom:1.2rem;line-height:1.7;}
.cl-date{margin-bottom:1.2rem;}
.cl-greeting{font-size:.92rem;margin-bottom:1rem;}
.cl-body{font-size:.92rem;line-height:1.8;color:#222;white-space:pre-line;}
.cl-sign{margin-top:1.5rem;font-size:.92rem;line-height:1.7;}
.cl-sign .sig-name{font-weight:700;margin-top:.3rem;}
.cl-actions{position:fixed;bottom:1rem;right:1rem;display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;justify-content:flex-end;max-width:340px;}
.cl-btn{padding:.6rem 1.1rem;border-radius:8px;font-size:.82rem;font-weight:700;cursor:pointer;border:none;text-decoration:none;}
.btn-print{background:#1a1a2e;color:#fff;}
.btn-edit{background:var(--accent);color:#fff;}
.print-tip{display:none;font-size:.72rem;background:#1a1a2e;color:#fff;padding:.5rem .7rem;border-radius:8px;line-height:1.4;box-shadow:0 4px 16px rgba(0,0,0,.25);}
.print-tip b{color:#5eead4;}
@media print{
  @page{size:A4;margin:14mm 0;}
  *{-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important;}
  html,body{background:#fff!important;margin:0!important;padding:0!important;}
  .no-print{display:none!important;}
  .cl-wrap{max-width:100%!important;width:100%!important;margin:0!important;box-shadow:none!important;min-height:0!important;}
  .cl-body{orphans:3;widows:3;}
}
</style>
<style>:root{--accent: {{ in_array($letter->accent_color,['#007a33','#0056b3','#ce1126','#1a1a2e']) ? $letter->accent_color : '#007a33' }};}</style>
</head>
<body>
@php
$name = trim(($dbUser->first_name ?? '') . ' ' . ($dbUser->last_name ?? '')) ?: 'Applicant';
$template = $letter->template ?? 'classic';
$greeting = $letter->recipient_name ? 'Dear ' . $letter->recipient_name . ',' : 'Dear Hiring Manager,';
@endphp

<div class="cl-wrap {{ $template }}">
    <div class="cl-doc">
        <div class="cl-head">
            <div class="cl-name">{{ $name }}</div>
            @if($profile && ($profile->headline ?? ''))<div class="cl-headline">{{ $profile->headline }}</div>@endif
            <div class="cl-contact">
                @if($dbUser->email ?? '')<span>{{ $dbUser->email }}</span>@endif
                @if($profile && ($profile->phone ?? ''))<span>{{ $profile->phone }}</span>@endif
                @if($profile && ($profile->location ?? ''))<span>{{ $profile->location }}</span>@endif
            </div>
        </div>

        <div class="cl-date">{{ date('d F Y') }}</div>

        @if($letter->company_name || $letter->recipient_name)
        <div class="cl-meta">
            @if($letter->recipient_name){{ $letter->recipient_name }}<br>@endif
            @if($letter->company_name){{ $letter->company_name }}<br>@endif
            @if($letter->job_title)<span style="color:#666;">Re: {{ $letter->job_title }}</span>@endif
        </div>
        @endif

        <div class="cl-greeting">{{ $greeting }}</div>

        <div class="cl-body">{{ $letter->body }}</div>

        <div class="cl-sign">
            <div>Sincerely,</div>
            <div class="sig-name">{{ $name }}</div>
        </div>
    </div>
</div>

<div class="cl-actions no-print">
    <div class="print-tip" id="printTip"><i data-lucide="lightbulb" class="lic"></i> In the dialog, choose <b>Save as PDF</b> and enable <b>Background graphics</b> for colours.</div>
    <button class="cl-btn btn-print" onclick="downloadPdf()"><i data-lucide="download" class="lic"></i> Download PDF</button>
    @if($canEdit ?? false)
    <a class="cl-btn btn-edit" href="/cover-letters/{{ $letter->id }}/edit"><i data-lucide="pen-line" class="lic"></i> Edit</a>
    @endif
</div>
<script>
function downloadPdf(){var t=document.getElementById('printTip');if(t){t.style.display='block';}setTimeout(function(){window.print();},350);}
</script>
</body>
</html>
