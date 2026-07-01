<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Forgot Password — Galerie virtuelle de l'artisanat du Cameroun</title></head>
<body>
@php $activeTab = ''; @endphp
@include('partials.nav')
<style>
.auth-wrap{min-height:calc(100vh - 60px);display:flex;align-items:center;justify-content:center;padding:2rem 1rem;}
.auth-card{background:var(--white);border-radius:14px;box-shadow:0 4px 32px rgba(0,0,0,.1);padding:2.5rem;width:100%;max-width:420px;}
.auth-logo{text-align:center;margin-bottom:1.5rem;}
.auth-logo .flag{display:inline-flex;height:28px;border-radius:4px;overflow:hidden;}
.auth-logo .flag span{display:block;width:12px;height:28px;}
.auth-title{font-size:1.2rem;font-weight:800;text-align:center;margin-bottom:.25rem;}
.auth-sub{text-align:center;font-size:.84rem;color:var(--muted);margin-bottom:1.8rem;line-height:1.5;}
.form-group{margin-bottom:1.1rem;}
.form-label{display:block;font-size:.83rem;font-weight:600;margin-bottom:.35rem;color:var(--text);}
.form-input{width:100%;padding:.65rem .85rem;border:1.5px solid var(--border);border-radius:8px;font-size:.9rem;outline:none;transition:border-color .15s;}
.form-input:focus{border-color:var(--green);}
.form-input.is-error{border-color:var(--red);}
.btn-primary{width:100%;padding:.8rem;background:var(--green);color:#fff;border:none;border-radius:9px;font-size:.95rem;font-weight:700;cursor:pointer;transition:background .15s;margin-top:.5rem;}
.btn-primary:hover{background:#00962e;}
.auth-link{text-align:center;font-size:.84rem;color:var(--muted);margin-top:1rem;}
.auth-link a{color:var(--green);font-weight:600;}
.alert{padding:.75rem 1rem;border-radius:8px;font-size:.83rem;margin-bottom:1rem;}
.alert-success{background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;}
.alert-error{background:#fef2f2;color:#991b1b;border:1px solid #fecaca;}
.alert-dev{background:#fff3cd;color:#856404;border:1px solid #ffc107;word-break:break-all;}
</style>

<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-logo">
            <div class="flag"><span style="background:var(--green)"></span><span style="background:var(--red)"></span><span style="background:var(--yellow)"></span></div>
        </div>
        <div class="auth-title">Reset your password</div>
        <div class="auth-sub">Enter your email and we will send you a link to reset your password.</div>

        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @if(session('dev_reset_url'))
            <div class="alert alert-dev">
                <strong>Dev mode — reset link:</strong><br>
                <a href="{{ session('dev_reset_url') }}" style="color:#856404;word-break:break-all;">{{ session('dev_reset_url') }}</a>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">{{ $errors->first() }}</div>
        @endif

        @if(!session('status'))
        <form method="POST" action="/forgot-password">
            @csrf
            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input class="form-input {{ $errors->has('email') ? 'is-error' : '' }}"
                    type="email" id="email" name="email"
                    value="{{ old('email') }}" placeholder="you@example.com" required autofocus>
            </div>
            <button class="btn-primary" type="submit">Send Reset Link</button>
        </form>
        @endif

        <div class="auth-link"><a href="/login">&larr; Back to login</a></div>
    </div>
</div>
@include('partials.footer')
</body>
</html>
