<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Support — Galerie virtuelle de l'artisanat du Cameroun</title></head>
<body>
@include('partials.nav')
<style>
.page{max-width:900px;margin:2rem auto;padding:0 1.5rem;display:grid;grid-template-columns:1fr 340px;gap:1.5rem;}
.page-title{font-size:1.3rem;font-weight:800;margin-bottom:1.2rem;grid-column:1/-1;}
.card{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden;margin-bottom:1rem;}
.card-title{padding:.85rem 1.2rem;font-weight:700;border-bottom:1px solid var(--border);background:var(--light-bg);}
.card-body{padding:1.2rem;}
.form-group{margin-bottom:1rem;}
.form-label{display:block;font-size:.83rem;font-weight:600;margin-bottom:.35rem;}
.form-input,.form-select,.form-textarea{width:100%;padding:.65rem .85rem;border:1.5px solid var(--border);border-radius:8px;font-size:.88rem;outline:none;transition:border-color .15s;font-family:inherit;}
.form-input:focus,.form-select:focus,.form-textarea:focus{border-color:var(--green);}
.form-textarea{min-height:130px;resize:vertical;}
.btn-submit{width:100%;padding:.75rem;background:var(--green);color:#fff;border:none;border-radius:8px;font-weight:700;cursor:pointer;font-size:.9rem;}
.btn-submit:hover{background:#00962e;}
.ticket-row{display:flex;gap:.75rem;padding:.7rem 1.1rem;border-bottom:1px solid var(--border);align-items:flex-start;font-size:.84rem;}
.ticket-row:last-child{border-bottom:none;}
.ticket-num{font-weight:700;color:var(--green);font-size:.78rem;flex-shrink:0;width:90px;}
.ticket-info{flex:1;}
.ticket-subject{font-weight:600;}
.ticket-date{font-size:.73rem;color:var(--muted);margin-top:2px;}
.ticket-status{font-size:.68rem;font-weight:700;padding:2px 8px;border-radius:99px;flex-shrink:0;margin-left:auto;}
.ts-open{background:#d4edda;color:#007a33;}
.ts-closed{background:#f8f9fa;color:#6c757d;}
.ts-pending{background:#fff3cd;color:#856404;}
.empty-state{text-align:center;padding:2rem;color:var(--muted);font-size:.85rem;}
.faq-item{padding:.7rem 0;border-bottom:1px solid var(--border);}
.faq-item:last-child{border-bottom:none;}
.faq-q{font-weight:600;font-size:.85rem;margin-bottom:.3rem;}
.faq-a{font-size:.8rem;color:var(--muted);line-height:1.55;}
@media(max-width:650px){.page{grid-template-columns:1fr;}}
</style>

<div class="page">
    <div class="page-title">Support Center</div>

    <div>
        <div class="card">
            <div class="card-title">Create New Ticket</div>
            <div class="card-body">
                @if($errors->any())
                    <div style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;border-radius:8px;padding:.75rem;font-size:.83rem;margin-bottom:1rem;">{{ $errors->first() }}</div>
                @endif
                <form method="POST" action="/support">
                    @csrf
                    @if($categories->count() > 0)
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select class="form-select" name="category_id">
                            <option value="">Select a category…</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id')==$cat->id?'selected':'' }}>{{ $cat->name_en }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="form-group">
                        <label class="form-label">Subject</label>
                        <input class="form-input" type="text" name="subject" value="{{ old('subject') }}" placeholder="Brief description of your issue" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Message</label>
                        <textarea class="form-textarea" name="body" placeholder="Describe your issue in detail…" required>{{ old('body') }}</textarea>
                    </div>
                    <button class="btn-submit" type="submit">Submit Ticket</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-title">My Tickets</div>
            @if($tickets->isEmpty())
                <div class="empty-state">No tickets yet.</div>
            @else
                @foreach($tickets as $t)
                    @php $stClass = 'ts-'.($t->status==='open'?'open':($t->status==='closed'?'closed':'pending')); @endphp
                    <div class="ticket-row">
                        <div class="ticket-num">#{{ $t->ticket_number }}</div>
                        <div class="ticket-info">
                            <div class="ticket-subject">{{ $t->subject }}</div>
                            <div class="ticket-date">{{ date('d M Y',strtotime($t->created_at)) }}</div>
                        </div>
                        <span class="ticket-status {{ $stClass }}">{{ ucfirst($t->status) }}</span>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    <div>
        <div class="card">
            <div class="card-title">Quick Help</div>
            <div class="card-body">
                <div class="faq-item">
                    <div class="faq-q">How do I invest in an offering?</div>
                    <div class="faq-a">Browse open offerings, click "Invest Now", enter your amount, and complete payment via MTN MoMo, Orange Money, or bank transfer.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-q">What is CMF approval?</div>
                    <div class="faq-a">The Commission des Marchés Financiers (CMF) is Cameroon's financial market regulator. All offerings must be approved before opening to investors.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-q">How long does verification take?</div>
                    <div class="faq-a">Basic verification takes 2–3 business days. CMF certification can take 2–4 weeks depending on document completeness.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-q">What currencies are accepted?</div>
                    <div class="faq-a">All transactions are in CFA Franc (XAF). We support local mobile money and bank transfers within the CEMAC zone.</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-title">Contact Us</div>
            <div class="card-body" style="font-size:.84rem;color:var(--muted);line-height:1.7;">
                <div><i data-lucide="mail" class="lic"></i> <a href="mailto:support@camcompany.cm" style="color:var(--green);">support@camcompany.cm</a></div>
                <div><i data-lucide="phone" class="lic"></i> +237 222 000 000</div>
                <div style="margin-top:.5rem;font-size:.78rem;">Mon–Fri, 08:00–17:00 WAT</div>
            </div>
        </div>
    </div>
</div>
@include('partials.footer')
</body>
</html>
