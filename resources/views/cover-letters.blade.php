<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Cover Letters — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:820px;margin:0 auto;padding:1.5rem;}
.header-row{display:flex;justify-content:space-between;align-items:center;margin-bottom:.3rem;flex-wrap:wrap;gap:.5rem;}
.h-title{font-size:1.5rem;font-weight:900;}
.h-links{display:flex;gap:1rem;align-items:center;}
.subtitle{font-size:.85rem;color:var(--muted);margin-bottom:1.3rem;}
.success{background:#d4edda;border-radius:var(--radius);padding:.7rem 1rem;font-size:.84rem;color:#155724;margin-bottom:1rem;}
.error{background:#f8d7da;border-radius:var(--radius);padding:.7rem 1rem;font-size:.84rem;color:#842029;margin-bottom:1rem;}
.letter-card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);border:1px solid var(--border);padding:1.1rem 1.3rem;margin-bottom:.8rem;display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;}
.l-title{font-weight:800;font-size:.95rem;color:var(--text);}
.l-meta{font-size:.76rem;color:var(--muted);margin-top:.2rem;}
.l-actions{display:flex;gap:.4rem;align-items:center;}
.btn{padding:.4rem .85rem;border-radius:7px;font-size:.8rem;font-weight:700;cursor:pointer;text-decoration:none;border:1px solid var(--border);background:#fff;color:var(--text);}
.btn-green{background:var(--green);color:#fff;border-color:var(--green);}
.btn-danger:hover{border-color:var(--red);color:var(--red);}
.empty{text-align:center;padding:3rem;background:#fff;border-radius:var(--radius);border:1px solid var(--border);}
.create-card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);border:1px solid var(--border);padding:1.3rem;margin-bottom:1.3rem;}
.create-card h2{font-size:1rem;font-weight:800;margin-bottom:.9rem;}
.form-group{margin-bottom:.8rem;}
.form-label{display:block;font-size:.78rem;font-weight:600;margin-bottom:.3rem;}
.form-control{width:100%;padding:.5rem .8rem;border:1px solid var(--border);border-radius:7px;font-size:.86rem;font-family:inherit;box-sizing:border-box;}
.form-control:focus{outline:none;border-color:var(--green);}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:.7rem;}
.tones{display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:.5rem;}
.tone-opt{border:2px solid var(--border);border-radius:8px;padding:.55rem;cursor:pointer;text-align:center;}
.tone-opt.sel,.tone-opt:hover{border-color:var(--green);background:#e8f5e9;}
.tone-emoji{font-size:1.2rem;}
.tone-name{font-size:.78rem;font-weight:700;margin-top:.1rem;}
.tone-desc{font-size:.66rem;color:var(--muted);}
@media(max-width:560px){.form-row{grid-template-columns:1fr;}}
</style>

<div class="page">
    <div class="header-row">
        <div class="h-title"><i data-lucide="pen-line" class="lic"></i> Cover Letters</div>
        <div class="h-links">
            <a href="/cv" style="font-size:.83rem;color:var(--green);font-weight:600;">My CVs →</a>
            <a href="/my-profile" style="font-size:.83rem;color:var(--muted);">Career Profile</a>
        </div>
    </div>
    <p class="subtitle">Generate a professional draft in seconds, then personalise and export to PDF.</p>

    @if(session('success'))<div class="success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="error">{{ $errors->first() }}</div>@endif

    <div class="create-card">
        <h2><i data-lucide="plus" class="lic"></i> New Cover Letter</h2>
        <form method="POST" action="/cover-letters">
            @csrf
            <div class="form-group">
                <label class="form-label">Title (for your reference) *</label>
                <input type="text" class="form-control" name="title" required maxlength="100" placeholder="e.g. Application to MTN — Senior Engineer" value="{{ $prefill['job_title'] && $prefill['company_name'] ? $prefill['job_title'].' — '.$prefill['company_name'] : '' }}">
            </div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Job Title</label><input type="text" class="form-control" name="job_title" value="{{ $prefill['job_title'] }}" placeholder="e.g. Senior Software Engineer"></div>
                <div class="form-group"><label class="form-label">Company</label><input type="text" class="form-control" name="company_name" value="{{ $prefill['company_name'] }}" placeholder="e.g. MTN Cameroun"></div>
            </div>
            <div class="form-group"><label class="form-label">Addressed To</label><input type="text" class="form-control" name="recipient_name" placeholder="e.g. Hiring Manager"></div>
            <div class="form-group">
                <label class="form-label">Tone — we'll draft the letter for you</label>
                <div class="tones">
                    @foreach(['formal'=>['briefcase','Formal','Professional & traditional'],'enthusiastic'=>['rocket','Enthusiastic','Warm & energetic'],'concise'=>['zap','Concise','Short & direct'],'career_change'=>['refresh-cw','Career Change','For a new field']] as $tv=>$t)
                    <label class="tone-opt {{ $loop->first?'sel':'' }}" onclick="selTone(this,'{{ $tv }}')">
                        <div class="tone-emoji">{{ $t[0] }}</div><div class="tone-name">{{ $t[1] }}</div><div class="tone-desc">{{ $t[2] }}</div>
                    </label>
                    @endforeach
                </div>
                <input type="hidden" name="tone" id="tone-input" value="formal">
            </div>
            <button type="submit" class="btn btn-green" style="padding:.6rem 1.4rem;">Generate Draft →</button>
        </form>
    </div>

    @if($letters->isEmpty())
    <div class="empty">
        <div style="font-size:2rem;margin-bottom:.5rem;"><i data-lucide="pen-line" class="lic"></i></div>
        <div style="font-weight:700;">No cover letters yet</div>
        <div style="font-size:.85rem;color:var(--muted);">Create your first one above — it takes seconds.</div>
    </div>
    @else
    @foreach($letters as $l)
    <div class="letter-card">
        <div style="flex:1;min-width:180px;">
            <div class="l-title">{{ $l->title }}</div>
            <div class="l-meta">{{ $l->job_title ? $l->job_title : 'Untitled role' }}{{ $l->company_name ? ' · '.$l->company_name : '' }} · updated {{ date('d M Y', strtotime($l->updated_at)) }}{{ $l->is_public ? ' · <i data-lucide="globe" class="lic"></i> Public' : '' }}</div>
        </div>
        <div class="l-actions">
            <a href="/cover-letters/{{ $l->id }}" class="btn btn-green">Preview</a>
            <a href="/cover-letters/{{ $l->id }}/edit" class="btn">Edit</a>
            <form method="POST" action="/cover-letters/{{ $l->id }}/delete" onsubmit="return confirm('Delete this cover letter?')" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-danger">Delete</button>
            </form>
        </div>
    </div>
    @endforeach
    @endif
</div>

<script>
function selTone(el,val){document.querySelectorAll('.tone-opt').forEach(x=>x.classList.remove('sel'));el.classList.add('sel');document.getElementById('tone-input').value=val;}
</script>
@include('partials.footer')
</body>
</html>
