<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Business Events — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:1100px;margin:0 auto;padding:1.5rem;}
.hero{background:linear-gradient(135deg,#0f1623,#1e3a5f);border-radius:var(--radius);padding:2.5rem 2rem;margin-bottom:1.5rem;color:#fff;}
.hero-title{font-size:1.8rem;font-weight:900;margin-bottom:.4rem;}
.hero-sub{font-size:.9rem;color:#93c5fd;margin-bottom:1.2rem;}
.hero-btns{display:flex;gap:.75rem;flex-wrap:wrap;}
.btn-white{padding:.55rem 1.3rem;background:#fff;color:var(--dark);border-radius:8px;font-weight:700;font-size:.88rem;border:none;cursor:pointer;}
.btn-outline{padding:.55rem 1.3rem;border:1px solid rgba(255,255,255,.3);color:#fff;border-radius:8px;font-weight:600;font-size:.88rem;cursor:pointer;background:none;}
.filter-bar{display:flex;gap:.75rem;flex-wrap:wrap;margin-bottom:1.2rem;align-items:center;}
.filter-select{padding:.38rem .75rem;border:1px solid var(--border);border-radius:7px;font-size:.82rem;background:#fff;color:var(--text);outline:none;}
.cat-tabs{display:flex;gap:.4rem;flex-wrap:wrap;margin-bottom:1.2rem;}
.cat-tab{padding:.3rem .9rem;border-radius:99px;font-size:.78rem;font-weight:600;cursor:pointer;border:1px solid var(--border);background:#fff;color:var(--muted);transition:all .15s;}
.cat-tab.active,.cat-tab:hover{background:var(--dark);color:#fff;border-color:var(--dark);}
.events-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1rem;}
.event-card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);border:1px solid var(--border);overflow:hidden;transition:box-shadow .15s;}
.event-card:hover{box-shadow:var(--shadow-hover);}
.event-header{padding:1.1rem;border-bottom:1px solid var(--border);}
.event-cat-row{display:flex;justify-content:space-between;margin-bottom:.5rem;}
.badge{display:inline-block;padding:2px 9px;border-radius:99px;font-size:.7rem;font-weight:700;border:1px solid var(--border);background:var(--light-bg);color:var(--muted);}
.badge-paid{background:#fef3c7;color:#92400e;border-color:#fbbf24;}
.badge-free{background:#d1fae5;color:#065f46;border-color:#6ee7b7;}
.event-title{font-weight:800;font-size:.95rem;color:var(--text);margin-bottom:.3rem;}
.event-body{padding:.9rem 1.1rem;}
.event-meta{display:flex;flex-direction:column;gap:.3rem;font-size:.78rem;color:var(--muted);}
.event-footer{padding:.75rem 1.1rem;background:var(--light-bg);border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;}
.countdown{font-size:.72rem;font-weight:700;}
.countdown.soon{color:var(--red);}
.countdown.upcoming{color:var(--muted);}

/* Modal */
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:200;align-items:center;justify-content:center;}
.modal-overlay.open{display:flex;}
.modal{background:#fff;border-radius:var(--radius);padding:1.5rem;width:min(500px,95vw);max-height:90vh;overflow-y:auto;}
.modal-title{font-size:1rem;font-weight:800;margin-bottom:1rem;}
.form-group{margin-bottom:.85rem;}
.form-label{display:block;font-size:.8rem;font-weight:600;margin-bottom:.3rem;}
.form-control{width:100%;padding:.45rem .75rem;border:1px solid var(--border);border-radius:7px;font-size:.85rem;outline:none;font-family:inherit;box-sizing:border-box;}
.form-control:focus{border-color:var(--green);}
textarea.form-control{resize:vertical;min-height:80px;}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:.6rem;}
.btn-submit{padding:.6rem 1.5rem;background:var(--green);color:#fff;border:none;border-radius:8px;font-weight:700;cursor:pointer;}
.btn-cancel{padding:.6rem 1.2rem;border:1px solid var(--border);background:#fff;border-radius:8px;font-weight:600;cursor:pointer;}
@media(max-width:600px){.form-row{grid-template-columns:1fr;}}
</style>

@php
$q        = request('q','');
$category = request('category','');
$format   = request('format','');
$authUser = webUser();
$query = DB::table('events')
    ->leftJoin('companies','events.organizer_company_id','=','companies.id')
    ->select('events.*','companies.name as company_name','companies.slug as company_slug');
if ($q)        $query->where('events.title','like',"%$q%");
if ($category) $query->where('events.category',$category);
if ($format)   $query->where('events.format',$format);
$events = $query->orderBy('events.start_date','asc')->get();

$catLabels = ['conference'=>'Conference','summit'=>'Summit','trade_fair'=>'Trade Fair','workshop'=>'Workshop','networking'=>'Networking','hackathon'=>'Hackathon','webinar'=>'Webinar','training'=>'Training','exhibition'=>'Exhibition','other'=>'Other'];
$catIcons  = ['conference'=>'landmark','summit'=>'mountain','trade_fair'=>'store','workshop'=>'wrench','networking'=>'handshake','hackathon'=>'laptop','webinar'=>'tv','training'=>'book-open','exhibition'=>'tent','other'=>'calendar'];
$formatColors = ['in_person'=>'#007a33','virtual'=>'#0284c7','hybrid'=>'#6d28d9'];
$now = now();
@endphp

<div class="page">
    <div class="hero">
        <div class="hero-title"><i data-lucide="calendar" class="lic"></i> Business Events</div>
        <div class="hero-sub">Conferences, trade fairs, workshops, and networking events for Cameroonian businesses</div>
        <div class="hero-btns">
            @if($authUser)
            <button class="btn-white" onclick="document.getElementById('postModal').classList.add('open')">+ Create Event</button>
            @else
            <a href="/auth/login" class="btn-outline">Sign In to Post Event</a>
            @endif
        </div>
    </div>

    <div class="cat-tabs">
        <a class="cat-tab {{ !$category?'active':'' }}" href="/events">All Events</a>
        @foreach($catLabels as $k=>$v)<a class="cat-tab {{ $category===$k?'active':'' }}" href="/events?category={{ $k }}{{ $format?'&format='.$format:'' }}"><i data-lucide="{{ $catIcons[$k] }}" class="lic"></i> {{ $v }}</a>@endforeach
    </div>

    <div class="filter-bar">
        <form method="GET" action="/events" style="display:flex;gap:.5rem;flex-wrap:wrap;">
            <input type="text" name="q" value="{{ $q }}" class="filter-select" style="min-width:180px;" placeholder="Search events…">
            <select name="format" class="filter-select" onchange="this.form.submit()">
                <option value="">All Formats</option>
                <option value="in_person" {{ $format==='in_person'?'selected':'' }}>In-Person</option>
                <option value="virtual" {{ $format==='virtual'?'selected':'' }}>Virtual</option>
                <option value="hybrid" {{ $format==='hybrid'?'selected':'' }}>Hybrid</option>
            </select>
            @if($category)<input type="hidden" name="category" value="{{ $category }}">@endif
            <button type="submit" style="padding:.38rem .9rem;background:var(--green);color:#fff;border:none;border-radius:7px;font-size:.82rem;font-weight:600;cursor:pointer;">Search</button>
        </form>
        <span style="font-size:.78rem;color:var(--muted);margin-left:auto;">{{ $events->count() }} events found</span>
    </div>

    @if($events->isEmpty())
    <div style="text-align:center;padding:3rem;background:#fff;border-radius:var(--radius);border:1px solid var(--border);">
        <div style="font-size:2rem;margin-bottom:.5rem;"><i data-lucide="calendar" class="lic"></i></div>
        <div style="font-weight:700;margin-bottom:.3rem;">No events found</div>
        <div style="font-size:.85rem;color:var(--muted);">Be the first to post an event in this category.</div>
    </div>
    @else
    <div class="events-grid">
        @foreach($events as $ev)
        @php
        $daysUntil = $now->diffInDays(\Carbon\Carbon::parse($ev->start_date), false);
        $isPast = $daysUntil < 0;
        @endphp
        <div class="event-card">
            <div class="event-header">
                <div class="event-cat-row">
                    <span class="badge"><i data-lucide="{{ $catIcons[$ev->category]??'calendar' }}" class="lic"></i> {{ $catLabels[$ev->category]??ucfirst($ev->category) }}</span>
                    <span class="badge {{ $ev->is_paid?'badge-paid':'badge-free' }}">{{ $ev->is_paid ? 'XAF '.number_format($ev->ticket_price) : 'FREE' }}</span>
                </div>
                <a href="/events/{{ $ev->id }}" style="color:var(--text);">
                    <div class="event-title">{{ $ev->title }}</div>
                </a>
                @if($ev->company_name)<div style="font-size:.75rem;color:var(--muted);">by <a href="/companies/{{ $ev->company_slug }}" style="color:var(--green);font-weight:600;">{{ $ev->company_name }}</a></div>@endif
            </div>
            <div class="event-body">
                <div class="event-meta">
                    <span><i data-lucide="calendar" class="lic"></i> {{ date('d M Y', strtotime($ev->start_date)) }}</span>
                    @if($ev->location_city)<span><i data-lucide="map-pin" class="lic"></i> {{ $ev->location_city }}{{ $ev->venue_name ? ' · '.$ev->venue_name : '' }}</span>@endif
                    <span style="color:{{ $formatColors[$ev->format]??'#6b7a8d' }};font-weight:600;font-size:.75rem;">{{ strtoupper(str_replace('_',' ',$ev->format)) }}</span>
                </div>
                @if($ev->description)<div style="font-size:.8rem;color:var(--muted);margin-top:.5rem;line-height:1.5;">{{ Str::limit($ev->description,100) }}</div>@endif
            </div>
            <div class="event-footer">
                <span class="countdown {{ $daysUntil<=7 && !$isPast?'soon':'upcoming' }}">
                    {{ $isPast ? 'Completed' : ($daysUntil===0 ? 'TODAY' : ($daysUntil===1 ? 'Tomorrow' : 'In '.$daysUntil.' days')) }}
                </span>
                <div style="display:flex;gap:.5rem;align-items:center;font-size:.75rem;color:var(--muted);">
                    <i data-lucide="users" class="lic"></i> {{ number_format($ev->attendee_count) }} registered
                    <a href="/events/{{ $ev->id }}" style="padding:.25rem .7rem;background:var(--green);color:#fff;border-radius:6px;font-size:.72rem;font-weight:700;">View →</a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

@if($authUser)
<div class="modal-overlay" id="postModal">
    <div class="modal">
        <div class="modal-title"><i data-lucide="calendar" class="lic"></i> Create New Event</div>
        <form method="POST" action="/events">
            @csrf
            <div class="form-group"><label class="form-label">Event Title *</label><input type="text" class="form-control" name="title" required></div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Category *</label>
                    <select class="form-control" name="category" required>
                        @foreach($catLabels as $k=>$v)<option value="{{ $k }}"><i data-lucide="{{ $catIcons[$k] }}" class="lic"></i> {{ $v }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group"><label class="form-label">Format *</label>
                    <select class="form-control" name="format" required>
                        <option value="in_person">In-Person</option>
                        <option value="virtual">Virtual</option>
                        <option value="hybrid">Hybrid</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Start Date & Time *</label><input type="datetime-local" class="form-control" name="start_date" required></div>
                <div class="form-group"><label class="form-label">End Date & Time *</label><input type="datetime-local" class="form-control" name="end_date" required></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">City</label><input type="text" class="form-control" name="location_city" placeholder="e.g. Douala"></div>
                <div class="form-group"><label class="form-label">Venue</label><input type="text" class="form-control" name="venue_name" placeholder="Venue name"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Ticket Price (0 = free)</label><input type="number" class="form-control" name="ticket_price" value="0" min="0"></div>
                <div class="form-group"><label class="form-label">Max Attendees</label><input type="number" class="form-control" name="max_attendees" placeholder="Leave blank for unlimited"></div>
            </div>
            <div class="form-group"><label class="form-label">Description</label><textarea class="form-control" name="description" placeholder="Describe your event…"></textarea></div>
            <div style="display:flex;gap:.6rem;justify-content:flex-end;margin-top:.75rem;">
                <button type="button" class="btn-cancel" onclick="document.getElementById('postModal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn-submit">Create Event →</button>
            </div>
        </form>
    </div>
</div>
@endif
@include('partials.footer')
</body>
</html>
