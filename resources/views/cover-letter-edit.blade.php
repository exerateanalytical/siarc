<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Edit Cover Letter — {{ $letter->title }}</title>
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:680px;margin:0 auto;padding:1.5rem 1.5rem 3rem;}
.crumb{font-size:.78rem;color:var(--muted);margin-bottom:.8rem;}
h1{font-size:1.2rem;font-weight:800;margin-bottom:1rem;}
.success{background:#d4edda;border-radius:var(--radius);padding:.7rem 1rem;font-size:.84rem;color:#155724;margin-bottom:1rem;}
.card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);border:1px solid var(--border);padding:1.3rem;margin-bottom:1rem;}
.card h2{font-size:.95rem;font-weight:700;margin-bottom:.9rem;}
.form-group{margin-bottom:.85rem;}
label{display:block;font-size:.78rem;font-weight:600;margin-bottom:.3rem;}
input,select,textarea{width:100%;padding:.55rem .8rem;border:1px solid var(--border);border-radius:7px;font-size:.88rem;font-family:inherit;box-sizing:border-box;}
input:focus,select:focus,textarea:focus{outline:none;border-color:var(--green);}
textarea{resize:vertical;min-height:280px;line-height:1.6;}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:.7rem;}
.template-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:.5rem;}
.tmpl{border:2px solid var(--border);border-radius:8px;padding:.6rem;text-align:center;cursor:pointer;font-size:.8rem;font-weight:700;}
.tmpl.sel,.tmpl:hover{border-color:var(--green);background:#e8f5e9;}
.color-row{display:flex;gap:.6rem;}
.color-dot{width:30px;height:30px;border-radius:50%;cursor:pointer;border:3px solid transparent;}
.color-dot.sel{border-color:#222;}
.save-btn{padding:.65rem 1.4rem;background:var(--green);color:#fff;border:none;border-radius:8px;font-weight:700;font-size:.9rem;cursor:pointer;}
.btn-row{display:flex;gap:.8rem;align-items:center;flex-wrap:wrap;}
.hint{font-size:.73rem;color:var(--muted);margin-top:.3rem;}
@media(max-width:560px){.form-row{grid-template-columns:1fr;}}
</style>

<div class="page">
    <div class="crumb"><a href="/cover-letters" style="color:var(--muted);">Cover Letters</a> › {{ $letter->title }} › Edit</div>
    <h1><i data-lucide="pen-line" class="lic"></i> Edit Cover Letter</h1>
    @if(session('success'))<div class="success">{{ session('success') }}</div>@endif

    <form method="POST" action="/cover-letters/{{ $letter->id }}">
        @csrf
        <div class="card">
            <h2>Details</h2>
            <div class="form-group"><label>Title</label><input type="text" name="title" value="{{ $letter->title }}" required maxlength="100"></div>
            <div class="form-row">
                <div class="form-group"><label>Job Title</label><input type="text" name="job_title" value="{{ $letter->job_title }}"></div>
                <div class="form-group"><label>Company</label><input type="text" name="company_name" value="{{ $letter->company_name }}"></div>
            </div>
            <div class="form-group"><label>Addressed To</label><input type="text" name="recipient_name" value="{{ $letter->recipient_name }}" placeholder="e.g. Hiring Manager"></div>
        </div>

        <div class="card">
            <h2>Letter Body</h2>
            <div class="form-group">
                <textarea name="body" required maxlength="6000">{{ $letter->body }}</textarea>
                <div class="hint">Edit freely. Use blank lines to separate paragraphs. The date, greeting, and your name are added automatically in the preview.</div>
            </div>
        </div>

        <div class="card">
            <h2>Style</h2>
            <div class="form-group">
                <label>Template</label>
                <div class="template-grid">
                    @foreach(['classic'=>'Classic','modern'=>'Modern','minimal'=>'Minimal'] as $tv=>$tl)
                    <div class="tmpl {{ ($letter->template??'classic')===$tv?'sel':'' }}" onclick="selT(this,'{{ $tv }}')">{{ $tl }}</div>
                    @endforeach
                </div>
                <input type="hidden" name="template" id="tmpl-input" value="{{ $letter->template ?? 'classic' }}">
            </div>
            <div class="form-group">
                <label>Accent Colour</label>
                <div class="color-row">
                    @foreach(['#007a33','#0056b3','#ce1126','#1a1a2e'] as $c)
                    <div class="color-dot {{ ($letter->accent_color??'#007a33')===$c?'sel':'' }}" style="background:{{ $c }}" onclick="selC(this,'{{ $c }}')"></div>
                    @endforeach
                </div>
                <input type="hidden" name="accent_color" id="color-input" value="{{ $letter->accent_color ?? '#007a33' }}">
            </div>
        </div>

        <div class="card">
            <h2>Visibility</h2>
            <div class="form-group">
                <label>Shareable link</label>
                <select name="is_public">
                    <option value="0" {{ !($letter->is_public??0)?'selected':'' }}>Private</option>
                    <option value="1" {{ ($letter->is_public??0)?'selected':'' }}>Public (shareable link)</option>
                </select>
                @if($letter->is_public && $letter->public_slug)
                <div class="hint">Public link: <span style="font-family:monospace;">/cover-letter/{{ $letter->public_slug }}/view</span></div>
                @endif
            </div>
        </div>

        <div class="btn-row">
            <button type="submit" class="save-btn">Save</button>
            <a href="/cover-letters/{{ $letter->id }}" style="font-size:.85rem;color:var(--green);font-weight:600;">Preview & Download PDF →</a>
        </div>
    </form>
</div>

<script>
function selT(el,v){document.querySelectorAll('.tmpl').forEach(x=>x.classList.remove('sel'));el.classList.add('sel');document.getElementById('tmpl-input').value=v;}
function selC(el,v){document.querySelectorAll('.color-dot').forEach(x=>x.classList.remove('sel'));el.classList.add('sel');document.getElementById('color-input').value=v;}
</script>
@include('partials.footer')
</body>
</html>
