<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Developer Portal — Galerie virtuelle de l'artisanat du Cameroun API</title></head>
<body>
@include('partials.nav')
<style>
.page{max-width:900px;margin:0 auto;padding:1.5rem 1.5rem 3rem;}
h1{font-size:1.3rem;font-weight:800;margin-bottom:.3rem;}
.subtitle{font-size:.83rem;color:var(--muted);margin-bottom:1.5rem;}
.two-col{display:grid;grid-template-columns:1fr 1fr;gap:1rem;}
@media(max-width:640px){.two-col{grid-template-columns:1fr;}}
.card{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);padding:1.3rem;margin-bottom:1rem;}
.card h2{font-size:.98rem;font-weight:700;margin-bottom:.9rem;}
.stat-row{display:grid;grid-template-columns:repeat(3,1fr);gap:.8rem;margin-bottom:1rem;}
.stat-card{background:var(--white);border-radius:var(--radius);padding:1rem;box-shadow:var(--shadow);text-align:center;}
.stat-num{font-size:1.5rem;font-weight:900;color:var(--green);}
.stat-lbl{font-size:.75rem;color:var(--muted);}
.key-row{background:var(--light-bg);border-radius:8px;padding:.7rem 1rem;display:flex;align-items:center;gap:.7rem;margin-bottom:.6rem;flex-wrap:wrap;}
.key-name{font-weight:700;font-size:.85rem;min-width:120px;}
.key-val{font-family:monospace;font-size:.78rem;color:var(--muted);flex:1;word-break:break-all;}
.key-badge{font-size:.68rem;padding:2px 7px;border-radius:99px;font-weight:700;}
.kb-active{background:#d4edda;color:#007a33;}
.kb-revoked{background:#f8d7da;color:#721c24;}
.key-actions{display:flex;gap:.4rem;}
.key-btn{padding:.3rem .7rem;border:1px solid var(--border);border-radius:6px;font-size:.72rem;cursor:pointer;background:#fff;font-weight:600;}
.key-btn:hover{background:var(--light-bg);}
.key-btn-danger:hover{border-color:var(--red);color:var(--red);}
.form-group{display:flex;flex-direction:column;gap:.3rem;margin-bottom:.7rem;}
label{font-size:.78rem;font-weight:600;}
input,select{padding:.55rem .8rem;border:1px solid var(--border);border-radius:7px;font-size:.88rem;font-family:inherit;}
input:focus,select:focus{outline:none;border-color:var(--green);}
.gen-btn{padding:.6rem 1.2rem;background:var(--green);color:#fff;border:none;border-radius:8px;font-weight:700;font-size:.88rem;cursor:pointer;}
.endpoint-list{list-style:none;}
.endpoint-list li{padding:.5rem 0;border-bottom:1px solid var(--border);font-size:.83rem;display:flex;gap:.6rem;align-items:center;}
.endpoint-list li:last-child{border-bottom:none;}
.method{font-size:.68rem;font-weight:800;padding:2px 6px;border-radius:4px;font-family:monospace;}
.m-get{background:#e8f5e9;color:#007a33;}
.m-post{background:#e3f2fd;color:#0056b3;}
.m-del{background:#f8d7da;color:#721c24;}
.code-block{background:#1a1a2e;color:#e8e8e8;border-radius:8px;padding:1rem;font-family:monospace;font-size:.8rem;overflow-x:auto;line-height:1.5;margin-top:.5rem;}
.code-block .hl{color:#8bc34a;}
.code-block .str{color:#ffd54f;}
.success{background:#d4edda;border-radius:var(--radius);padding:.7rem 1rem;font-size:.84rem;color:#155724;margin-bottom:1rem;}
.new-key-box{background:#fff3cd;border-radius:var(--radius);padding:1rem;margin-bottom:1rem;font-size:.85rem;}
.new-key-code{font-family:monospace;background:#1a1a2e;color:#8bc34a;padding:.5rem .8rem;border-radius:6px;margin-top:.5rem;word-break:break-all;}
</style>

<div class="page">
    <h1>Developer Portal</h1>
    <p class="subtitle">Manage your API keys, explore endpoints, and integrate Galerie virtuelle de l'artisanat du Cameroun data into your applications.</p>

    @if(session('success'))<div class="success">{{ session('success') }}</div>@endif

    @if(session('new_api_key'))
    <div class="new-key-box">
        <strong>Your new API key — copy it now, it will not be shown again:</strong>
        <div class="new-key-code">{{ session('new_api_key') }}</div>
    </div>
    @endif

    <div class="stat-row">
        <div class="stat-card"><div class="stat-num">{{ $keyCount }}</div><div class="stat-lbl">Active Keys</div></div>
        <div class="stat-card"><div class="stat-num">60</div><div class="stat-lbl">Req / minute</div></div>
        <div class="stat-card"><div class="stat-num">77</div><div class="stat-lbl">API Endpoints</div></div>
    </div>

    <div class="two-col">
        <div>
            <div class="card">
                <h2>Your API Keys</h2>
                @if($keys->isEmpty())
                <p style="font-size:.83rem;color:var(--muted);">No API keys yet. Create one below to get started.</p>
                @else
                @foreach($keys as $key)
                <div class="key-row">
                    <span class="key-name">{{ $key->name }}</span>
                    <span class="key-val">{{ $key->key_prefix ?? substr($key->api_key??'',0,12) }}••••••••</span>
                    <span class="key-badge {{ ($key->is_active??true)?'kb-active':'kb-revoked' }}">{{ ($key->is_active??true)?'Active':'Revoked' }}</span>
                    <div class="key-actions">
                        @if($key->is_active??true)
                        <form method="POST" action="/developer/keys/{{ $key->id }}/revoke" style="display:inline;">
                            @csrf
                            <button type="submit" class="key-btn key-btn-danger" onclick="return confirm('Revoke this key? This cannot be undone.')">Revoke</button>
                        </form>
                        @endif
                    </div>
                </div>
                @endforeach
                @endif

                <div style="margin-top:1rem;border-top:1px solid var(--border);padding-top:1rem;">
                    <h2 style="font-size:.88rem;font-weight:700;margin-bottom:.6rem;">Generate New Key</h2>
                    <form method="POST" action="/developer/keys">
                        @csrf
                        <div class="form-group">
                            <label>Key Name / Label</label>
                            <input type="text" name="name" required placeholder="e.g. My App, Production, Testing">
                        </div>
                        <button type="submit" class="gen-btn">Generate API Key</button>
                    </form>
                </div>
            </div>
        </div>

        <div>
            <div class="card">
                <h2>Quick Start</h2>
                <p style="font-size:.82rem;color:var(--muted);margin-bottom:.6rem;">Authenticate every request with your API key in the header:</p>
                <div class="code-block"><span class="hl">Authorization:</span> <span class="str">Bearer YOUR_API_KEY</span></div>
                <p style="font-size:.82rem;color:var(--muted);margin-top:.8rem;margin-bottom:.4rem;">Example — list companies:</p>
                <div class="code-block">curl -X GET \<br>  <span class="str">https://api.camcompany.cm/v1/companies</span> \<br>  -H <span class="str">"Authorization: Bearer ck_YOUR_KEY"</span></div>
            </div>

            <div class="card">
                <h2>Base URL</h2>
                <div class="code-block"><span class="str">https://api.camcompany.cm/v1</span></div>
                <p style="font-size:.78rem;color:var(--muted);margin-top:.6rem;">All responses are JSON. Rate limit: <strong>60 req/min</strong> authenticated, <strong>20 req/min</strong> public.</p>
            </div>
        </div>
    </div>

    <div class="card">
        <h2>Available Endpoints</h2>
        <ul class="endpoint-list">
            <li><span class="method m-get">GET</span><code>/companies</code> — List verified companies (paginated)</li>
            <li><span class="method m-get">GET</span><code>/companies/{slug}</code> — Get company details</li>
            <li><span class="method m-get">GET</span><code>/offerings</code> — List share offerings</li>
            <li><span class="method m-get">GET</span><code>/offerings/{id}</code> — Get offering details</li>
            <li><span class="method m-post">POST</span><code>/offerings/{id}/pledge</code> — Create investment pledge (auth)</li>
            <li><span class="method m-get">GET</span><code>/jobs</code> — List open job postings</li>
            <li><span class="method m-get">GET</span><code>/jobs/{id}</code> — Get job details</li>
            <li><span class="method m-post">POST</span><code>/jobs/{id}/apply</code> — Apply for a job (auth)</li>
            <li><span class="method m-get">GET</span><code>/blog</code> — List blog posts</li>
            <li><span class="method m-get">GET</span><code>/me</code> — Authenticated user profile (auth)</li>
            <li><span class="method m-get">GET</span><code>/me/portfolio</code> — My investments (auth)</li>
            <li><span class="method m-get">GET</span><code>/me/wallet</code> — My wallet balance (auth)</li>
        </ul>
        <a href="/docs/api" style="display:inline-block;margin-top:.8rem;font-size:.82rem;color:var(--green);">View full OpenAPI 3.1 documentation →</a>
    </div>
</div>
@include('partials.footer')
</body>
</html>
