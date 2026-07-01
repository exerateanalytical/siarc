<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>CV Settings — {{ $cv->title }}</title></head>
<body>
@include('partials.nav')
<style>
.page{max-width:560px;margin:2rem auto;padding:0 1.5rem 3rem;}
h1{font-size:1.2rem;font-weight:800;margin-bottom:.3rem;}
.subtitle{font-size:.83rem;color:var(--muted);margin-bottom:1.5rem;}
.card{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);padding:1.3rem;margin-bottom:1rem;}
.card h2{font-size:.95rem;font-weight:700;margin-bottom:.9rem;}
.form-group{display:flex;flex-direction:column;gap:.3rem;margin-bottom:.75rem;}
label{font-size:.78rem;font-weight:600;}
input,select{padding:.55rem .8rem;border:1px solid var(--border);border-radius:7px;font-size:.88rem;font-family:inherit;}
input:focus,select:focus{outline:none;border-color:var(--green);}
.template-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:.6rem;margin:.3rem 0;}
.tmpl-option{border:2px solid var(--border);border-radius:8px;padding:.5rem;text-align:center;cursor:pointer;transition:border-color .15s;}
.tmpl-option:hover,.tmpl-option.selected{border-color:var(--green);}
.tmpl-option.selected{background:#e8f5e9;}
.tmpl-name{font-size:.76rem;font-weight:700;margin-top:.25rem;}
.tmpl-desc{font-size:.66rem;color:var(--muted);line-height:1.3;margin-top:1px;}
/* distinct mini previews */
.mini{height:48px;border-radius:4px;overflow:hidden;border:1px solid #e5e7eb;background:#fff;display:flex;flex-direction:column;}
.mini-classic{display:grid;grid-template-columns:1fr 30%;}
.mini-classic .h{grid-column:1/3;height:13px;background:var(--green);}
.mini-classic .m{padding:3px;}.mini-classic .s{background:#f1f3f5;}
.mini-classic .ln{height:3px;background:#dde2ea;margin:2px 0;border-radius:2px;}
.mini-modern{display:grid;grid-template-columns:34% 1fr;}
.mini-modern .sb{background:#1a1a2e;}.mini-modern .mn{padding:3px;}
.mini-modern .h{height:13px;background:var(--green);grid-column:1/3;}
.mini-modern .ln{height:3px;background:#dde2ea;margin:2px 0;border-radius:2px;}
.mini-minimal{padding:4px 6px;}
.mini-minimal .nm{height:7px;width:55%;background:#333;border-radius:2px;}
.mini-minimal .ln{height:3px;background:#e5e7eb;margin:3px 0;border-radius:2px;}
.mini-pro{padding:3px 5px;text-align:center;}
.mini-pro .nm{height:6px;width:60%;background:#333;margin:2px auto;border-radius:1px;}
.mini-pro .dbl{height:2px;background:var(--green);margin:2px 0;}
.mini-pro .ln{height:3px;background:#e5e7eb;margin:2px 0;border-radius:2px;}
.mini-tech{background:#0f1623;padding:4px;}
.mini-tech .nm{height:6px;width:50%;background:#5eead4;border-radius:1px;}
.mini-tech .band{display:flex;gap:2px;margin:3px 0;}
.mini-tech .chip{height:5px;width:14px;background:rgba(94,234,212,.3);border-radius:2px;}
.mini-tech .ln{height:3px;background:#2a3645;margin:2px 0;border-radius:2px;}
.mini-ats{padding:4px 6px;}
.mini-ats .nm{height:7px;width:50%;background:#000;border-radius:1px;}
.mini-ats .ttl{height:3px;width:30%;background:#000;margin:4px 0 2px;}
.mini-ats .ln{height:3px;background:#d1d5db;margin:2px 0;border-radius:1px;}
.color-row{display:flex;gap:.6rem;margin:.3rem 0;}
.color-dot{width:30px;height:30px;border-radius:50%;cursor:pointer;border:3px solid transparent;}
.color-dot.selected{border-color:#222;}
.save-btn{padding:.65rem 1.4rem;background:var(--green);color:#fff;border:none;border-radius:8px;font-weight:700;font-size:.9rem;cursor:pointer;}
.btn-row{display:flex;gap:.7rem;align-items:center;flex-wrap:wrap;}
.success{background:#d4edda;border-radius:var(--radius);padding:.7rem 1rem;font-size:.84rem;color:#155724;margin-bottom:1rem;}
.slug-box{background:var(--light-bg);border-radius:6px;padding:.4rem .7rem;font-size:.78rem;font-family:monospace;color:var(--muted);margin-top:.3rem;word-break:break-all;}
</style>

<div class="page">
    <div style="font-size:.78rem;color:var(--muted);margin-bottom:.8rem;"><a href="/cv" style="color:var(--muted);">My CVs</a> › <a href="/cv/{{ $cv->id }}" style="color:var(--muted);">{{ $cv->title }}</a> › Settings</div>
    <h1>CV Settings</h1>
    <p class="subtitle">Update the appearance and visibility of "{{ $cv->title }}".</p>

    @if(session('success'))<div class="success">{{ session('success') }}</div>@endif

    <form method="POST" action="/cv/{{ $cv->id }}">
        @csrf
        <div class="card">
            <h2>Basic Details</h2>
            <div class="form-group">
                <label>CV Title</label>
                <input type="text" name="title" value="{{ $cv->title }}" required maxlength="80">
            </div>
            <div class="form-group">
                <label>Language</label>
                <select name="language">
                    <option value="en" {{ ($cv->language??'en')==='en'?'selected':'' }}>English</option>
                    <option value="fr" {{ ($cv->language??'')==='fr'?'selected':'' }}>Français</option>
                </select>
            </div>
        </div>

        <div class="card">
            <h2>Template</h2>
            <p style="font-size:.76rem;color:var(--muted);margin-bottom:.6rem;">Industry-standard layouts. <a href="/cv-templates" style="color:var(--green);">Compare all templates →</a></p>
            @php
            $templates = [
                'classic'      => ['Classic','Two-column, all-purpose','<div class="mini mini-classic"><div class="h"></div><div class="m"><div class="ln"></div><div class="ln"></div><div class="ln" style="width:70%"></div></div><div class="s m"><div class="ln"></div><div class="ln"></div></div></div>'],
                'modern'       => ['Modern','Bold dark sidebar','<div class="mini mini-modern"><div class="h"></div><div class="sb"></div><div class="mn"><div class="ln"></div><div class="ln"></div><div class="ln" style="width:60%"></div></div></div>'],
                'minimal'      => ['Minimal','Clean & understated','<div class="mini mini-minimal"><div class="nm"></div><div class="ln" style="width:40%;margin-top:4px"></div><div class="ln"></div><div class="ln"></div><div class="ln" style="width:75%"></div></div>'],
                'professional' => ['Professional','Formal executive serif','<div class="mini mini-pro"><div class="nm"></div><div class="dbl"></div><div class="ln"></div><div class="ln"></div><div class="ln" style="width:65%"></div></div>'],
                'technical'    => ['Technical','Skills-forward for engineers','<div class="mini mini-tech"><div class="nm"></div><div class="band"><span class="chip"></span><span class="chip"></span><span class="chip"></span></div><div class="ln"></div><div class="ln" style="width:70%"></div></div>'],
                'ats'          => ['ATS-Friendly','Single-column, recruiter-safe','<div class="mini mini-ats"><div class="nm"></div><div class="ttl"></div><div class="ln"></div><div class="ln" style="width:80%"></div><div class="ttl"></div><div class="ln"></div></div>'],
            ];
            @endphp
            <div class="template-grid">
                @foreach($templates as $tv=>$t)
                <div class="tmpl-option {{ ($cv->template??'classic')===$tv?'selected':'' }}" onclick="selectTemplate(this,'{{ $tv }}')">
                    {!! $t[2] !!}
                    <div class="tmpl-name">{{ $t[0] }}</div>
                    <div class="tmpl-desc">{{ $t[1] }}</div>
                </div>
                @endforeach
            </div>
            <input type="hidden" name="template" id="template-input" value="{{ $cv->template ?? 'classic' }}">
        </div>

        <div class="card">
            <h2>Colour Scheme</h2>
            <div class="color-row">
                @foreach(['green'=>'#007a33','blue'=>'#0056b3','red'=>'#ce1126','dark'=>'#1a1a2e'] as $cv_color=>$cc)
                <div class="color-dot {{ ($cv->color_scheme??'green')===$cv_color?'selected':'' }}" style="background:{{ $cc }}" onclick="selectColor(this,'{{ $cv_color }}')"></div>
                @endforeach
            </div>
            <input type="hidden" name="color_scheme" id="color-input" value="{{ $cv->color_scheme ?? 'green' }}">
        </div>

        <div class="card">
            <h2>Visibility</h2>
            <div class="form-group">
                <label>Public CV</label>
                <select name="is_public">
                    <option value="0" {{ !($cv->is_public??0)?'selected':'' }}>Private (only you can see it)</option>
                    <option value="1" {{ ($cv->is_public??0)?'selected':'' }}>Public (shareable link)</option>
                </select>
            </div>
            @if($cv->is_public && $cv->public_slug)
            <div>
                <p style="font-size:.78rem;color:var(--muted);margin-bottom:.3rem;">Public link:</p>
                <div class="slug-box">/cv/{{ $cv->public_slug }}/view</div>
                <button type="button" onclick="navigator.clipboard.writeText(window.location.origin+'/cv/{{ $cv->public_slug }}/view').then(()=>alert('Link copied!'))" style="margin-top:.4rem;font-size:.75rem;padding:.3rem .7rem;border:1px solid var(--border);border-radius:6px;background:#fff;cursor:pointer;">Copy Link</button>
            </div>
            @endif
        </div>

        <div class="btn-row">
            <button type="submit" class="save-btn">Save Settings</button>
            <a href="/cv/{{ $cv->id }}" style="font-size:.85rem;color:var(--green);">Preview CV →</a>
            <a href="/my-profile" style="font-size:.85rem;color:var(--muted);">Edit Content</a>
        </div>
    </form>
</div>

<script>
function selectTemplate(el,val){document.querySelectorAll('.tmpl-option').forEach(x=>x.classList.remove('selected'));el.classList.add('selected');document.getElementById('template-input').value=val;}
function selectColor(el,val){document.querySelectorAll('.color-dot').forEach(x=>x.classList.remove('selected'));el.classList.add('selected');document.getElementById('color-input').value=val;}
</script>
@include('partials.footer')
</body>
</html>
