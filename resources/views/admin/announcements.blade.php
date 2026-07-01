<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Announcements — Admin</title></head><body>
@php $pageTitle = 'Announcements'; @endphp
@include('admin.nav')
<style>
.layout{display:grid;grid-template-columns:1fr 360px;gap:1.5rem;}
.card{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden;margin-bottom:1rem;}
.card-hd{padding:.75rem 1rem;font-weight:700;font-size:.85rem;border-bottom:1px solid var(--border);background:var(--light-bg);}
.card-bd{padding:1.1rem;}
.ann-item{padding:1rem;border-bottom:1px solid var(--border);display:flex;gap:1rem;align-items:flex-start;}
.ann-item:last-child{border-bottom:none;}
.ann-body{flex:1;font-size:.84rem;line-height:1.5;}
.ann-meta{font-size:.72rem;color:var(--muted);margin-top:.3rem;}
.ann-actions{display:flex;flex-direction:column;gap:.3rem;flex-shrink:0;}
.badge{display:inline-block;font-size:.67rem;font-weight:700;padding:1px 6px;border-radius:99px;}
.b-on{background:#d4edda;color:#155724;}
.b-off{background:#f8f9fa;color:#6c757d;}
.btn-sm{padding:.3rem .65rem;border-radius:6px;font-size:.74rem;font-weight:700;border:none;cursor:pointer;}
.btn-del{background:#fef2f2;color:#991b1b;}
.btn-tog{background:var(--light-bg);color:var(--muted);}
.form-group{margin-bottom:.85rem;}
.form-label{display:block;font-size:.8rem;font-weight:700;margin-bottom:.3rem;color:var(--text);}
.form-control{width:100%;padding:.55rem .75rem;border:1.5px solid var(--border);border-radius:8px;font-size:.85rem;outline:none;font-family:inherit;}
.form-control:focus{border-color:var(--green);}
textarea.form-control{resize:vertical;min-height:80px;}
.btn-primary{width:100%;padding:.65rem;background:var(--green);color:#fff;border:none;border-radius:8px;font-weight:700;font-size:.87rem;cursor:pointer;margin-top:.3rem;}
.empty-state{text-align:center;padding:2rem;color:var(--muted);}
@media(max-width:700px){.layout{grid-template-columns:1fr;}}
</style>

<div class="layout">
    <div>
        <div class="card">
            <div class="card-hd">Active &amp; Scheduled Announcements</div>
            @forelse($announcements as $ann)
            <div class="ann-item">
                <div class="ann-body">
                    <div>{{ $ann->body_en }}</div>
                    @if($ann->body_fr && $ann->body_fr !== $ann->body_en)
                    <div style="color:var(--muted);margin-top:.25rem;font-size:.79rem;font-style:italic;">🇫🇷 {{ $ann->body_fr }}</div>
                    @endif
                    <div class="ann-meta">
                        <span class="badge {{ $ann->is_published ? 'b-on' : 'b-off' }}">{{ $ann->is_published ? 'Published' : 'Hidden' }}</span>
                        &nbsp;
                        {{ $ann->starts_at ? date('d M Y',strtotime($ann->starts_at)) : '?' }}
                        →
                        {{ $ann->ends_at ? date('d M Y',strtotime($ann->ends_at)) : '?' }}
                    </div>
                </div>
                <div class="ann-actions">
                    <form method="POST" action="/admin/announcements/{{ $ann->id }}/toggle">@csrf
                        <button type="submit" class="btn-sm btn-tog">{{ $ann->is_published ? 'Hide' : 'Show' }}</button>
                    </form>
                    <form method="POST" action="/admin/announcements/{{ $ann->id }}/delete" onsubmit="return confirm('Delete this announcement?')">@csrf
                        <button type="submit" class="btn-sm btn-del">Delete</button>
                    </form>
                </div>
            </div>
            @empty
            <div class="empty-state">
                <div style="font-size:2rem;margin-bottom:.5rem;"><i data-lucide="megaphone" class="lic"></i></div>
                <div style="font-weight:700;">No announcements yet</div>
                <div style="font-size:.82rem;margin-top:.3rem;">Create one using the form →</div>
            </div>
            @endforelse
        </div>
    </div>

    <div>
        <div class="card">
            <div class="card-hd">New Announcement</div>
            <div class="card-bd">
                <form method="POST" action="/admin/announcements">
                    @csrf
                    @if($errors->any())
                    <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;padding:.7rem .9rem;margin-bottom:.85rem;font-size:.8rem;color:#991b1b;">
                        @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
                    </div>
                    @endif

                    <div class="form-group">
                        <label class="form-label">Message (English) <span style="color:var(--red);">*</span></label>
                        <textarea class="form-control" name="body_en" maxlength="300" placeholder="e.g. Platform will be under maintenance on 30 Jun from 02:00–04:00 UTC." required>{{ old('body_en') }}</textarea>
                        <div style="font-size:.72rem;color:var(--muted);margin-top:.2rem;">Max 300 characters. Shown on all pages when active.</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Message (French)</label>
                        <textarea class="form-control" name="body_fr" maxlength="300" placeholder="Optional — defaults to English message if blank.">{{ old('body_fr') }}</textarea>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
                        <div class="form-group">
                            <label class="form-label">Start Date <span style="color:var(--red);">*</span></label>
                            <input class="form-control" type="date" name="starts_at" value="{{ old('starts_at', date('Y-m-d')) }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">End Date <span style="color:var(--red);">*</span></label>
                            <input class="form-control" type="date" name="ends_at" value="{{ old('ends_at', date('Y-m-d', strtotime('+7 days'))) }}" required>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary">Publish Announcement</button>
                </form>
            </div>
        </div>
    </div>
</div>
@include('admin.end')
