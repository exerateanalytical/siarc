<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>{{ $requirement->title }} — Compliance — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:860px;margin:0 auto;padding:1.5rem;}
.back{font-size:.82rem;color:var(--muted);display:inline-flex;gap:.3rem;margin-bottom:1rem;}
.req-card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);padding:2rem;border:1px solid var(--border);}
.badge{display:inline-block;padding:2px 10px;border-radius:99px;font-size:.72rem;font-weight:700;background:var(--light-bg);color:var(--muted);border:1px solid var(--border);}
.req-title{font-size:1.5rem;font-weight:900;color:var(--text);line-height:1.3;margin:.6rem 0 .5rem;}
.req-desc{font-size:.92rem;color:var(--muted);margin-bottom:1rem;line-height:1.6;}
.meta-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:.6rem;margin:1rem 0;padding:1rem 0;border-top:1px solid var(--border);border-bottom:1px solid var(--border);}
.meta-item{}
.meta-lbl{font-size:.68rem;color:var(--muted);font-weight:600;margin-bottom:2px;}
.meta-val{font-size:.85rem;font-weight:700;color:var(--text);}
.body-text{font-size:.9rem;color:var(--text);line-height:1.8;white-space:pre-wrap;margin-top:1rem;}
.penalty-box{background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:.9rem;margin-top:1rem;font-size:.84rem;color:#991b1b;}
.track-box{background:#eff6ff;border:1px solid #bfdbfe;border-radius:var(--radius);padding:1.2rem;margin-top:1.5rem;}
.btn-track{padding:.55rem 1.3rem;background:#1e40af;color:#fff;border:none;border-radius:8px;font-weight:700;font-size:.88rem;cursor:pointer;}
</style>

@php
$authUser = webUser();
$catLabels = ['tax'=>'Tax','labour'=>'Labour','environmental'=>'Environmental','sector_license'=>'Sector License','data_protection'=>'Data Protection','health_safety'=>'Health & Safety','customs'=>'Customs','financial'=>'Financial','corporate'=>'Corporate','intellectual_property'=>'IP','other'=>'Other'];
$catIcons = ['tax'=>'banknote','labour'=>'hard-hat','environmental'=>'trees','sector_license'=>'clipboard-list','data_protection'=>'lock','health_safety'=>'hard-hat','customs'=>'stamp','financial'=>'landmark','corporate'=>'landmark','intellectual_property'=>'™','other'=>'file-text'];
$freqLabels = ['one_time'=>'One-time','monthly'=>'Monthly','quarterly'=>'Quarterly','biannual'=>'Biannual','annual'=>'Annual','as_needed'=>'As needed'];
$appliesLabels = ['all'=>'All businesses','sarl'=>'SARL','sa'=>'SA','sole_proprietor'=>'Sole proprietor','specific_sector'=>'Specific sectors'];
@endphp

<div class="page">
    <a class="back" href="/compliance">← Compliance Intelligence</a>
    <div class="req-card">
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
            <span class="badge"><i data-lucide="{{ $catIcons[$requirement->category]??'file-text' }}" class="lic"></i> {{ $catLabels[$requirement->category]??ucfirst($requirement->category) }}</span>
            <span class="badge"><i data-lucide="repeat" class="lic"></i> {{ $freqLabels[$requirement->frequency]??ucfirst($requirement->frequency) }}</span>
        </div>
        <div class="req-title">{{ $requirement->title }}</div>
        @if($requirement->description)<div class="req-desc">{{ $requirement->description }}</div>@endif
        <div class="meta-grid">
            @if($requirement->authority)<div class="meta-item"><div class="meta-lbl">Authority</div><div class="meta-val">{{ $requirement->authority }}</div></div>@endif
            <div class="meta-item"><div class="meta-lbl">Frequency</div><div class="meta-val">{{ $freqLabels[$requirement->frequency]??ucfirst($requirement->frequency) }}</div></div>
            <div class="meta-item"><div class="meta-lbl">Applies To</div><div class="meta-val">{{ $appliesLabels[$requirement->applies_to]??ucfirst($requirement->applies_to) }}</div></div>
            <div class="meta-item"><div class="meta-lbl">Views</div><div class="meta-val">{{ number_format($requirement->view_count+1) }}</div></div>
        </div>
        @if($requirement->body)<div class="body-text">{{ $requirement->body }}</div>@endif
        @if($requirement->penalty_info)<div class="penalty-box"><i data-lucide="alert-triangle" class="lic"></i> <strong>Penalties:</strong> {{ $requirement->penalty_info }}</div>@endif

        @if($authUser)
        <div class="track-box">
            @if($alreadyTracking)
            <div style="display:flex;align-items:center;gap:.6rem;">
                <span style="font-size:1.3rem;"><i data-lucide="check" class="lic"></i></span>
                <div style="font-size:.88rem;color:#1e40af;font-weight:600;">This requirement is on your compliance tracker.</div>
            </div>
            @elseif($hasCompany)
            <div style="font-weight:700;font-size:.9rem;margin-bottom:.6rem;"><i data-lucide="bar-chart-3" class="lic"></i> Add to your compliance tracker</div>
            <form method="POST" action="/compliance/{{ $requirement->slug }}/track" style="display:flex;gap:.6rem;align-items:flex-end;flex-wrap:wrap;">
                @csrf
                <div><label style="display:block;font-size:.76rem;font-weight:600;margin-bottom:.2rem;">Next due date</label><input type="date" name="due_date" style="padding:.42rem .7rem;border:1px solid var(--border);border-radius:7px;font-size:.84rem;"></div>
                <button type="submit" class="btn-track">+ Track This</button>
            </form>
            @else
            <div style="font-size:.85rem;color:var(--muted);">Claim or create a company to track compliance requirements.</div>
            @endif
        </div>
        @endif
    </div>
</div>
@include('partials.footer')
</body>
</html>
