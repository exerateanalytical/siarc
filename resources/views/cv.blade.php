<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>CV Builder — Galerie virtuelle de l'artisanat du Cameroun</title></head>
<body>
@include('partials.nav')
<style>
.page{max-width:900px;margin:0 auto;padding:1.5rem 1.5rem 3rem;}
.page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:1.2rem;}
h1{font-size:1.3rem;font-weight:800;}
.cv-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:1rem;}
.cv-card{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden;}
.cv-preview-strip{height:80px;position:relative;display:flex;align-items:center;justify-content:center;}
.cv-card-body{padding:.8rem 1rem;}
.cv-title{font-size:.9rem;font-weight:700;margin-bottom:.3rem;}
.cv-meta{font-size:.75rem;color:var(--muted);}
.cv-footer{padding:.6rem 1rem;border-top:1px solid var(--border);display:flex;gap:.5rem;}
.cv-btn{padding:.3rem .7rem;border-radius:6px;font-size:.75rem;font-weight:600;text-decoration:none;border:1px solid var(--border);color:var(--text);}
.cv-btn:hover{background:var(--light-bg);}
.cv-btn-primary{background:var(--green);color:#fff;border-color:var(--green);}
.cv-btn-primary:hover{background:#00962e;}
.new-card{border:2px dashed var(--border);background:transparent;box-shadow:none;display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:180px;cursor:pointer;border-radius:var(--radius);}
.new-card:hover{border-color:var(--green);color:var(--green);}
.new-card-icon{font-size:2rem;margin-bottom:.4rem;}
.new-card span{font-size:.85rem;font-weight:600;}
.modal{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:999;align-items:center;justify-content:center;}
.modal-box{background:#fff;border-radius:var(--radius);padding:1.5rem;width:480px;max-width:95vw;}
.modal-box h2{font-size:1rem;font-weight:700;margin-bottom:1rem;}
.form-group{display:flex;flex-direction:column;gap:.3rem;margin-bottom:.75rem;}
label{font-size:.78rem;font-weight:600;}
input,select{padding:.55rem .8rem;border:1px solid var(--border);border-radius:7px;font-size:.88rem;font-family:inherit;}
input:focus,select:focus{outline:none;border-color:var(--green);}
.template-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:.5rem;margin:.3rem 0;}
.tmpl-option{border:2px solid var(--border);border-radius:8px;padding:.6rem;text-align:center;cursor:pointer;transition:.1s;}
.tmpl-option:hover,.tmpl-option.selected{border-color:var(--green);}
.tmpl-option.selected{background:#e8f5e9;}
.tmpl-name{font-size:.78rem;font-weight:700;margin-top:.3rem;}
.color-row{display:flex;gap:.5rem;margin:.3rem 0;}
.color-dot{width:28px;height:28px;border-radius:50%;cursor:pointer;border:3px solid transparent;}
.color-dot.selected{border-color:#222;}
.modal-actions{display:flex;gap:.5rem;margin-top:1rem;}
.btn-cancel{flex:1;padding:.55rem;border:1px solid var(--border);border-radius:7px;background:#fff;font-size:.85rem;cursor:pointer;}
.btn-create{flex:1;padding:.55rem;border:none;border-radius:7px;background:var(--green);color:#fff;font-weight:700;font-size:.85rem;cursor:pointer;}
.success{background:#d4edda;border-radius:var(--radius);padding:.7rem 1rem;font-size:.84rem;color:#155724;margin-bottom:1rem;}
</style>

<div class="page">
    <div class="page-header">
        <h1>My CVs</h1>
        <div style="display:flex;gap:1rem;align-items:center;">
            <a href="/cover-letters" style="font-size:.83rem;color:var(--green);font-weight:600;"><i data-lucide="pen-line" class="lic"></i> Cover Letters</a>
            <a href="/cv-templates" style="font-size:.83rem;color:var(--green);font-weight:600;"><i data-lucide="file-text" class="lic"></i> Browse Templates</a>
            <a href="/my-profile" style="font-size:.83rem;color:var(--muted);">← Career Profile</a>
        </div>
    </div>

    @if(session('success'))<div class="success">{{ session('success') }}</div>@endif

    <div class="cv-grid">
        {{-- New CV card --}}
        <div class="new-card" onclick="document.getElementById('new-modal').style.display='flex'">
            <div class="new-card-icon">+</div>
            <span>Create New CV</span>
        </div>

        @foreach($cvs as $cv)
        @php
            $colors = ['green'=>'#007a33','blue'=>'#0056b3','red'=>'#ce1126','dark'=>'#1a1a2e'];
            $bgClr = $colors[$cv->color_scheme ?? 'green'] ?? '#007a33';
        @endphp
        <div class="cv-card">
            <div class="cv-preview-strip" style="background:{{ $bgClr }};">
                <div style="color:rgba(255,255,255,.6);font-size:.7rem;text-transform:uppercase;font-weight:700;letter-spacing:1px;">{{ ucfirst($cv->template ?? 'classic') }}</div>
            </div>
            <div class="cv-card-body">
                <div class="cv-title">{{ $cv->title }}</div>
                <div class="cv-meta">
                    {{ strtoupper($cv->language ?? 'en') }} · {{ ucfirst($cv->color_scheme ?? 'green') }}
                    · {{ ($cv->is_public??0) ? 'Public' : 'Private' }}
                </div>
                <div class="cv-meta" style="margin-top:.2rem;">Updated {{ $cv->updated_at ? date('d M Y',strtotime($cv->updated_at)) : '' }}</div>
            </div>
            <div class="cv-footer">
                <a class="cv-btn cv-btn-primary" href="/cv/{{ $cv->id }}/settings">Settings</a>
                <a class="cv-btn" href="/cv/{{ $cv->id }}" target="_blank">Preview</a>
                @if($cv->is_public && $cv->public_slug)
                <a class="cv-btn" href="/cv/{{ $cv->public_slug }}/view" target="_blank">Share</a>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- New CV Modal --}}
<div id="new-modal" class="modal">
    <div class="modal-box">
        <h2>Create New CV</h2>
        <form method="POST" action="/cv">
            @csrf
            <div class="form-group">
                <label>CV Title <span style="color:var(--red)">*</span></label>
                <input type="text" name="title" required placeholder="e.g. Software Engineer CV 2026">
            </div>
            <div class="form-group">
                <label>Template</label>
                <div class="template-grid">
                    @foreach(['classic'=>'Classic','modern'=>'Modern','minimal'=>'Minimal'] as $tv=>$tl)
                    <div class="tmpl-option {{ $tv==='classic'?'selected':'' }}" onclick="selectTemplate(this,'{{ $tv }}')">
                        <div style="height:40px;background:var(--light-bg);border-radius:4px;"></div>
                        <div class="tmpl-name">{{ $tl }}</div>
                    </div>
                    @endforeach
                </div>
                <input type="hidden" name="template" id="template-input" value="classic">
            </div>
            <div class="form-group">
                <label>Colour Scheme</label>
                <div class="color-row">
                    @foreach(['green'=>'#007a33','blue'=>'#0056b3','red'=>'#ce1126','dark'=>'#1a1a2e'] as $cv=>$cc)
                    <div class="color-dot {{ $cv==='green'?'selected':'' }}" style="background:{{ $cc }}" onclick="selectColor(this,'{{ $cv }}')"></div>
                    @endforeach
                </div>
                <input type="hidden" name="color_scheme" id="color-input" value="green">
            </div>
            <div class="form-group">
                <label>Language</label>
                <select name="language">
                    <option value="en">English</option>
                    <option value="fr">Français</option>
                </select>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="document.getElementById('new-modal').style.display='none'">Cancel</button>
                <button type="submit" class="btn-create">Create CV →</button>
            </div>
        </form>
    </div>
</div>
<script>
function selectTemplate(el,val){document.querySelectorAll('.tmpl-option').forEach(x=>x.classList.remove('selected'));el.classList.add('selected');document.getElementById('template-input').value=val;}
function selectColor(el,val){document.querySelectorAll('.color-dot').forEach(x=>x.classList.remove('selected'));el.classList.add('selected');document.getElementById('color-input').value=val;}
document.getElementById('new-modal').addEventListener('click',function(e){if(e.target===this)this.style.display='none';});
</script>
@include('partials.footer')
</body>
</html>
