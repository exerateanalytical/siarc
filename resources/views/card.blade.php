<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>{{ $card->display_name }} — Digital Card — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:440px;margin:0 auto;padding:1.5rem;}
.back{font-size:.82rem;color:var(--muted);display:inline-flex;gap:.3rem;margin-bottom:1rem;}
.dcard{border-radius:18px;overflow:hidden;box-shadow:0 8px 30px rgba(0,0,0,.15);background:#fff;}
.dcard-top{padding:2rem 1.5rem 1.5rem;color:#fff;text-align:center;}
.dcard-avatar{width:90px;height:90px;border-radius:50%;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:900;margin:0 auto .8rem;border:3px solid rgba(255,255,255,.5);}
.dcard-name{font-size:1.5rem;font-weight:900;line-height:1.2;}
.dcard-job{font-size:.92rem;opacity:.92;margin-top:.2rem;}
.dcard-company{font-size:.85rem;opacity:.8;margin-top:.4rem;font-weight:600;}
.dcard-tagline{font-size:.82rem;opacity:.85;margin-top:.6rem;font-style:italic;line-height:1.4;}
.dcard-body{padding:1.3rem 1.5rem;}
.contact-row{display:flex;align-items:center;gap:.75rem;padding:.6rem 0;border-bottom:1px solid var(--border);font-size:.88rem;color:var(--text);text-decoration:none;}
.contact-row:last-of-type{border-bottom:none;}
.contact-icon{width:34px;height:34px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;color:#fff;}
.contact-label{font-size:.7rem;color:var(--muted);font-weight:600;}
.contact-value{font-size:.86rem;font-weight:600;color:var(--text);word-break:break-all;}
.actions{display:flex;gap:.6rem;margin:1.2rem 0;}
.btn-action{flex:1;padding:.65rem;border-radius:9px;font-weight:700;font-size:.85rem;text-align:center;cursor:pointer;border:none;}
.qr-wrap{text-align:center;margin-top:1rem;padding-top:1.2rem;border-top:1px solid var(--border);}
.qr-img{width:160px;height:160px;border-radius:10px;border:1px solid var(--border);padding:6px;background:#fff;}
.qr-hint{font-size:.74rem;color:var(--muted);margin-top:.5rem;}
.socials{display:flex;justify-content:center;gap:.6rem;margin-top:1rem;}
.social-btn{width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.9rem;font-weight:700;color:#fff;text-decoration:none;}
</style>

@php
$c = $card;
$cardUrl = url('/card/'.$c->slug);
$qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&margin=0&data='.urlencode($cardUrl);
@endphp

<div class="page">
    <a class="back" href="/cards">← Digital Cards</a>

    <div class="dcard">
        <div class="dcard-top" style="background:linear-gradient(135deg,{{ $c->theme_color }},{{ $c->theme_color }}cc);">
            <div class="dcard-avatar">{{ $c->initials ?: strtoupper(substr($c->display_name,0,2)) }}</div>
            <div class="dcard-name">{{ $c->display_name }}</div>
            @if($c->job_title)<div class="dcard-job">{{ $c->job_title }}</div>@endif
            @if($c->company_name)<div class="dcard-company">{{ $c->company_name }}</div>@endif
            @if($c->tagline)<div class="dcard-tagline">"{{ $c->tagline }}"</div>@endif
        </div>
        <div class="dcard-body">
            <div class="actions">
                @if($c->phone)<a class="btn-action" style="background:{{ $c->theme_color }};color:#fff;text-decoration:none;" href="tel:{{ $c->phone }}"><i data-lucide="phone" class="lic"></i> Call</a>@endif
                @if($c->whatsapp)<a class="btn-action" style="background:#25d366;color:#fff;text-decoration:none;" href="https://wa.me/{{ preg_replace('/[^0-9]/','',$c->whatsapp) }}" target="_blank" rel="noopener"><i data-lucide="message-circle" class="lic"></i> WhatsApp</a>@endif
                <button class="btn-action" style="background:var(--light-bg);color:var(--text);border:1px solid var(--border);" onclick="saveContact()"><i data-lucide="save" class="lic"></i> Save</button>
            </div>

            @if($c->phone)<a class="contact-row" href="tel:{{ $c->phone }}"><div class="contact-icon" style="background:{{ $c->theme_color }};"><i data-lucide="phone" class="lic"></i></div><div><div class="contact-label">Phone</div><div class="contact-value">{{ $c->phone }}</div></div></a>@endif
            @if($c->email)<a class="contact-row" href="mailto:{{ $c->email }}"><div class="contact-icon" style="background:{{ $c->theme_color }};"><i data-lucide="mail" class="lic"></i></div><div><div class="contact-label">Email</div><div class="contact-value">{{ $c->email }}</div></div></a>@endif
            @if($c->website)<a class="contact-row" href="{{ $c->website }}" target="_blank" rel="noopener"><div class="contact-icon" style="background:{{ $c->theme_color }};"><i data-lucide="globe" class="lic"></i></div><div><div class="contact-label">Website</div><div class="contact-value">{{ preg_replace('#^https?://#','',$c->website) }}</div></div></a>@endif
            @if($c->address || $c->city)<div class="contact-row"><div class="contact-icon" style="background:{{ $c->theme_color }};"><i data-lucide="map-pin" class="lic"></i></div><div><div class="contact-label">Location</div><div class="contact-value">{{ trim(($c->address??'').($c->address && $c->city ? ', ' : '').($c->city??'')) }}</div></div></div>@endif
            @if($company)<a class="contact-row" href="/companies/{{ $company->slug }}"><div class="contact-icon" style="background:{{ $c->theme_color }};"><i data-lucide="building-2" class="lic"></i></div><div><div class="contact-label">Company Profile</div><div class="contact-value">View {{ $company->name }} →</div></div></a>@endif

            @if($c->linkedin || $c->facebook || $c->twitter || $c->instagram)
            <div class="socials">
                @if($c->linkedin)<a class="social-btn" style="background:#0a66c2;" href="{{ $c->linkedin }}" target="_blank" rel="noopener">in</a>@endif
                @if($c->facebook)<a class="social-btn" style="background:#1877f2;" href="{{ $c->facebook }}" target="_blank" rel="noopener">f</a>@endif
                @if($c->twitter)<a class="social-btn" style="background:#000;" href="{{ $c->twitter }}" target="_blank" rel="noopener">𝕏</a>@endif
                @if($c->instagram)<a class="social-btn" style="background:#e1306c;" href="{{ $c->instagram }}" target="_blank" rel="noopener"><i data-lucide="camera" class="lic"></i></a>@endif
            </div>
            @endif

            <div class="qr-wrap">
                <img class="qr-img" src="{{ $qrUrl }}" alt="QR code for {{ $c->display_name }}" loading="lazy">
                <div class="qr-hint">Scan to open this card · <i data-lucide="eye" class="lic"></i> {{ number_format($c->view_count+1) }} views</div>
                <button class="btn-action" style="background:var(--light-bg);color:var(--text);border:1px solid var(--border);margin-top:.7rem;max-width:200px;" onclick="shareCard()">↗ Share Card</button>
            </div>
        </div>
    </div>
</div>

<script>
function saveContact(){
    var vcf = "BEGIN:VCARD\nVERSION:3.0\n";
    vcf += "FN:{{ addslashes($c->display_name) }}\n";
    @if($c->company_name)vcf += "ORG:{{ addslashes($c->company_name) }}\n";@endif
    @if($c->job_title)vcf += "TITLE:{{ addslashes($c->job_title) }}\n";@endif
    @if($c->phone)vcf += "TEL;TYPE=CELL:{{ $c->phone }}\n";@endif
    @if($c->email)vcf += "EMAIL:{{ $c->email }}\n";@endif
    @if($c->website)vcf += "URL:{{ $c->website }}\n";@endif
    @if($c->address || $c->city)vcf += "ADR;TYPE=WORK:;;{{ addslashes(trim(($c->address??'').' '.($c->city??''))) }};;;;\n";@endif
    vcf += "END:VCARD";
    var blob = new Blob([vcf], {type:'text/vcard'});
    var a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = "{{ $c->slug }}.vcf";
    document.body.appendChild(a); a.click(); document.body.removeChild(a);
}
function shareCard(){
    var url = "{{ $cardUrl }}";
    if(navigator.share){ navigator.share({title:"{{ addslashes($c->display_name) }}", url:url}); }
    else { navigator.clipboard.writeText(url).then(function(){ alert('Card link copied to clipboard!'); }); }
}
</script>
@include('partials.footer')
</body>
</html>
