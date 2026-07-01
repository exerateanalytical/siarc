<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Set New Password — Galerie virtuelle de l'artisanat du Cameroun</title></head>
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
.auth-sub{text-align:center;font-size:.84rem;color:var(--muted);margin-bottom:1.8rem;}
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
.alert-error{background:#fef2f2;color:#991b1b;border:1px solid #fecaca;}
.invalid-token{text-align:center;padding:1.5rem 0;}
.invalid-token p{color:var(--muted);font-size:.88rem;margin-bottom:1rem;}
</style>

<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-logo">
            <div class="flag"><span style="background:var(--green)"></span><span style="background:var(--red)"></span><span style="background:var(--yellow)"></span></div>
        </div>
        <div class="auth-title">Set new password</div>
        <div class="auth-sub">Enter your new password below.</div>

        @if($errors->any())
            <div class="alert alert-error">{{ $errors->first() }}</div>
        @endif

        @if($tokenValid)
        <form method="POST" action="/reset-password">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input class="form-input" type="email" value="{{ $email }}" disabled style="color:var(--muted);background:var(--light-bg);">
            </div>
            <div class="form-group">
                <label class="form-label" for="password">New Password</label>
                <input class="form-input {{ $errors->has('password') ? 'is-error' : '' }}"
                    type="password" id="password" name="password"
                    placeholder="Min. 8 characters" required autofocus>
            </div>
            <div class="form-group">
                <label class="form-label" for="password_confirmation">Confirm New Password</label>
                <input class="form-input"
                    type="password" id="password_confirmation" name="password_confirmation"
                    placeholder="Repeat password" required>
            </div>
            <button class="btn-primary" type="submit">Set New Password</button>
        </form>
        @else
        <div class="invalid-token">
            <p>This password reset link is invalid or has expired (links expire after 60 minutes).</p>
            <a href="/forgot-password" style="display:inline-block;padding:.6rem 1.4rem;background:var(--green);color:#fff;border-radius:8px;font-weight:700;font-size:.88rem;">Request a new link</a>
        </div>
        @endif

        <div class="auth-link"><a href="/login">&larr; Back to login</a></div>
    </div>
</div>
@include('partials.footer')
</body>
</html>
