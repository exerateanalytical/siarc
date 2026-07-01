<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>{{ $event->title }} — Galerie virtuelle de l'artisanat du Cameroun</title>
</head>
<body>
@include('partials.nav')
<style>
.page{max-width:1000px;margin:0 auto;padding:1.5rem;}
.grid2{display:grid;grid-template-columns:1fr 280px;gap:1.5rem;}
.card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);border:1px solid var(--border);margin-bottom:1rem;}
.card-body{padding:1.3rem;}
.event-title{font-size:1.4rem;font-weight:900;margin-bottom:.5rem;}
.badge{display:inline-block;padding:3px 12px;border-radius:99px;font-size:.75rem;font-weight:700;background:var(--light-bg);color:var(--muted);border:1px solid var(--border);}
.detail-row{display:flex;gap:.75rem;align-items:flex-start;padding:.5rem 0;border-bottom:1px solid var(--border);font-size:.85rem;}
.detail-row:last-child{border-bottom:none;}
.detail-icon{width:20px;flex-shrink:0;text-align:center;}
.detail-label{font-weight:600;color:var(--text);}
.detail-value{color:var(--muted);}
.register-box{border:2px solid var(--green);border-radius:var(--radius);padding:1.3rem;background:#fff;}
.register-btn{display:block;width:100%;padding:.65rem;background:var(--green);color:#fff;border:none;border-radius:8px;font-size:.9rem;font-weight:700;cursor:pointer;text-align:center;}
.countdown-box{background:linear-gradient(135deg,var(--dark),#1e3a5f);border-radius:var(--radius);padding:1rem;color:#fff;text-align:center;margin-bottom:1rem;}
.cd-num{font-size:2rem;font-weight:900;line-height:1;}
.cd-label{font-size:.7rem;color:#93c5fd;margin-top:2px;}
.attendee-row{display:flex;align-items:center;gap:.5rem;padding:.35rem 0;font-size:.82rem;border-bottom:1px solid var(--border);}
.attendee-row:last-child{border-bottom:none;}
.avatar-sm{width:28px;height:28px;border-radius:50%;background:var(--green);display:flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:700;color:#fff;flex-shrink:0;}
@media(max-width:700px){.grid2{grid-template-columns:1fr;}}
</style>

@php
$catLabels = ['conference'=>'Conference','summit'=>'Summit','trade_fair'=>'Trade Fair','workshop'=>'Workshop','networking'=>'Networking','hackathon'=>'Hackathon','webinar'=>'Webinar','training'=>'Training','exhibition'=>'Exhibition','other'=>'Other'];
$catIcons  = ['conference'=>'landmark','summit'=>'mountain','trade_fair'=>'store','workshop'=>'wrench','networking'=>'handshake','hackathon'=>'laptop','webinar'=>'tv','training'=>'book-open','exhibition'=>'tent','other'=>'calendar'];
$now = now();
$start = \Carbon\Carbon::parse($event->start_date);
$daysUntil = $now->diffInDays($start, false);
$isPast = $daysUntil < 0;
@endphp

<div class="page">
    <a href="/events" style="font-size:.82rem;color:var(--muted);display:inline-flex;gap:.3rem;margin-bottom:1rem;">← Business Events</a>
    <div class="grid2">
        <div>
            <div class="card">
                <div class="card-body">
                    <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:.7rem;">
                        <span class="badge"><i data-lucide="{{ $catIcons[$event->category]??'calendar' }}" class="lic"></i> {{ $catLabels[$event->category]??ucfirst($event->category) }}</span>
                        <span class="badge" style="background:{{ $event->format==='virtual'?'#eff6ff':($event->format==='hybrid'?'#faf5ff':'#f0fdf4') }};color:{{ $event->format==='virtual'?'#1d4ed8':($event->format==='hybrid'?'#6d28d9':'#166534') }};border-color:currentColor;">{{ strtoupper(str_replace('_',' ',$event->format)) }}</span>
                        @if($event->is_paid)<span class="badge" style="background:#fef3c7;color:#92400e;border-color:#fbbf24;">XAF {{ number_format($event->ticket_price) }}</span>@else<span class="badge" style="background:#d1fae5;color:#065f46;border-color:#6ee7b7;">FREE</span>@endif
                        @if($isPast)<span class="badge" style="background:#f3f4f6;color:#6b7280;">Completed</span>@elseif($event->status==='full')<span class="badge" style="background:#fee2e2;color:#991b1b;">Full</span>@endif
                    </div>
                    <div class="event-title">{{ $event->title }}</div>
                    @if($company)<div style="font-size:.82rem;color:var(--muted);margin-bottom:.9rem;">Organised by <a href="/companies/{{ $company->slug }}" style="color:var(--green);font-weight:600;">{{ $company->name }}</a></div>@endif
                    <div style="margin:.9rem 0;">
                        <div class="detail-row"><div class="detail-icon"><i data-lucide="calendar" class="lic"></i></div><div><div class="detail-label">Date & Time</div><div class="detail-value">{{ date('d M Y H:i', strtotime($event->start_date)) }} – {{ date('d M Y H:i', strtotime($event->end_date)) }}</div></div></div>
                        @if($event->location_city)<div class="detail-row"><div class="detail-icon"><i data-lucide="map-pin" class="lic"></i></div><div><div class="detail-label">Location</div><div class="detail-value">{{ $event->venue_name ? $event->venue_name.' · ' : '' }}{{ $event->location_city }}, {{ $event->location_country }}</div></div></div>@endif
                        <div class="detail-row"><div class="detail-icon"><i data-lucide="users" class="lic"></i></div><div><div class="detail-label">Registrations</div><div class="detail-value">{{ number_format($event->attendee_count) }}{{ $event->max_attendees ? ' / '.number_format($event->max_attendees).' max' : '' }}</div></div></div>
                        <div class="detail-row"><div class="detail-icon"><i data-lucide="eye" class="lic"></i></div><div><div class="detail-label">Views</div><div class="detail-value">{{ number_format($event->view_count+1) }}</div></div></div>
                    </div>
                    @if($event->description)
                    <div style="font-size:.87rem;color:var(--text);line-height:1.7;margin-top:.5rem;">{{ $event->description }}</div>
                    @endif
                </div>
            </div>

            @if($registrations->count() > 0)
            <div class="card">
                <div style="padding:.75rem 1.1rem;font-weight:700;font-size:.88rem;border-bottom:1px solid var(--border);background:var(--light-bg);">Registered Attendees ({{ $registrations->count() }})</div>
                <div class="card-body">
                    @foreach($registrations->take(10) as $reg)
                    <div class="attendee-row">
                        <div class="avatar-sm">{{ strtoupper(substr($reg->first_name??'A',0,1)) }}</div>
                        <div>
                            <span style="font-weight:600;">{{ ($reg->first_name??'').' '.($reg->last_name??'') }}</span>
                            @if($reg->company_name)<span style="color:var(--muted);font-size:.76rem;"> · {{ $reg->company_name }}</span>@endif
                        </div>
                    </div>
                    @endforeach
                    @if($registrations->count() > 10)<div style="font-size:.78rem;color:var(--muted);margin-top:.4rem;">and {{ $registrations->count()-10 }} more…</div>@endif
                </div>
            </div>
            @endif
        </div>

        <div>
            @if(!$isPast && $event->status !== 'cancelled')
            @if(!$daysUntil)
            <div class="countdown-box" style="background:linear-gradient(135deg,var(--red),#b91c1c);">
                <div class="cd-num">TODAY!</div>
                <div class="cd-label">Event is happening now</div>
            </div>
            @elseif($daysUntil > 0)
            <div class="countdown-box">
                <div class="cd-num">{{ $daysUntil }}</div>
                <div class="cd-label">days until this event</div>
            </div>
            @endif

            @if($alreadyRegistered)
            <div class="register-box" style="border-color:var(--muted);">
                <div style="text-align:center;padding:.5rem 0;">
                    <div style="font-size:1.5rem;margin-bottom:.4rem;"><i data-lucide="check" class="lic"></i></div>
                    <div style="font-weight:700;">You are registered!</div>
                    <div style="font-size:.8rem;color:var(--muted);margin-top:.3rem;">We will send reminders as the event approaches.</div>
                </div>
            </div>
            @elseif($authUser && $event->status === 'open')
            <div class="register-box">
                <div style="font-weight:800;font-size:.95rem;margin-bottom:.75rem;">{{ $event->is_paid ? 'Get Your Ticket' : 'Register — Free' }}</div>
                @if($event->is_paid)<div style="font-size:1.2rem;font-weight:900;color:var(--green);margin-bottom:.75rem;">XAF {{ number_format($event->ticket_price) }}</div>@endif
                <form method="POST" action="/events/{{ $event->id }}/register">
                    @csrf
                    @if($myCompanies->count() > 0)
                    <div style="margin-bottom:.7rem;">
                        <label style="display:block;font-size:.8rem;font-weight:600;margin-bottom:.3rem;">Attending as</label>
                        <select style="width:100%;padding:.45rem .75rem;border:1px solid var(--border);border-radius:7px;font-size:.83rem;font-family:inherit;" name="company_id">
                            <option value="">Personal</option>
                            @foreach($myCompanies as $mc)<option value="{{ $mc->id }}">{{ $mc->name }}</option>@endforeach
                        </select>
                    </div>
                    @endif
                    <button type="submit" class="register-btn">{{ $event->is_paid ? 'Register & Pay →' : 'Register Now →' }}</button>
                </form>
            </div>
            @elseif(!$authUser)
            <div class="register-box" style="border-color:var(--muted);">
                <div style="font-size:.85rem;margin-bottom:.75rem;">Sign in to register for this event.</div>
                <a href="/auth/login" class="register-btn" style="display:block;">Sign In →</a>
            </div>
            @elseif($event->status === 'full')
            <div class="register-box" style="border-color:var(--red);">
                <div style="text-align:center;font-weight:700;color:var(--red);">This event is full</div>
            </div>
            @endif
            @else
            <div style="background:#f3f4f6;border-radius:var(--radius);padding:1.2rem;text-align:center;margin-bottom:1rem;">
                <div style="font-size:1.5rem;margin-bottom:.4rem;"><i data-lucide="check-circle-2" class="lic"></i></div>
                <div style="font-weight:700;color:var(--muted);">Event Completed</div>
            </div>
            @endif

            @if($company)
            <div style="background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);padding:1.1rem;border:1px solid var(--border);">
                <div style="display:flex;gap:.5rem;align-items:center;margin-bottom:.75rem;">
                    <div style="width:36px;height:36px;border-radius:7px;background:linear-gradient(135deg,var(--dark),var(--mid));display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.85rem;color:var(--yellow);">{{ strtoupper(substr($company->name,0,2)) }}</div>
                    <div>
                        <div style="font-weight:800;font-size:.85rem;">{{ $company->name }}</div>
                        @if($company->verification_status==='verified')<div style="font-size:.68rem;background:#d4edda;color:#166534;padding:1px 7px;border-radius:99px;display:inline-block;font-weight:700;"><i data-lucide="check" class="lic"></i> Verified</div>@endif
                    </div>
                </div>
                <a href="/companies/{{ $company->slug }}" style="display:block;text-align:center;border:1px solid var(--border);color:var(--text);padding:.4rem;border-radius:7px;font-size:.8rem;font-weight:600;">View Organiser →</a>
            </div>
            @endif
        </div>
    </div>
</div>
@include('partials.footer')
</body>
</html>
