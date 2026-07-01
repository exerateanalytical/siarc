<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Profile — Galerie virtuelle de l'artisanat du Cameroun</title></head>
<body>
@include('partials.nav')
<style>
.page{max-width:760px;margin:2rem auto;padding:0 1.5rem;}
.page-title{font-size:1.3rem;font-weight:800;margin-bottom:1.2rem;}
.card{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden;margin-bottom:1.2rem;}
.card-title{padding:.85rem 1.2rem;font-weight:700;border-bottom:1px solid var(--border);background:var(--light-bg);}
.card-body{padding:1.3rem;}
.avatar-section{display:flex;align-items:center;gap:1.2rem;margin-bottom:1.5rem;padding-bottom:1.2rem;border-bottom:1px solid var(--border);}
.avatar-lg{width:64px;height:64px;border-radius:50%;background:var(--green);display:flex;align-items:center;justify-content:center;font-size:1.4rem;font-weight:800;color:#fff;flex-shrink:0;}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:.75rem;}
.form-group{margin-bottom:1rem;}
.form-label{display:block;font-size:.83rem;font-weight:600;margin-bottom:.35rem;}
.form-input{width:100%;padding:.65rem .85rem;border:1.5px solid var(--border);border-radius:8px;font-size:.9rem;outline:none;transition:border-color .15s;}
.form-input:focus{border-color:var(--green);}
.form-input[readonly]{background:var(--light-bg);cursor:not-allowed;}
.btn-save{padding:.65rem 1.5rem;background:var(--green);color:#fff;border:none;border-radius:8px;font-weight:700;cursor:pointer;font-size:.88rem;}
.btn-save:hover{background:#00962e;}
.form-error{font-size:.77rem;color:var(--red);margin-top:.3rem;}
@media(max-width:480px){.form-row{grid-template-columns:1fr;}}
</style>

@php $authUser = session('auth_user'); @endphp

<div class="page">
    <div class="page-title">My Profile</div>

    <div class="card">
        <div class="card-title">Personal Information</div>
        <div class="card-body">
            <div class="avatar-section">
                <div class="avatar-lg">{{ strtoupper(substr($dbUser->first_name,0,1).substr($dbUser->last_name,0,1)) }}</div>
                <div>
                    <div style="font-weight:700;font-size:1.05rem;">{{ $dbUser->first_name }} {{ $dbUser->last_name }}</div>
                    <div style="font-size:.83rem;color:var(--muted);">{{ $dbUser->email }}</div>
                    <div style="font-size:.75rem;color:var(--muted);margin-top:2px;">Member since {{ date('M Y',strtotime($dbUser->created_at)) }}</div>
                </div>
            </div>

            @if($errors->has('first_name') || $errors->has('last_name') || $errors->has('phone'))
                <div style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;border-radius:8px;padding:.75rem;font-size:.83rem;margin-bottom:1rem;">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="/profile">
                @csrf
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">First Name</label>
                        <input class="form-input" type="text" name="first_name" value="{{ old('first_name', $dbUser->first_name) }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Last Name</label>
                        <input class="form-input" type="text" name="last_name" value="{{ old('last_name', $dbUser->last_name) }}" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Email (cannot be changed)</label>
                    <input class="form-input" type="email" value="{{ $dbUser->email }}" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input class="form-input" type="tel" name="phone" value="{{ old('phone', $dbUser->phone) }}" placeholder="+237 6XX XXX XXX">
                </div>
                <button class="btn-save" type="submit">Save Changes</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-title">Change Password</div>
        <div class="card-body">
            @if($errors->has('current_password'))
                <div style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;border-radius:8px;padding:.75rem;font-size:.83rem;margin-bottom:1rem;">{{ $errors->first('current_password') }}</div>
            @endif
            <form method="POST" action="/profile/password">
                @csrf
                <div class="form-group">
                    <label class="form-label">Current Password</label>
                    <input class="form-input" type="password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <input class="form-input" type="password" name="password" required minlength="8">
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm New Password</label>
                    <input class="form-input" type="password" name="password_confirmation" required>
                </div>
                <button class="btn-save" type="submit">Change Password</button>
            </form>
        </div>
    </div>

    <div style="text-align:center;margin-top:.5rem;">
        <a href="/dashboard" style="color:var(--muted);font-size:.83rem;">← Back to Dashboard</a>
    </div>
</div>
@include('partials.footer')
</body>
</html>
