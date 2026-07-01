<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Explore Companies — CollabCam — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:1100px;margin:0 auto;padding:1.5rem;}
.page-header{margin-bottom:1.5rem;}
.page-title{font-size:1.3rem;font-weight:900;color:var(--text);}
.page-sub{font-size:.85rem;color:var(--muted);margin-top:.25rem;}
.cc-tabs{display:flex;gap:.4rem;background:var(--white);border-radius:var(--radius);padding:.4rem;box-shadow:var(--shadow);margin-bottom:1.5rem;width:fit-content;}
.cc-tab{padding:.4rem 1rem;border-radius:7px;font-size:.82rem;font-weight:600;color:var(--muted);text-decoration:none;transition:all .15s;}
.cc-tab.active{background:var(--dark);color:#fff;}
.filters{display:flex;gap:.75rem;flex-wrap:wrap;margin-bottom:1.2rem;align-items:center;}
.fi{padding:.45rem .85rem;border:1px solid var(--border);border-radius:7px;font-size:.83rem;outline:none;background:#fff;color:var(--text);}
.fi:focus{border-color:var(--green);}
.results-count{font-size:.82rem;color:var(--muted);margin-bottom:1rem;}
.companies-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(310px,1fr));gap:1.1rem;}
.co-card{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);border:1px solid var(--border);overflow:hidden;transition:box-shadow .2s,transform .15s;}
.co-card:hover{box-shadow:var(--shadow-hover);transform:translateY(-2px);}
.co-card-top{padding:1.2rem;display:flex;gap:.9rem;align-items:flex-start;}
.co-logo{width:50px;height:50px;border-radius:10px;background:linear-gradient(135deg,var(--dark),var(--mid));display:flex;align-items:center;justify-content:center;font-weight:900;font-size:1.1rem;color:var(--yellow);flex-shrink:0;}
.co-name{font-weight:800;font-size:.92rem;color:var(--text);line-height:1.3;}
.co-meta{font-size:.75rem;color:var(--muted);margin-top:2px;}
.co-verified{display:inline-flex;align-items:center;gap:3px;background:#d4edda;color:#166534;font-size:.65rem;font-weight:700;padding:1px 7px;border-radius:99px;margin-top:.3rem;}
.co-desc{font-size:.8rem;color:var(--muted);padding:0 1.2rem 1rem;line-height:1.5;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
.co-tags{padding:0 1.2rem .75rem;display:flex;gap:.35rem;flex-wrap:wrap;}
.co-tag{background:var(--light-bg);color:var(--muted);padding:2px 8px;border-radius:99px;font-size:.68rem;font-weight:600;}
.co-footer{padding:.75rem 1.2rem;border-top:1px solid var(--border);display:flex;gap:.5rem;align-items:center;background:var(--light-bg);}
.btn-collab{flex:1;padding:.45rem .75rem;background:var(--green);color:#fff;border-radius:7px;font-size:.78rem;font-weight:700;border:none;cursor:pointer;text-align:center;display:block;}
.btn-collab:hover{background:#00962e;}
.btn-view{padding:.45rem .75rem;border:1px solid var(--border);color:var(--text);border-radius:7px;font-size:.78rem;font-weight:600;display:block;background:#fff;}
.btn-view:hover{background:var(--light-bg);}
/* Modal */
.modal{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:300;align-items:center;justify-content:center;padding:1rem;}
.modal.open{display:flex;}
.modal-box{background:#fff;border-radius:14px;padding:1.8rem;width:100%;max-width:500px;box-shadow:0 16px 48px rgba(0,0,0,.2);}
.modal-title{font-size:1.1rem;font-weight:800;margin-bottom:1.2rem;color:var(--text);}
.form-group{margin-bottom:1rem;}
.form-label{display:block;font-size:.8rem;font-weight:600;margin-bottom:.35rem;color:var(--text);}
.form-control{width:100%;padding:.5rem .75rem;border:1px solid var(--border);border-radius:7px;font-size:.85rem;outline:none;font-family:inherit;color:var(--text);}
.form-control:focus{border-color:var(--green);}
textarea.form-control{resize:vertical;min-height:90px;}
.form-footer{display:flex;gap:.75rem;justify-content:flex-end;margin-top:1.2rem;}
.btn-cancel{padding:.5rem 1.2rem;border:1px solid var(--border);background:#fff;color:var(--text);border-radius:7px;font-size:.85rem;font-weight:600;cursor:pointer;}
.btn-send{padding:.5rem 1.4rem;background:var(--green);color:#fff;border-radius:7px;font-size:.85rem;font-weight:700;cursor:pointer;border:none;}
.btn-send:hover{background:#00962e;}
.empty{text-align:center;padding:3rem;color:var(--muted);}
@media(max-width:640px){.companies-grid{grid-template-columns:1fr;}}
</style>

@php
$authUser = session('auth_user');
$q        = request('q','');
$verified = request('verified','');
$query = DB::table('companies')->whereNull('deleted_at');
if($q) $query->where(function($x) use($q){$x->where('companies.name','like',"%$q%")->orWhere('companies.description_en','like',"%$q%");});
if($verified) $query->where('verification_status',$verified);
$companies = $query->orderByRaw("CASE verification_status WHEN 'verified' THEN 0 WHEN 'pending' THEN 1 ELSE 2 END")->orderBy('name')->paginate(24);
// User's own companies (for collab requests)
$myCompanies = $authUser ? DB::table('company_users')
    ->join('companies','company_users.company_id','=','companies.id')
    ->where('company_users.user_id',$authUser['id'])
    ->where('company_users.status','approved')
    ->whereNull('companies.deleted_at')
    ->select('companies.id','companies.name')->get() : collect();
@endphp

<div class="page">
    <div class="cc-tabs">
        <a href="/collabcam" class="cc-tab">Overview</a>
        <a href="/collabcam/explore" class="cc-tab active">Explore Companies</a>
        <a href="/collabcam/opportunities" class="cc-tab">Opportunities</a>
        @if($authUser)<a href="/collabcam/hub" class="cc-tab">My Collaborations</a>@endif
    </div>

    <div class="page-header">
        <div class="page-title">Discover Companies</div>
        <div class="page-sub">Find and connect with verified Cameroonian businesses for collaboration.</div>
    </div>

    <form method="GET" action="/collabcam/explore">
        <div class="filters">
            <input class="fi" type="text" name="q" value="{{ $q }}" placeholder="Search company name…">
            <select class="fi" name="verified" onchange="this.form.submit()">
                <option value="">Any status</option>
                <option value="verified" {{ $verified==='verified'?'selected':'' }}>Verified only</option>
                <option value="pending" {{ $verified==='pending'?'selected':'' }}>Pending verification</option>
            </select>
            <button type="submit" style="padding:.45rem 1rem;background:var(--green);color:#fff;border:none;border-radius:7px;font-size:.83rem;font-weight:600;cursor:pointer;">Search</button>
            @if($q||$verified)<a href="/collabcam/explore" style="font-size:.8rem;color:var(--muted);align-self:center;">Clear</a>@endif
        </div>
    </form>

    <div class="results-count">{{ $companies->total() }} companies found</div>

    @if($companies->isEmpty())
        <div class="empty">No companies found. <a href="/collabcam/explore" style="color:var(--green);">Clear filters →</a></div>
    @else
        <div class="companies-grid">
            @foreach($companies as $c)
            <div class="co-card">
                <div class="co-card-top">
                    <div class="co-logo">{{ strtoupper(substr($c->name,0,2)) }}</div>
                    <div style="flex:1;min-width:0;">
                        <div class="co-name">{{ $c->name }}</div>
                        <div class="co-meta">{{ $c->legal_form ? ucfirst($c->legal_form) : 'Business' }}{{ $c->verification_status !== 'verified' ? ' · '.ucfirst($c->verification_status??'') : '' }}</div>
                        @if($c->verification_status === 'verified')<div><span class="co-verified"><i data-lucide="check" class="lic"></i> Verified</span></div>@endif
                    </div>
                </div>
                @if($c->description_en)
                <div class="co-desc">{{ $c->description_en }}</div>
                @endif
                <div class="co-footer">
                    @if($authUser)
                        <button class="btn-collab" onclick="openCollabModal('{{ $c->id }}','{{ addslashes($c->name) }}')"><i data-lucide="handshake" class="lic"></i> Collaborate</button>
                    @else
                        <a href="/auth/login" class="btn-collab"><i data-lucide="handshake" class="lic"></i> Collaborate</a>
                    @endif
                    <a href="/companies/{{ $c->slug }}" class="btn-view">View →</a>
                </div>
            </div>
            @endforeach
        </div>
        <div style="margin-top:1.5rem;">{{ $companies->appends(request()->query())->links() }}</div>
    @endif
</div>

{{-- Collab Request Modal --}}
<div class="modal" id="collabModal">
    <div class="modal-box">
        <div class="modal-title" id="modalTitle">Send Collaboration Request</div>
        <form method="POST" action="/collabcam/request">
            @csrf
            <input type="hidden" name="to_company_id" id="toCompanyId">
            @if($myCompanies->count() > 0)
            <div class="form-group">
                <label class="form-label">Collaborating as (your company)</label>
                <select class="form-control" name="from_company_id" required>
                    @foreach($myCompanies as $mc)
                    <option value="{{ $mc->id }}">{{ $mc->name }}</option>
                    @endforeach
                </select>
            </div>
            @else
            <div style="background:#fef3c7;border-radius:8px;padding:.75rem 1rem;font-size:.82rem;color:#92400e;margin-bottom:1rem;">
                You need a <a href="/companies" style="color:var(--green);font-weight:600;">claimed company</a> to send collaboration requests.
            </div>
            @endif
            <div class="form-group">
                <label class="form-label">Collaboration type</label>
                <select class="form-control" name="collab_type" required>
                    <option value="supply_chain">Supply Chain Partnership</option>
                    <option value="joint_venture">Joint Venture</option>
                    <option value="distribution">Distribution Agreement</option>
                    <option value="manufacturing">Manufacturing Partnership</option>
                    <option value="export">Export Collaboration</option>
                    <option value="research">Research &amp; Development</option>
                    <option value="logistics">Logistics &amp; Transport</option>
                    <option value="processing">Processing Partnership</option>
                    <option value="packaging">Packaging Partnership</option>
                    <option value="other">Other Partnership</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Subject</label>
                <input type="text" class="form-control" name="subject" placeholder="Brief subject of your collaboration request" required>
            </div>
            <div class="form-group">
                <label class="form-label">Your message</label>
                <textarea class="form-control" name="message" placeholder="Describe what you're proposing and why this collaboration would be valuable…" required></textarea>
            </div>
            <div class="form-footer">
                <button type="button" class="btn-cancel" onclick="closeCollabModal()">Cancel</button>
                @if($myCompanies->count() > 0)
                <button type="submit" class="btn-send">Send Request →</button>
                @endif
            </div>
        </form>
    </div>
</div>

<script>
function openCollabModal(id, name) {
    document.getElementById('toCompanyId').value = id;
    document.getElementById('modalTitle').textContent = 'Collaborate with ' + name;
    document.getElementById('collabModal').classList.add('open');
}
function closeCollabModal() {
    document.getElementById('collabModal').classList.remove('open');
}
document.getElementById('collabModal').addEventListener('click', function(e) {
    if(e.target === this) closeCollabModal();
});
</script>
@include('partials.footer')
</body>
</html>
