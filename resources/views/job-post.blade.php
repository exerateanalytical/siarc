<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Post a Job — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:680px;margin:0 auto;padding:1.5rem 1.5rem 3rem;}
.crumb{font-size:.78rem;color:var(--muted);margin-bottom:.8rem;}
h1{font-size:1.3rem;font-weight:900;margin-bottom:1rem;}
.error{background:#f8d7da;border-radius:var(--radius);padding:.7rem 1rem;font-size:.84rem;color:#842029;margin-bottom:1rem;}
.card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);border:1px solid var(--border);padding:1.4rem;}
.fg{margin-bottom:.95rem;}
label{display:block;font-size:.8rem;font-weight:600;margin-bottom:.3rem;}
input,select,textarea{width:100%;padding:.55rem .8rem;border:1px solid var(--border);border-radius:7px;font-size:.88rem;font-family:inherit;box-sizing:border-box;}
input:focus,select:focus,textarea:focus{outline:none;border-color:var(--green);}
textarea{resize:vertical;min-height:160px;line-height:1.6;}
.row{display:grid;grid-template-columns:1fr 1fr;gap:.7rem;}
.req{color:var(--red);}
.hint{font-size:.73rem;color:var(--muted);margin-top:.25rem;}
.save-btn{padding:.7rem 1.6rem;background:var(--green);color:#fff;border:none;border-radius:8px;font-weight:700;font-size:.92rem;cursor:pointer;}
.empty{text-align:center;padding:2.5rem;background:#fff;border-radius:var(--radius);border:1px solid var(--border);}
@media(max-width:560px){.row{grid-template-columns:1fr;}}
</style>

@php
$typeLabels = ['full_time'=>'Full-time','part_time'=>'Part-time','contract'=>'Contract','internship'=>'Internship','remote'=>'Remote'];
@endphp

<div class="page">
    <div class="crumb"><a href="/recruiter" style="color:var(--muted);">Recruiter</a> › Post a Job</div>
    <h1><i data-lucide="megaphone" class="lic"></i> Post a Job</h1>

    @if(session('error'))<div class="error">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="error">{{ $errors->first() }}</div>@endif

    @if($companies->isEmpty())
    <div class="empty">
        <div style="font-size:2rem;margin-bottom:.5rem;"><i data-lucide="building-2" class="lic"></i></div>
        <div style="font-weight:700;margin-bottom:.3rem;">You don't manage a company yet</div>
        <div style="font-size:.85rem;color:var(--muted);margin-bottom:1rem;">Claim or create a company to post jobs under it.</div>
        <a href="/" class="save-btn" style="text-decoration:none;">Find your company →</a>
    </div>
    @else
    <div class="card">
        <form method="POST" action="/jobs">
            @csrf
            <div class="fg">
                <label>Company <span class="req">*</span></label>
                <select name="company_id" required>
                    @foreach($companies as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                </select>
            </div>
            <div class="fg">
                <label>Job Title <span class="req">*</span></label>
                <input type="text" name="title_en" required maxlength="140" value="{{ old('title_en') }}" placeholder="e.g. Senior Software Engineer">
            </div>
            <div class="row">
                <div class="fg">
                    <label>Employment Type <span class="req">*</span></label>
                    <select name="type" required>@foreach($typeLabels as $k=>$v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select>
                </div>
                <div class="fg">
                    <label>Location</label>
                    <input type="text" name="location" value="{{ old('location') }}" placeholder="e.g. Douala">
                </div>
            </div>
            <div class="row">
                <div class="fg"><label>Department</label><input type="text" name="department" value="{{ old('department') }}" placeholder="e.g. Engineering"></div>
                <div class="fg"><label>Application Deadline</label><input type="date" name="deadline" value="{{ old('deadline') }}"></div>
            </div>
            <div class="row">
                <div class="fg"><label>Salary Min (XAF/month)</label><input type="number" name="salary_min" min="0" value="{{ old('salary_min') }}" placeholder="e.g. 400000"></div>
                <div class="fg"><label>Salary Max (XAF/month)</label><input type="number" name="salary_max" min="0" value="{{ old('salary_max') }}" placeholder="e.g. 800000"></div>
            </div>
            <div class="fg">
                <label>Job Description <span class="req">*</span></label>
                <textarea name="description_en" required maxlength="8000" placeholder="Describe the role, responsibilities, and requirements…">{{ old('description_en') }}</textarea>
                <div class="hint">Candidates with matching job alerts will be notified automatically when you post.</div>
            </div>
            <button type="submit" class="save-btn">Post Job →</button>
        </form>
    </div>
    @endif
</div>
@include('partials.footer')
</body>
</html>
