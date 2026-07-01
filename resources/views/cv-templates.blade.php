<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>CV Templates — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:1000px;margin:0 auto;padding:1.5rem;}
.hero{background:linear-gradient(135deg,#0f1623,#1e3a5f);border-radius:var(--radius);padding:2.5rem 2rem;margin-bottom:1.5rem;color:#fff;}
.hero-title{font-size:1.8rem;font-weight:900;margin-bottom:.4rem;}
.hero-sub{font-size:.9rem;color:#93c5fd;margin-bottom:1.2rem;}
.btn-white{padding:.55rem 1.3rem;background:#fff;color:#0f1623;border-radius:8px;font-weight:700;font-size:.88rem;text-decoration:none;display:inline-block;}
.btn-outline{padding:.55rem 1.3rem;border:1px solid rgba(255,255,255,.3);color:#fff;border-radius:8px;font-weight:600;font-size:.88rem;text-decoration:none;display:inline-block;}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(290px,1fr));gap:1.2rem;}
.tcard{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);border:1px solid var(--border);overflow:hidden;display:flex;flex-direction:column;}
.preview{height:260px;background:#f4f5f7;padding:14px;display:flex;align-items:flex-start;justify-content:center;overflow:hidden;}
.doc{width:100%;max-width:230px;background:#fff;border-radius:5px;box-shadow:0 2px 10px rgba(0,0,0,.12);overflow:hidden;font-size:5px;line-height:1.5;}
.tinfo{padding:1rem 1.2rem;flex:1;display:flex;flex-direction:column;}
.tname{font-weight:800;font-size:1rem;color:var(--text);}
.tbest{font-size:.72rem;color:var(--green);font-weight:700;margin:.2rem 0 .4rem;}
.tdesc{font-size:.82rem;color:var(--muted);line-height:1.5;flex:1;}
.tfeat{font-size:.72rem;color:var(--muted);margin-top:.5rem;}
/* doc mini renderings */
.d-h{padding:7px 9px;}
.d-name{height:9px;width:55%;background:currentColor;border-radius:1px;margin-bottom:3px;}
.d-line{height:3px;background:#e5e7eb;border-radius:1px;margin:2.5px 0;}
.d-line.s{width:60%;}.d-line.xs{width:35%;}
.d-ttl{height:3.5px;width:28%;border-radius:1px;margin:6px 0 3px;}
.d-body{padding:7px 9px;}
.chip{display:inline-block;height:5px;width:18px;border-radius:2px;margin:1px;}
</style>

@php
$accent = '#007a33';
$templates = [
    ['classic','Classic','All-purpose & versatile','A balanced two-column layout with a coloured header and a skills sidebar. The safe choice that works for almost any role or industry.','Two-column · Skills sidebar · Header accent'],
    ['modern','Modern','Creative & standout','A striking dark sidebar holds your skills and languages while the main column tells your story. Great for design, marketing, and modern startups.','Dark sidebar · Avatar · Bold header'],
    ['minimal','Minimal','Clean & content-first','Understated typography with generous whitespace. Lets your experience speak for itself — ideal for academics, writers, and consultants.','Single-column · Light type · Spacious'],
    ['professional','Professional','Executive & senior roles','A formal serif layout with a centred name and classic section rules. Conveys authority — built for management, finance, and legal.','Serif · Centred header · Formal'],
    ['technical','Technical','Engineers & developers','A skills-forward layout with a prominent tech-stack band and a code-style accent. Designed for software, data, and engineering roles.','Tech-stack band · Monospace accents · Dark header'],
    ['ats','ATS-Friendly','Recruiter & ATS-safe','A clean single-column, machine-readable format with no graphics or columns — so applicant tracking systems parse every word correctly.','Single-column · Plain text · Parseable'],
];
@endphp

<div class="page">
    <div class="hero">
        <div class="hero-title"><i data-lucide="file-text" class="lic"></i> Industry-Standard CV Templates</div>
        <div class="hero-sub">Six professionally designed layouts. Pick the one that fits your industry — switch anytime, your content stays the same.</div>
        @if($authUser)
            @if($myCvs->count() > 0)
            <a href="/cv" class="btn-white">Apply to My CV →</a>
            @else
            <a href="/cv" class="btn-white">Create My CV →</a>
            @endif
            <a href="/my-profile" class="btn-outline" style="margin-left:.5rem;">Edit Profile Content</a>
        @else
            <a href="/auth/login" class="btn-white">Sign In to Build Your CV →</a>
        @endif
    </div>

    <div class="grid">
        @foreach($templates as [$key,$name,$best,$desc,$feat])
        <div class="tcard">
            <div class="preview">
                <div class="doc">
                    @if($key==='classic')
                    <div class="d-h" style="color:{{ $accent }};background:#fff;border-bottom:2.5px solid {{ $accent }};"><div class="d-name"></div><div class="d-line xs" style="background:#cbd5e1"></div></div>
                    <div style="display:grid;grid-template-columns:1fr 32%;"><div class="d-body"><div class="d-ttl" style="background:{{ $accent }}"></div><div class="d-line"></div><div class="d-line"></div><div class="d-line s"></div><div class="d-ttl" style="background:{{ $accent }}"></div><div class="d-line"></div><div class="d-line s"></div></div><div class="d-body" style="background:#f8f9fa;"><div class="d-ttl" style="background:{{ $accent }}"></div><div class="chip" style="background:#d7ece0"></div><div class="chip" style="background:#d7ece0"></div><div class="chip" style="background:#d7ece0"></div></div></div>
                    @elseif($key==='modern')
                    <div class="d-h" style="background:{{ $accent }};color:#fff;"><div class="d-name" style="background:#fff"></div><div class="d-line xs" style="background:rgba(255,255,255,.6)"></div></div>
                    <div style="display:grid;grid-template-columns:34% 1fr;"><div class="d-body" style="background:#1a1a2e;"><div class="chip" style="background:rgba(255,255,255,.25);display:block;width:70%"></div><div class="chip" style="background:rgba(255,255,255,.25);display:block;width:60%"></div><div class="chip" style="background:rgba(255,255,255,.25);display:block;width:65%"></div></div><div class="d-body"><div class="d-ttl" style="background:{{ $accent }}"></div><div class="d-line"></div><div class="d-line"></div><div class="d-line s"></div></div></div>
                    @elseif($key==='minimal')
                    <div class="d-h" style="color:#333;border-bottom:1px solid #ddd;"><div class="d-name" style="width:60%;height:11px;font-weight:300"></div><div class="d-line xs" style="background:#cbd5e1"></div></div>
                    <div class="d-body"><div class="d-ttl" style="background:#999"></div><div class="d-line"></div><div class="d-line"></div><div class="d-line s"></div><div class="d-ttl" style="background:#999"></div><div class="d-line"></div><div class="d-line s"></div></div>
                    @elseif($key==='professional')
                    <div class="d-h" style="text-align:center;border-bottom:2.5px double {{ $accent }};color:#1a1a2e;"><div class="d-name" style="margin:0 auto 3px;width:50%"></div><div class="d-line xs" style="margin:0 auto;background:{{ $accent }}"></div></div>
                    <div class="d-body"><div class="d-ttl" style="background:{{ $accent }};border-bottom:1px solid #ccc;width:40%"></div><div class="d-line"></div><div class="d-line"></div><div class="d-line s"></div><div class="d-ttl" style="background:{{ $accent }};width:35%"></div><div class="d-line"></div></div>
                    @elseif($key==='technical')
                    <div class="d-h" style="background:#0f1623;color:#5eead4;"><div class="d-name" style="background:#5eead4"></div><div class="d-line xs" style="background:#3b4a5a"></div></div>
                    <div style="background:#13202e;padding:5px 9px;border-bottom:2px solid {{ $accent }};"><span class="chip" style="background:rgba(94,234,212,.3)"></span><span class="chip" style="background:rgba(94,234,212,.3)"></span><span class="chip" style="background:rgba(94,234,212,.3)"></span></div>
                    <div class="d-body"><div class="d-ttl" style="background:{{ $accent }}"></div><div class="d-line"></div><div class="d-line s"></div></div>
                    @else
                    <div class="d-h" style="color:#000;"><div class="d-name" style="background:#000"></div><div class="d-line xs" style="background:#000"></div></div>
                    <div class="d-body"><div class="d-ttl" style="background:#000;border-bottom:1px solid #000;width:30%"></div><div class="d-line"></div><div class="d-line"></div><div class="d-line s"></div><div class="d-ttl" style="background:#000;width:30%"></div><div class="d-line"></div></div>
                    @endif
                </div>
            </div>
            <div class="tinfo">
                <div class="tname">{{ $name }}</div>
                <div class="tbest"><i data-lucide="sparkles" class="lic"></i> {{ $best }}</div>
                <div class="tdesc">{{ $desc }}</div>
                <div class="tfeat">{{ $feat }}</div>
                @if($authUser && $myCvs->count() > 0)
                <a href="/cv/{{ $myCvs->first()->id }}/settings" style="margin-top:.7rem;font-size:.8rem;color:var(--green);font-weight:700;">Use this template →</a>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@include('partials.footer')
</body>
</html>
